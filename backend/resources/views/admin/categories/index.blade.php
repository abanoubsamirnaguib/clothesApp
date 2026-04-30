@extends('admin.layouts.app')

@section('title', 'Categories')

@section('content')
<h1 class="mb-6 text-3xl font-black">Categories</h1>
<div class="grid gap-6 lg:grid-cols-[380px_1fr]">
    <form
        method="POST"
        action="{{ ($categoryToEdit ?? null) ? route('admin.categories.update', $categoryToEdit) : route('admin.categories.store') }}"
        class="rounded-3xl bg-white p-5 shadow"
    >
        @csrf
        @if (($categoryToEdit ?? null))
            @method('PUT')
        @endif

        <h2 class="mb-4 text-xl font-black">
            {{ ($categoryToEdit ?? null) ? 'Edit Category' : 'Add Category' }}
        </h2>

        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-bold">Name</span>
            <input
                class="w-full rounded-xl border px-4 py-3"
                name="name"
                value="{{ old('name', ($categoryToEdit->name ?? '')) }}"
                required
            >
            @error('name')<div class="mt-2 text-sm font-bold text-red-700">{{ $message }}</div>@enderror
        </label>

        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-bold">Image URL</span>
            <input
                class="w-full rounded-xl border px-4 py-3"
                name="image"
                value="{{ old('image', ($categoryToEdit->image ?? '')) }}"
            >
            @error('image')<div class="mt-2 text-sm font-bold text-red-700">{{ $message }}</div>@enderror
        </label>

        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-bold">Description</span>
            <textarea class="h-28 w-full rounded-xl border px-4 py-3" name="description">{{ old('description', ($categoryToEdit->description ?? '')) }}</textarea>
            @error('description')<div class="mt-2 text-sm font-bold text-red-700">{{ $message }}</div>@enderror
        </label>

        <div class="flex flex-wrap items-center gap-2">
            <button class="rounded-xl bg-neutral-950 px-4 py-3 font-bold text-white">
                {{ ($categoryToEdit ?? null) ? 'Update Category' : 'Save Category' }}
            </button>
            @if (($categoryToEdit ?? null))
                <a href="{{ route('admin.categories') }}" class="rounded-xl bg-neutral-100 px-4 py-3 font-bold text-neutral-900">Cancel</a>
            @endif
        </div>
    </form>

    <div class="rounded-3xl bg-white p-5 shadow">
        <table class="admin-table w-full text-left text-sm">
            <thead><tr class="border-b"><th class="py-3">Name</th><th>Products</th><th></th></tr></thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr class="border-b">
                        <td data-label="Name" class="py-3 font-bold">{{ $category->name }}</td>
                        <td data-label="Products">{{ $category->products_count }}</td>
                        <td data-label="Actions" class="admin-table-actions">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="inline-block rounded-lg bg-neutral-100 px-3 py-2 font-bold text-neutral-900">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.delete', $category) }}" onsubmit="return confirm('Delete this category?')">
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
</div>
@endsection
