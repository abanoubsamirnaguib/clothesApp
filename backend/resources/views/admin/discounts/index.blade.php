@extends('admin.layouts.app')

@section('title', 'Discounts')

@section('content')
<h1 class="mb-6 text-3xl font-black">Discounts</h1>
<div class="grid gap-6 lg:grid-cols-[420px_1fr]">
    <form method="POST" action="{{ route('admin.discounts.store') }}" class="rounded-3xl bg-white p-5 shadow">
        @csrf
        <h2 class="mb-4 text-xl font-black">Add / Update Discount</h2>
        <label class="mb-4 block"><span class="mb-1 block text-sm font-bold">Code</span><input class="w-full rounded-xl border px-4 py-3 uppercase" name="code" required></label>
        <label class="mb-4 block"><span class="mb-1 block text-sm font-bold">Type</span><select class="w-full rounded-xl border px-4 py-3" name="type"><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select></label>
        <label class="mb-4 block"><span class="mb-1 block text-sm font-bold">Value</span><input class="w-full rounded-xl border px-4 py-3" name="value" type="number" step="0.01" min="0" required></label>
        <label class="mb-4 block"><span class="mb-1 block text-sm font-bold">Minimum Order</span><input class="w-full rounded-xl border px-4 py-3" name="min_order_amount" type="number" step="0.01" min="0"></label>
        <label class="mb-4 block"><span class="mb-1 block text-sm font-bold">Max Uses</span><input class="w-full rounded-xl border px-4 py-3" name="max_uses" type="number" min="1"></label>
        <label class="mb-4 block"><span class="mb-1 block text-sm font-bold">Expires At</span><input class="w-full rounded-xl border px-4 py-3" name="expires_at" type="datetime-local"></label>
        <label class="mb-4 flex items-center gap-2 font-bold"><input name="is_active" type="checkbox" value="1" checked> Active</label>
        <button class="rounded-xl bg-neutral-950 px-4 py-3 font-bold text-white">Save Discount</button>
    </form>

    <div class="rounded-3xl bg-white p-5 shadow">
        <table class="admin-table w-full text-left text-sm">
            <thead><tr class="border-b"><th class="py-3">Code</th><th>Type</th><th>Value</th><th>Used</th><th>Active</th><th></th></tr></thead>
            <tbody>
                @foreach ($discounts as $discount)
                    <tr class="border-b">
                        <td data-label="Code" class="py-3 font-bold">{{ $discount->code }}</td>
                        <td data-label="Type">{{ ucfirst($discount->type) }}</td>
                        <td data-label="Value">{{ $discount->type === 'percentage' ? $discount->value.'%' : '$'.number_format($discount->value, 2) }}</td>
                        <td data-label="Used">{{ $discount->used_count }}{{ $discount->max_uses ? ' / '.$discount->max_uses : '' }}</td>
                        <td data-label="Active">{{ $discount->is_active ? 'Yes' : 'No' }}</td>
                        <td data-label="Actions" class="admin-table-actions">
                            <form method="POST" action="{{ route('admin.discounts.delete', $discount) }}" onsubmit="return confirm('Delete this discount?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg bg-red-100 px-3 py-2 font-bold text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
