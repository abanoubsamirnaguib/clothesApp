@extends('admin.layouts.app')

@section('title', $product->exists ? 'Edit Product' : 'Create Product')

@section('content')
<h1 class="mb-6 text-3xl font-black">{{ $product->exists ? 'Edit Product' : 'Create Product' }}</h1>
<form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="grid gap-5 rounded-3xl bg-white p-6 shadow">
    @csrf
    @if ($product->exists) @method('PUT') @endif

    <div class="grid gap-4 md:grid-cols-2">
        <label><span class="mb-1 block text-sm font-bold">Name</span><input class="w-full rounded-xl border px-4 py-3" name="name" value="{{ old('name', $product->name) }}" required></label>
        <label><span class="mb-1 block text-sm font-bold">SKU</span><input class="w-full rounded-xl border px-4 py-3" name="sku" value="{{ old('sku', $product->sku) }}" required></label>
        <label><span class="mb-1 block text-sm font-bold">Category</span><select class="w-full rounded-xl border px-4 py-3" name="category_id"><option value="">No category</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
        <label><span class="mb-1 block text-sm font-bold">Status</span><select class="w-full rounded-xl border px-4 py-3" name="status"><option value="active" @selected(old('status', $product->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option></select></label>
        <label><span class="mb-1 block text-sm font-bold">Price</span><input class="w-full rounded-xl border px-4 py-3" name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price ?? 0) }}" required></label>
        <label><span class="mb-1 block text-sm font-bold">Sale Price</span><input class="w-full rounded-xl border px-4 py-3" name="sale_price" type="number" step="0.01" min="0" value="{{ old('sale_price', $product->sale_price) }}"></label>
        <label><span class="mb-1 block text-sm font-bold">Stock</span><input class="w-full rounded-xl border px-4 py-3" name="stock_quantity" type="number" min="0" value="{{ old('stock_quantity', $product->stock_quantity ?? 100) }}" required></label>
        <label><span class="mb-1 block text-sm font-bold">Color</span><input class="w-full rounded-xl border px-4 py-3" name="color" value="{{ old('color', $product->color) }}"></label>
        <label><span class="mb-1 block text-sm font-bold">Style</span><input class="w-full rounded-xl border px-4 py-3" name="style" value="{{ old('style', $product->style) }}"></label>
        <label><span class="mb-1 block text-sm font-bold">Sizes (comma separated)</span><input class="w-full rounded-xl border px-4 py-3" name="sizes" value="{{ old('sizes', implode(', ', $product->sizes ?? [])) }}"></label>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <label><span class="mb-1 block text-sm font-bold">Featured Image URL</span><input class="w-full rounded-xl border px-4 py-3" name="featured_image" value="{{ old('featured_image', $product->featured_image) }}"></label>
        <label><span class="mb-1 block text-sm font-bold">Or Upload Featured Image</span><input class="w-full rounded-xl border px-4 py-3" name="featured_image_file" type="file" accept="image/*"></label>
    </div>
    <label><span class="mb-1 block text-sm font-bold">All Image URLs (one per line or comma separated)</span><textarea class="h-28 w-full rounded-xl border px-4 py-3" name="images">{{ old('images', implode("\n", $product->images ?? [])) }}</textarea></label>
    <label><span class="mb-1 block text-sm font-bold">Upload Gallery Images</span><input class="w-full rounded-xl border px-4 py-3" name="gallery_images[]" type="file" accept="image/*" multiple></label>
    <label><span class="mb-1 block text-sm font-bold">Description</span><textarea class="h-40 w-full rounded-xl border px-4 py-3" name="description">{{ old('description', $product->description) }}</textarea></label>

    <div class="flex gap-3">
        <button class="rounded-xl bg-neutral-950 px-5 py-3 font-bold text-white">Save Product</button>
        <a href="{{ route('admin.products') }}" class="rounded-xl bg-neutral-100 px-5 py-3 font-bold">Cancel</a>
    </div>
</form>
@endsection
