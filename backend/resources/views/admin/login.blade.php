<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - {{ \App\Models\Setting::value('store_name', 'NuxtCommerce') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-neutral-950 text-white grid place-items-center p-5">
    <form method="POST" action="{{ route('admin.authenticate') }}" class="w-full max-w-md rounded-[2rem] bg-white p-8 text-neutral-950 shadow-2xl">
        @csrf
        <h1 class="mb-2 text-3xl font-black">Admin Login</h1>
        <p class="mb-6 text-sm text-neutral-500">Default: admin@example.com / password</p>
        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-100 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-semibold">Email</span>
            <input class="w-full rounded-xl border px-4 py-3" name="email" type="email" value="{{ old('email') }}" required autofocus>
        </label>
        <label class="mb-6 block">
            <span class="mb-1 block text-sm font-semibold">Password</span>
            <input class="w-full rounded-xl border px-4 py-3" name="password" type="password" required>
        </label>
        <button class="w-full rounded-xl bg-neutral-950 px-4 py-3 font-bold text-white">Login</button>
    </form>
</body>
</html>
