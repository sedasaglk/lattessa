<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin') - Lattessa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<nav class="bg-gray-900 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <span class="text-white font-semibold">Lattessa</span>
        <span class="text-gray-500 text-xs">Super Admin</span>
    </div>
    <div class="flex items-center gap-4">
        <span class="text-gray-400 text-sm">{{ auth()->guard('super_admin')->user()->name }}</span>
        <form method="POST" action="{{ route('super-admin.logout') }}">
            @csrf
            <button type="submit" class="text-gray-400 hover:text-white text-sm transition">Cikis</button>
        </form>
    </div>
</nav>

<div class="flex min-h-screen">
    <aside class="w-48 bg-white border-r border-gray-200 py-4 flex-shrink-0">
        <nav class="space-y-1 px-2">
            <a href="{{ route('super-admin.dashboard') }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('super-admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                Dashboard
            </a>
            <a href="{{ route('super-admin.tenants.index') }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('super-admin.tenants*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                Firmalar
            </a>
            <a href="{{ route('super-admin.packages.index') }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('super-admin.packages*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                Paketler
            </a>
            <a href="{{ route('super-admin.support.index') }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('super-admin.support*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                Destek Talepleri
            </a>
            <a href="{{ route('super-admin.sms.index') }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('super-admin.sms*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                SMS Yonetimi
            </a>
            <a href="{{ route('super-admin.logs.index') }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('super-admin.logs*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                Sistem Loglari
            </a>
            <a href="{{ route('super-admin.2fa.setup') }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('super-admin.2fa*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                2FA Güvenlik
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-6">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>
</div>

</body>
</html>
