<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.phone' => ['required', 'string', 'max:255'],
            'customer.address' => ['nullable', 'string'],
            'customer.notes' => ['nullable', 'string'],
            'discount_code' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.selected_size' => ['nullable', 'string'],
        ]);

        $order = DB::transaction(function () use ($data) {
            $items = collect($data['items'])->map(function (array $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "{$product->name} does not have enough stock.",
                    ]);
                }

                $unitPrice = $product->sale_price !== null && $product->sale_price > 0
                    ? (float) $product->sale_price
                    : (float) $product->price;

                return [
                    'product' => $product,
                    'quantity' => (int) $item['quantity'],
                    'selected_size' => $item['selected_size'] ?? null,
                    'unit_price' => $unitPrice,
                    'subtotal' => round($unitPrice * (int) $item['quantity'], 2),
                ];
            });

            $subtotal = round($items->sum('subtotal'), 2);
            $discount = null;
            $discountAmount = 0.0;

            if (! empty($data['discount_code'])) {
                $discount = Discount::query()->where('code', strtoupper($data['discount_code']))->lockForUpdate()->first();

                if (! $discount || ! $discount->isValidFor($subtotal)) {
                    throw ValidationException::withMessages(['discount_code' => 'Invalid discount code.']);
                }

                $discountAmount = $discount->amountFor($subtotal);
                $discount->increment('used_count');
            }

            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'customer_name' => $data['customer']['name'],
                'customer_phone' => $data['customer']['phone'],
                'customer_address' => $data['customer']['address'] ?? null,
                'customer_notes' => $data['customer']['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount_code' => $discount?->code,
                'discount_amount' => $discountAmount,
                'total' => max($subtotal - $discountAmount, 0),
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                $product = $item['product'];
                $product->decrement('stock_quantity', $item['quantity']);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'selected_color' => $product->color,
                    'selected_size' => $item['selected_size'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            return $order->load('items');
        });

        return [
            'order_number' => $order->order_number,
            'order' => $order,
            'whatsapp_url' => $this->whatsappUrl($order),
        ];
    }

    private function nextOrderNumber(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-';
        $count = Order::query()->where('order_number', 'like', "{$prefix}%")->count() + 1;

        return $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    private function whatsappUrl(Order $order): string
    {
        $number = preg_replace('/\D+/', '', Setting::value('whatsapp_number', ''));
        $message = "New Order #{$order->order_number}\n\n";
        $message .= "Name: {$order->customer_name}\n";
        $message .= "Phone: {$order->customer_phone}\n";
        if ($order->customer_address) {
            $message .= "Address: {$order->customer_address}\n";
        }
        $message .= "\nItems:\n";

        foreach ($order->items as $item) {
            $size = $item->selected_size ? " / {$item->selected_size}" : '';
            $message .= "- {$item->product_name}{$size} (x{$item->quantity}) - $".number_format((float) $item->subtotal, 2)."\n";
        }

        if ($order->discount_code) {
            $message .= "\nDiscount: {$order->discount_code} (-$".number_format((float) $order->discount_amount, 2).")\n";
        }

        $message .= 'Total: $'.number_format((float) $order->total, 2)."\n";

        if ($order->customer_notes) {
            $message .= "\nNotes: {$order->customer_notes}";
        }

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }
}
