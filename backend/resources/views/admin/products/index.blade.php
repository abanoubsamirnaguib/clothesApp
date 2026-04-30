@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-3xl font-black">Products</h1>
    <a href="{{ route('admin.products.create') }}" class="rounded-xl bg-neutral-950 px-4 py-3 text-sm font-bold text-white">Add Product</a>
</div>

<div class="rounded-3xl bg-white p-5 shadow">
    <div class="overflow-auto">
        <table class="admin-table w-full text-left text-sm">
            <thead><tr class="border-b"><th class="py-3">Image</th><th>Name</th><th>SKU</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($products as $product)
                    <tr class="border-b">
                        <td data-label="Image" class="py-3"><img class="h-16 w-12 rounded-xl object-cover" src="{{ $product->featured_image }}" alt=""></td>
                        <td data-label="Name" class="font-bold">{{ $product->name }}</td>
                        <td data-label="SKU">{{ $product->sku }}</td>
                        <td data-label="Category">{{ $product->category?->name }}</td>
                        <td data-label="Price">${{ number_format($product->price, 2) }}</td>
                        <td data-label="Stock">{{ $product->stock_quantity }}</td>
                        <td data-label="Status">{{ ucfirst($product->status) }}</td>
                        <td data-label="Actions" class="admin-table-actions py-3">
                            <div class="flex flex-wrap gap-2">
                            <a class="rounded-lg bg-neutral-100 px-3 py-2 font-bold" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.products.delete', $product) }}" onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg bg-red-100 px-3 py-2 font-bold text-red-700">Delete</button>
                            </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $products->links() }}</div>
</div>
@endsection
