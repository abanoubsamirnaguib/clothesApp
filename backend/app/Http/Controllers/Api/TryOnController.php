<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TryOnAttempt;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TryOnController extends Controller
{
    private function dailyCutoff(): CarbonImmutable
    {
        // One try per calendar day (server timezone).
        return CarbonImmutable::now()->startOfDay();
    }

    private function isAttemptBlockingToday(TryOnAttempt $attempt): bool
    {
        if (! in_array($attempt->status, ['reserved', 'completed'], true)) return false;
        return $attempt->updated_at && $attempt->updated_at->greaterThanOrEqualTo($this->dailyCutoff());
    }

    private function hfSpaceBaseUrl(): string
    {
        $space = (string) env('HF_TRYON_SPACE', 'yisol/IDM-VTON');
        $subdomain = strtolower(str_replace(['/', '_'], ['-', '-'], $space));
        return "https://{$subdomain}.hf.space";
    }

    private function hfToken(): ?string
    {
        $t = (string) env('HF_TOKEN', '');
        return trim($t) !== '' ? $t : null;
    }

    /**
     * Gradio returns SSE. Poll until we see a `complete` event (or `error`).
     */
    private function fetchSseCompleteJson(string $url, array $headers = [], int $timeoutSeconds = 240): array
    {
        $deadline = microtime(true) + max(1, $timeoutSeconds);

        while (microtime(true) < $deadline) {
            $response = Http::withHeaders($headers)
                // Keep individual requests short; we may need several polls.
                ->timeout(30)
                ->withOptions(['allow_redirects' => true])
                ->get($url);

            if (! $response->successful()) {
                throw new \RuntimeException("Unexpected HTTP status {$response->status()} from {$url}: {$response->body()}");
            }

            $raw = (string) $response->body();
            $parsed = $this->parseSseForTerminalEvent($raw);

            if ($parsed['type'] === 'complete') {
                return $parsed['data'];
            }

            if ($parsed['type'] === 'error') {
                $details = $parsed['error'] ?? null;
                $suffix = is_string($details) && trim($details) !== '' ? (": {$details}") : '';
                throw new \RuntimeException("Try-on failed (SSE error) from {$url}{$suffix}");
            }

            // Not complete yet (often just heartbeats). Wait briefly then retry.
            usleep(500_000);
        }

        throw new \RuntimeException("No complete event found from {$url}");
    }

    /**
     * Parse SSE payload and return terminal event if present.
     *
     * @return array{type: 'complete'|'error'|'pending', data?: array, error?: string}
     */
    private function parseSseForTerminalEvent(string $raw): array
    {
        // SSE events are separated by blank lines.
        $chunks = preg_split("/\\R\\R+/", trim($raw));
        if (! is_array($chunks) || count($chunks) === 0) {
            return ['type' => 'pending'];
        }

        foreach ($chunks as $chunk) {
            $event = null;
            $dataLines = [];

            foreach (preg_split("/\\R/", (string) $chunk) as $line) {
                $line = rtrim((string) $line);
                if ($line === '') continue;

                if (str_starts_with($line, 'event:')) {
                    $event = trim(substr($line, strlen('event:')));
                    continue;
                }

                if (str_starts_with($line, 'data:')) {
                    $dataLines[] = ltrim(substr($line, strlen('data:')));
                }
            }

            if ($event === 'error') {
                $text = trim(implode("\n", $dataLines));
                return ['type' => 'error', 'error' => ($text !== '' ? $text : null)];
            }

            if ($event === 'complete') {
                $jsonText = trim(implode("\n", $dataLines));
                $decoded = json_decode($jsonText, true);
                if (is_array($decoded)) {
                    return ['type' => 'complete', 'data' => $decoded];
                }
                // If complete event exists but JSON parsing fails, treat as error.
                return ['type' => 'error', 'error' => $jsonText !== '' ? $jsonText : null];
            }
        }

        return ['type' => 'pending'];
    }

    private function callHfTryOn(string $personImageUrl, string $garmentImageUrl): string
    {
        $base = $this->hfSpaceBaseUrl();
        $token = $this->hfToken();

        $headers = [];
        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        $seed = random_int(0, 1_000_000_000);

        // Upload both images to the HF Space so it works on localhost/private domains.
        $personTmpPath = $this->uploadToHfSpace($personImageUrl, $headers);
        $garmentTmpPath = $this->uploadToHfSpace($garmentImageUrl, $headers);

        $startResponse = Http::withHeaders($headers)
            ->acceptJson()
            ->timeout(60)
            ->withOptions(['allow_redirects' => true])
            ->post("{$base}/call/tryon", [
            'data' => [
                [
                    'background' => [
                        'path' => $personTmpPath,
                        'meta' => ['_type' => 'gradio.FileData'],
                    ],
                    'layers' => [],
                    'composite' => null,
                ],
                [
                    'path' => $garmentTmpPath,
                    'meta' => ['_type' => 'gradio.FileData'],
                ],
                '',
                true,
                false,
                30,
                $seed,
            ],
            ]);

        if (! $startResponse->successful()) {
            throw new \RuntimeException("Failed to start try-on request ({$startResponse->status()}): {$startResponse->body()}");
        }

        $start = $startResponse->json();
        if (! is_array($start) || ! isset($start['event_id'])) {
            throw new \RuntimeException('Failed to start try-on request (invalid JSON)');
        }

        $eventId = (string) $start['event_id'];
        $result = $this->fetchSseCompleteJson("{$base}/call/tryon/{$eventId}", $headers, 180);

        $output0 = $result[0] ?? null;
        $resultUrl = is_array($output0) ? ($output0['url'] ?? null) : null;
        if (! is_string($resultUrl) || trim($resultUrl) === '') {
            throw new \RuntimeException('Try-on returned no image URL');
        }

        return $resultUrl;
    }

    private function uploadToHfSpace(string $sourceUrl, array $headers): string
    {
        $base = $this->hfSpaceBaseUrl();

        $fileRes = Http::timeout(60)->withOptions(['allow_redirects' => true])->get($sourceUrl);
        if (! $fileRes->successful()) {
            throw new \RuntimeException("Failed to fetch image: {$sourceUrl}");
        }

        $contentType = (string) ($fileRes->header('content-type') ?? 'image/jpeg');
        $ext = match (true) {
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'webp') => 'webp',
            default => 'jpg',
        };

        $filename = 'upload.' . $ext;

        $uploadRes = Http::withHeaders($headers)
            ->timeout(120)
            ->withOptions(['allow_redirects' => true])
            ->attach('files', $fileRes->body(), $filename)
            ->post("{$base}/upload");

        if (! $uploadRes->successful()) {
            throw new \RuntimeException("HF upload failed ({$uploadRes->status()}): {$uploadRes->body()}");
        }

        $json = $uploadRes->json();
        if (is_array($json) && isset($json[0]) && is_string($json[0]) && str_starts_with($json[0], '/tmp/')) {
            return $json[0];
        }

        throw new \RuntimeException('HF upload returned unexpected response');
    }
    public function result(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'user_key' => ['required', 'string', 'max:64'],
        ]);

        $attempt = TryOnAttempt::query()
            ->where('product_id', (int) $data['product_id'])
            ->where('user_key', $data['user_key'])
            ->first();

        if (! $attempt) {
            return response()->json([
                'found' => false,
                'status' => null,
                'result_image_url' => null,
                'garment_image_url' => null,
            ]);
        }

        return response()->json([
            'found' => true,
            'status' => $attempt->status,
            'result_image_url' => $attempt->status === 'completed' ? $attempt->result_image_url : null,
            'garment_image_url' => $attempt->garment_image_url,
        ]);
    }

    public function eligibility(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'user_key' => ['required', 'string', 'max:64'],
        ]);

        $cutoff = $this->dailyCutoff();
        $exists = TryOnAttempt::query()
            ->where('product_id', (int) $data['product_id'])
            ->where('user_key', $data['user_key'])
            ->whereIn('status', ['reserved', 'completed'])
            ->where('updated_at', '>=', $cutoff)
            ->exists();

        return response()->json([
            'allowed' => ! $exists,
            'reason' => $exists ? 'already_tried_today' : null,
        ]);
    }

    public function reserve(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'user_key' => ['required', 'string', 'max:64'],
            'person_image_url' => ['nullable', 'string', 'max:2048'],
            'garment_image_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $existing = TryOnAttempt::query()
            ->where('product_id', (int) $data['product_id'])
            ->where('user_key', $data['user_key'])
            ->first();

        if ($existing) {
            if ($this->isAttemptBlockingToday($existing)) {
                throw ValidationException::withMessages([
                    'product_id' => 'You already tried this product today.',
                ]);
            }

            // Allow retry after a failed attempt or after the day rolls over.
            $existing->status = 'reserved';
            $existing->person_image_url = $data['person_image_url'] ?? $existing->person_image_url;
            $existing->garment_image_url = $data['garment_image_url'] ?? $existing->garment_image_url;
            $existing->result_image_url = null;
            $existing->error = null;
            $existing->save();

            $attempt = $existing;
        } else {
            $attempt = TryOnAttempt::create([
                'product_id' => (int) $data['product_id'],
                'user_key' => $data['user_key'],
                'status' => 'reserved',
                'person_image_url' => $data['person_image_url'] ?? null,
                'garment_image_url' => $data['garment_image_url'] ?? null,
            ]);
        }

        return response()->json([
            'attempt_id' => $attempt->id,
        ]);
    }

    public function complete(Request $request)
    {
        $data = $request->validate([
            'attempt_id' => ['required', 'integer', 'exists:try_on_attempts,id'],
            'user_key' => ['required', 'string', 'max:64'],
            'status' => ['required', 'in:completed,failed'],
            'result_image_url' => ['nullable', 'string', 'max:2048'],
            'error' => ['nullable', 'string'],
        ]);

        $attempt = TryOnAttempt::query()
            ->where('id', (int) $data['attempt_id'])
            ->where('user_key', $data['user_key'])
            ->firstOrFail();

        $attempt->status = $data['status'];
        $attempt->result_image_url = $data['result_image_url'] ?? null;
        $attempt->error = $data['error'] ?? null;
        $attempt->save();

        return response()->json([
            'ok' => true,
        ]);
    }

    public function bestGarmentImage(Request $request, int $productId)
    {
        $product = Product::query()->findOrFail($productId);

        $candidates = collect([$product->featured_image])
            ->merge($product->images ?? [])
            ->filter(fn ($v) => is_string($v) && trim($v) !== '')
            ->values();

        // MVP heuristic: take featured_image, else first image.
        return response()->json([
            'product_id' => $product->id,
            'garment_image_url' => $candidates->first(),
            'candidates' => $candidates,
        ]);
    }

    public function tryOn(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'user_key' => ['required', 'string', 'max:64'],
            'person_image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        /** @var UploadedFile $personImage */
        $personImage = $request->file('person_image');

        $best = $this->bestGarmentImage($request, (int) $data['product_id'])->getData(true);
        $garmentUrl = $best['garment_image_url'] ?? null;
        if (! is_string($garmentUrl) || trim($garmentUrl) === '') {
            throw ValidationException::withMessages([
                'product_id' => 'Product has no images for try-on.',
            ]);
        }

        // Store user image in /public/tryon so it's accessible without `storage:link`.
        $dir = public_path('tryon');
        File::ensureDirectoryExists($dir);
        $ext = strtolower($personImage->getClientOriginalExtension() ?: 'jpg');
        $filename = (string) Str::uuid() . '.' . $ext;
        $personImage->move($dir, $filename);
        $personUrl = URL::to('tryon/' . $filename);

        $existing = TryOnAttempt::query()
            ->where('product_id', (int) $data['product_id'])
            ->where('user_key', (string) $data['user_key'])
            ->first();

        if ($existing) {
            if ($this->isAttemptBlockingToday($existing)) {
                throw ValidationException::withMessages([
                    'product_id' => 'You already tried this product today.',
                ]);
            }

            $existing->status = 'reserved';
            $existing->person_image_url = $personUrl;
            $existing->garment_image_url = $garmentUrl;
            $existing->result_image_url = null;
            $existing->error = null;
            $existing->save();
            $attempt = $existing;
        } else {
            $attempt = TryOnAttempt::create([
                'product_id' => (int) $data['product_id'],
                'user_key' => (string) $data['user_key'],
                'status' => 'reserved',
                'person_image_url' => $personUrl,
                'garment_image_url' => $garmentUrl,
            ]);
        }

        try {
            $resultUrl = $this->callHfTryOn($personUrl, $garmentUrl);

            $attempt->status = 'completed';
            $attempt->result_image_url = $resultUrl;
            $attempt->error = null;
            $attempt->save();

            return response()->json([
                'attempt_id' => (int) $attempt->id,
                'result_image_url' => $resultUrl,
                'garment_image_url' => $garmentUrl,
            ]);
        } catch (\Throwable $e) {
            $attempt->status = 'failed';
            $attempt->result_image_url = null;
            $attempt->error = $e->getMessage();
            $attempt->save();

            throw $e;
        }
    }
}

