<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - {{ $storeName ?? 'NuxtCommerce' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Mobile-only "app" feel + prevent any horizontal scroll */
        @media (max-width: 1023.98px) {
            html, body { overflow-x: hidden; }
        }

        /* Mobile-only: render tables as vertical cards (no x-scroll). */
        @media (max-width: 767.98px) {
            .admin-table { width: 100%; }
            .admin-table thead { display: none; }
            .admin-table tbody { display: block; }
            .admin-table tr {
                display: block;
                border: 1px solid #e5e5e5;
                border-radius: 1rem;
                padding: .75rem;
                margin-bottom: .75rem;
                background: #fff;
            }
            .admin-table td {
                display: flex;
                width: 100%;
                padding: .5rem 0;
                border: 0 !important;
                justify-content: space-between;
                gap: 1rem;
                word-break: break-word;
            }
            .admin-table td::before {
                content: attr(data-label);
                font-weight: 800;
                color: #525252;
                flex: 0 0 auto;
                padding-right: .75rem;
            }
            .admin-table td.admin-table-actions {
                display: block;
            }
            .admin-table td.admin-table-actions::before {
                content: "";
                display: none;
            }
        }
    </style>
</head>
<body class="bg-neutral-100 text-neutral-950 overflow-x-hidden">
    <div class="min-h-screen lg:flex">
        <input id="admin-nav" type="checkbox" class="peer hidden" />

        <header class="sticky top-0 z-30 border-b border-neutral-200 bg-white/90 backdrop-blur lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex min-w-0 items-center gap-3">
                    <label for="admin-nav" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-neutral-100 font-black text-neutral-900 active:scale-[0.99]">
                        ☰
                    </label>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-black text-neutral-900">{{ $storeName ?? 'NuxtCommerce' }}</div>
                        <div class="truncate text-xs font-semibold text-neutral-500">@yield('title', 'Admin')</div>
                    </div>
                </div>
            </div>
        </header>

        <label for="admin-nav" class="fixed inset-0 z-30 bg-black/40 opacity-0 pointer-events-none transition peer-checked:opacity-100 peer-checked:pointer-events-auto lg:hidden"></label>

        <aside class="fixed inset-y-0 left-0 z-40 w-[18rem] -translate-x-full bg-neutral-950 text-white transition peer-checked:translate-x-0 lg:static lg:z-auto lg:w-64 lg:translate-x-0 p-5">
            <div class="mb-8 flex items-center justify-between">
                <div class="text-2xl font-black">{{ $storeName ?? 'NuxtCommerce' }}</div>
                <label for="admin-nav" class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 font-black text-white">
                    ✕
                </label>
            </div>
            <nav class="grid gap-2 text-sm font-semibold">
                <a class="rounded-xl px-3 py-2 hover:bg-white/10" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="rounded-xl px-3 py-2 hover:bg-white/10" href="{{ route('admin.products') }}">Products</a>
                <a class="rounded-xl px-3 py-2 hover:bg-white/10" href="{{ route('admin.categories') }}">Categories</a>
                <a class="rounded-xl px-3 py-2 hover:bg-white/10" href="{{ route('admin.orders') }}">Orders</a>
                <a class="rounded-xl px-3 py-2 hover:bg-white/10" href="{{ route('admin.discounts') }}">Discounts</a>
                <a class="rounded-xl px-3 py-2 hover:bg-white/10" href="{{ route('admin.settings') }}">Settings</a>
                <form method="POST" action="{{ route('admin.logout') }}" class="pt-4">
                    @csrf
                    <button class="w-full rounded-xl bg-white/10 px-3 py-2 text-left hover:bg-white/20">Logout</button>
                </form>
            </nav>
        </aside>

        <main class="min-w-0 flex-1 p-4 pt-5 lg:p-8">
            @if (session('success'))
                <div class="mb-5 rounded-2xl bg-green-100 px-4 py-3 text-green-800">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-2xl bg-red-100 px-4 py-3 text-red-800">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
