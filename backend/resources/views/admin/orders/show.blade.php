@extends('admin.layouts.app')

@section('title', $order->order_number)

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-3xl font-black">{{ $order->order_number }}</h1>
    <a href="{{ route('admin.orders') }}" class="rounded-xl bg-neutral-100 px-4 py-3 font-bold">Back</a>
</div>

<div class="grid gap-6 lg:grid-cols-[1fr_380px]">
    <div class="rounded-3xl bg-white p-5 shadow">
        <h2 class="mb-4 text-xl font-black">Items</h2>
        <table class="admin-table w-full text-left text-sm">
            <thead><tr class="border-b"><th class="py-3">Product</th><th>SKU</th><th>Size</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr></thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-b">
                        <td data-label="Product" class="py-3 font-bold">{{ $item->product_name }}</td>
                        <td data-label="SKU">{{ $item->product_sku }}</td>
                        <td data-label="Size">{{ $item->selected_size ?: '-' }}</td>
                        <td data-label="Qty">{{ $item->quantity }}</td>
                        <td data-label="Unit">${{ number_format($item->unit_price, 2) }}</td>
                        <td data-label="Subtotal">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-5 grid justify-end gap-1 text-right">
            <div>Subtotal: <strong>${{ number_format($order->subtotal, 2) }}</strong></div>
            <div>Discount: <strong>${{ number_format($order->discount_amount, 2) }}</strong></div>
            <div class="text-xl">Total: <strong>${{ number_format($order->total, 2) }}</strong></div>
        </div>
    </div>

    <div class="grid gap-6">
        <div class="rounded-3xl bg-white p-5 shadow">
            <h2 class="mb-4 text-xl font-black">Customer</h2>
            <div class="grid gap-2 text-sm">
                <div><strong>Name:</strong> {{ $order->customer_name }}</div>
                <div><strong>Phone:</strong> {{ $order->customer_phone }}</div>
                <div><strong>Address:</strong> {{ $order->customer_address ?: '-' }}</div>
                <div><strong>Notes:</strong> {{ $order->customer_notes ?: '-' }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="rounded-3xl bg-white p-5 shadow">
            @csrf @method('PATCH')
            <h2 class="mb-4 text-xl font-black">Manage Status</h2>
            <label class="mb-4 block"><span class="mb-1 block text-sm font-bold">Status</span><select class="w-full rounded-xl border px-4 py-3" name="status">@foreach ($statuses as $status)<option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
            <label class="mb-4 block"><span class="mb-1 block text-sm font-bold">Admin Notes</span><textarea class="h-28 w-full rounded-xl border px-4 py-3" name="admin_notes">{{ $order->admin_notes }}</textarea></label>
            <button class="rounded-xl bg-neutral-950 px-4 py-3 font-bold text-white">Update Order</button>
        </form>
    </div>
</div>
@endsection
