@extends('admin.layouts.app')

@section('title', 'Orders')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h1 class="text-3xl font-black">Orders</h1>
    <form class="flex gap-2">
        <select class="rounded-xl border px-4 py-3" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-neutral-950 px-4 py-3 font-bold text-white">Filter</button>
    </form>
</div>

<div class="rounded-3xl bg-white p-5 shadow">
    <table class="admin-table w-full text-left text-sm">
        <thead><tr class="border-b"><th class="py-3">Order</th><th>Customer</th><th>Phone</th><th>Status</th><th>Total</th><th>Date</th><th></th></tr></thead>
        <tbody>
            @forelse ($orders as $order)
                <tr class="border-b">
                    <td data-label="Order" class="py-3 font-bold">{{ $order->order_number }}</td>
                    <td data-label="Customer">{{ $order->customer_name }}</td>
                    <td data-label="Phone">{{ $order->customer_phone }}</td>
                    <td data-label="Status">{{ ucfirst($order->status) }}</td>
                    <td data-label="Total">${{ number_format($order->total, 2) }}</td>
                    <td data-label="Date">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    <td data-label="Actions" class="admin-table-actions">
                        <a class="rounded-lg bg-neutral-100 px-3 py-2 font-bold" href="{{ route('admin.orders.show', $order) }}">View</a>
                    </td>
                </tr>
            @empty
                <tr><td class="py-6 text-neutral-500" colspan="7">No orders found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-5">{{ $orders->links() }}</div>
</div>
@endsection
