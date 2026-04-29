<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function validateCode(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $discount = Discount::query()
            ->where('code', strtoupper($data['code']))
            ->first();

        if (! $discount || ! $discount->isValidFor((float) $data['subtotal'])) {
            return response()->json(['message' => 'Invalid discount code.'], 422);
        }

        $amount = $discount->amountFor((float) $data['subtotal']);

        return [
            'code' => $discount->code,
            'type' => $discount->type,
            'value' => (float) $discount->value,
            'amount' => $amount,
            'total' => max((float) $data['subtotal'] - $amount, 0),
        ];
    }
}
