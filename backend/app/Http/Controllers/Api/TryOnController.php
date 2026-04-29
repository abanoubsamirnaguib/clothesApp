<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TryOnAttempt;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TryOnController extends Controller
{
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

        $exists = TryOnAttempt::query()
            ->where('product_id', (int) $data['product_id'])
            ->where('user_key', $data['user_key'])
            ->whereIn('status', ['reserved', 'completed'])
            ->exists();

        return response()->json([
            'allowed' => ! $exists,
            'reason' => $exists ? 'already_tried' : null,
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
            if (in_array($existing->status, ['reserved', 'completed'], true)) {
                throw ValidationException::withMessages([
                    'product_id' => 'You already tried this product.',
                ]);
            }

            // Allow retry after a failed attempt (e.g. "No GPU available").
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
}

