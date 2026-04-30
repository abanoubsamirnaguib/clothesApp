<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with('category')
            ->where('status', 'active');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category = $request->string('category')->trim()->toString()) {
            $query->whereHas('category', fn ($builder) => $builder->where('slug', $category)->orWhere('name', $category));
        }

        $field = match (strtolower($request->string('field', 'created_at')->toString())) {
            'price' => 'price',
            'name' => 'name',
            default => 'created_at',
        };
        $order = strtolower($request->string('order', 'desc')->toString()) === 'asc' ? 'asc' : 'desc';

        return ProductResource::collection($query->orderBy($field, $order)->paginate(24));
    }

    public function show(string $slug)
    {
        $product = Product::query()
            ->with('category')
            ->where('slug', $slug)
            ->orWhere('sku', $slug)
            ->firstOrFail();

        $related = Product::query()
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->limit(12)
            ->get();

        return (new ProductResource($product))->additional([
            'related' => ProductResource::collection($related),
        ]);
    }
}
