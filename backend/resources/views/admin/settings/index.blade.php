@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
<h1 class="mb-6 text-3xl font-black">Settings</h1>
<form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-2xl rounded-3xl bg-white p-6 shadow">
    @csrf
    <label class="mb-4 block"><span class="mb-1 block text-sm font-bold">WhatsApp Number</span><input class="w-full rounded-xl border px-4 py-3" name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '') }}" required><span class="mt-1 block text-xs text-neutral-500">Use country code, no plus sign. Example: 201000000000</span></label>
    <label class="mb-4 block"><span class="mb-1 block text-sm font-bold">Store Name</span><input class="w-full rounded-xl border px-4 py-3" name="store_name" value="{{ old('store_name', $settings['store_name'] ?? 'NuxtCommerce') }}" required></label>
    <label class="mb-4 block"><span class="mb-1 block text-sm font-bold">Currency</span><input class="w-full rounded-xl border px-4 py-3" name="currency" value="{{ old('currency', $settings['currency'] ?? 'USD') }}" required></label>
    <label class="mb-6 block"><span class="mb-1 block text-sm font-bold">Currency Symbol</span><input class="w-full rounded-xl border px-4 py-3" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '$') }}" required></label>
    <button class="rounded-xl bg-neutral-950 px-5 py-3 font-bold text-white">Save Settings</button>
</form>
@endsection
