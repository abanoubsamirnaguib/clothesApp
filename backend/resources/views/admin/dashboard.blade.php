@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <h1 class="text-3xl font-black">Dashboard</h1>
    <a href="{{ route('admin.products.create') }}" class="rounded-xl bg-neutral-950 px-4 py-3 text-sm font-bold text-white">Add Product</a>
</div>

<div class="mb-8 grid gap-4 md:grid-cols-4">
    <div class="rounded-3xl bg-white p-5 shadow"><div class="text-sm text-neutral-500">Orders Today</div><div class="text-3xl font-black">{{ $ordersToday }}</div></div>
    <div class="rounded-3xl bg-white p-5 shadow"><div class="text-sm text-neutral-500">Pending Orders</div><div class="text-3xl font-black">{{ $pendingOrders }}</div></div>
    <div class="rounded-3xl bg-white p-5 shadow"><div class="text-sm text-neutral-500">Products</div><div class="text-3xl font-black">{{ $totalProducts }}</div></div>
    <div class="rounded-3xl bg-white p-5 shadow"><div class="text-sm text-neutral-500">Revenue</div><div class="text-3xl font-black">${{ number_format($revenue, 2) }}</div></div>
</div>

<div class="rounded-3xl bg-white p-5 shadow">
    <h2 class="mb-4 text-xl font-black">Latest Orders</h2>
    <div class="overflow-auto">
        <table class="admin-table w-full text-left text-sm">
            <thead><tr class="border-b"><th class="py-3">Order</th><th>Customer</th><th>Status</th><th>Total</th><th></th></tr></thead>
            <tbody>
                @forelse ($latestOrders as $order)
                    <tr class="border-b">
                        <td data-label="Order" class="py-3 font-bold">{{ $order->order_number }}</td>
                        <td data-label="Customer">{{ $order->customer_name }}</td>
                        <td data-label="Status">{{ ucfirst($order->status) }}</td>
                        <td data-label="Total">${{ number_format($order->total, 2) }}</td>
                        <td data-label="Actions" class="admin-table-actions"><a class="font-bold underline" href="{{ route('admin.orders.show', $order) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td class="py-6 text-neutral-500" colspan="5">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
