<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — {{ $tenant->company_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { brand: { DEFAULT: '#6366F1', light: '#EEF2FF', dark: '#4F46E5' } }
                }
            }
        }
    </script>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366F1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Lattessa">
    <link rel="apple-touch-icon" href="/icons/icon-152x152.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW error:', err));
            });
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; background: #F8F8F8; }

        /* Sidebar */
        .sidebar-link { position: relative; transition: all 0.15s ease; }
        .sidebar-link.active { background: rgba(99,102,241,0.12); }
        .sidebar-link.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 60%; background: #6366F1; border-radius: 0 4px 4px 0; }
        .sidebar-link:not(.active):hover { background: rgba(255,255,255,0.07); }

        /* Cards & UI */
        .card { background: #fff; border: 1px solid #E5E7EB; border-radius: 16px; }
        .btn-primary { background: #111; color: #fff; border-radius: 10px; font-weight: 500; transition: all 0.15s; }
        .btn-primary:hover { background: #333; }
        .btn-secondary { background: #fff; color: #111; border: 1px solid #E5E7EB; border-radius: 10px; font-weight: 500; transition: all 0.15s; }
        input, select, textarea { border: 1px solid #E5E7EB; border-radius: 10px; font-family: 'Inter', sans-serif; font-size: 14px; transition: border-color 0.15s, box-shadow 0.15s; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #6366F1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }

        /* Badges */
        .badge-green { background: #DCFCE7; color: #166534; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }
        .badge-red { background: #FEE2E2; color: #991B1B; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }
        .badge-amber { background: #FEF3C7; color: #92400E; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }
        .badge-blue { background: #DBEAFE; color: #1E40AF; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }
        .badge-gray { background: #F3F4F6; color: #374151; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }
        .badge-indigo { background: #EEF2FF; color: #4338CA; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }

        /* Stats */
        .stat-card { background: #fff; border: 1px solid #E5E7EB; border-radius: 16px; padding: 20px; }
        .stat-card .stat-value { font-size: 28px; font-weight: 700; color: #111; line-height: 1; }
        .stat-card .stat-label { font-size: 12px; color: #9CA3AF; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
        .stat-card .stat-delta { font-size: 12px; font-weight: 500; margin-top: 6px; }

        /* Alerts */
        .alert-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; border-radius: 12px; padding: 12px 16px; font-size: 14px; }
        .alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 12px; padding: 12px 16px; font-size: 14px; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 999px; }

        /* Mobile Bottom Nav */
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: #111; border-top: 1px solid #222; z-index: 50; padding-bottom: env(safe-area-inset-bottom); }
        .bottom-nav-item { display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; padding: 8px 4px; color: #6B7280; transition: color 0.15s; cursor: pointer; text-decoration: none; min-height: 56px; }
        .bottom-nav-item.active { color: #6366F1; }
        .bottom-nav-item span.label { font-size: 10px; margin-top: 3px; font-weight: 500; }
        .bottom-nav-item .icon { font-size: 20px; line-height: 1; }

        /* FAB button */
        .fab { width: 52px; height: 52px; background: #6366F1; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; box-shadow: 0 4px 16px rgba(99,102,241,0.4); margin-top: -16px; }

        /* Mobile slide-over menu */
        .mobile-menu { position: fixed; inset: 0; z-index: 100; }
        .mobile-menu-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); }
        .mobile-menu-panel { position: absolute; right: 0; top: 0; bottom: 0; width: 280px; background: #111; overflow-y: auto; padding-bottom: env(safe-area-inset-bottom); }

        /* Mobile header */
        .mobile-header { background: #111; color: white; padding: 12px 16px; padding-top: calc(12px + env(safe-area-inset-top)); display: flex; align-items: center; justify-content: space-between; }

        /* Mobile content padding */
        @media (max-width: 768px) {
            .main-content { padding-bottom: 80px !important; }
        }

        /* Quick action sheet */
        .quick-action-sheet { position: fixed; inset: 0; z-index: 100; }
        .quick-action-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); }
        .quick-action-panel { position: absolute; bottom: 0; left: 0; right: 0; background: #fff; border-radius: 20px 20px 0 0; padding: 20px; padding-bottom: calc(20px + env(safe-area-inset-bottom)); }

        .table-row:hover { background: #FAFAFA; }
        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-size: 22px; font-weight: 700; color: #111; }
        .page-header p { font-size: 14px; color: #9CA3AF; margin-top: 2px; }
    </style>
    @stack('styles')
</head>
<body class="h-full">

@php
    $role = auth()->user()->role ?? 'personel';
    $isOwner = $role === 'firma_sahibi';
    $isManager = in_array($role, ['firma_sahibi', 'sube_muduru']);
    $isSecretary = in_array($role, ['firma_sahibi', 'sube_muduru', 'sekreter']);
    $slug = $tenant->slug;
@endphp

{{-- ==================== DESKTOP LAYOUT ==================== --}}
<div class="hidden md:flex h-screen overflow-hidden">

    {{-- Desktop Sidebar --}}
    <aside class="w-[220px] flex-shrink-0 flex flex-col" style="background:#111111;">
        <div class="px-5 py-5 border-b border-white/10">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#6366F1;">
                    <span class="text-white font-bold text-sm">L</span>
                </div>
                <div>
                    <p class="text-white font-semibold text-sm leading-tight">Lattessa</p>
                    <p class="text-xs leading-tight" style="color:#9CA3AF;">{{ Str::limit($tenant->company_name, 18) }}</p>
                </div>
            </div>
        </div>

        @if($tenant->status === 'trial' && $isOwner)
        @php $daysLeft = max(0, (int) ceil(now()->diffInHours($tenant->trial_ends_at, false) / 24)); @endphp
        <div class="mx-3 mt-3 px-3 py-2 rounded-lg" style="background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.3);">
            <p class="text-xs font-medium" style="color:#FCD34D;">⏱ {{ $daysLeft }} gün deneme kaldı</p>
            <a href="{{ route('panel.subscription.index', ['tenant_slug' => $slug]) }}" class="text-xs underline" style="color:#FCD34D; opacity:0.8;">Yükselt →</a>
        </div>
        @endif

        <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5">
            @php
            function sidebarLink($route, $label, $icon, $match, $slug) {
                $active = request()->routeIs($match);
                $url = route($route, ['tenant_slug' => $slug]);
                $activeClass = $active ? 'active' : '';
                $textClass = $active ? 'text-white font-medium' : 'font-normal';
                $textStyle = $active ? '' : 'color:#9CA3AF;';
                return "<a href=\"{$url}\" class=\"sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {$activeClass}\"><span class=\"text-base opacity-70\">{$icon}</span><span class=\"{$textClass}\" style=\"{$textStyle}\">{$label}</span></a>";
            }
            @endphp

            {!! sidebarLink('tenant.home', 'Dashboard', '▦', 'tenant.home', $slug) !!}
            {!! sidebarLink('panel.appointments.index', 'Randevular', '◷', 'panel.appointments*', $slug) !!}
            @if($isSecretary) {!! sidebarLink('panel.waiting.index', 'Bekleme Listesi', '◈', 'panel.waiting*', $slug) !!} @endif
            {!! sidebarLink('panel.customers.index', 'Müşteriler', '◉', 'panel.customers*', $slug) !!}
            @if($isSecretary) {!! sidebarLink('panel.crm.index', 'CRM', '◎', 'panel.crm*', $slug) !!} @endif
            {!! sidebarLink('panel.services.index', 'Hizmetler', '✦', 'panel.services*', $slug) !!}
            @if($isSecretary) {!! sidebarLink('panel.packages.index', 'Paketler', '⊞', 'panel.packages*', $slug) !!} @endif
            @if($isManager) {!! sidebarLink('panel.staff.index', 'Personel', '◈', 'panel.staff*', $slug) !!} @endif
            @if($isManager) {!! sidebarLink('panel.payroll.index', 'Bordro', '◑', 'panel.payroll*', $slug) !!}
            @elseif($role === 'personel') {!! sidebarLink('panel.payroll.show', 'Bordrolarım', '◑', 'panel.payroll*', $slug) !!}
            @endif
            {!! sidebarLink('panel.sales.index', 'Satışlar', '◈', 'panel.sales*', $slug) !!}
            @if($isSecretary) {!! sidebarLink('panel.inventory.index', 'Stok', '⊟', 'panel.inventory*', $slug) !!} @endif
            @if($isSecretary) {!! sidebarLink('panel.cash.index', 'Kasa', '◆', 'panel.cash*', $slug) !!} @endif
            {!! sidebarLink('panel.loyalty.index', 'Sadakat', '★', 'panel.loyalty*', $slug) !!}
            @if($isManager) {!! sidebarLink('panel.marketing.index', 'Pazarlama', '◈', 'panel.marketing*', $slug) !!} @endif
            @if($isManager) {!! sidebarLink('panel.branches.index', 'Şubeler', '⊕', 'panel.branches*', $slug) !!} @endif
            @if($isManager) {!! sidebarLink('panel.reports.index', 'Raporlar', '◈', 'panel.reports*', $slug) !!} @endif
            @if($isManager) {!! sidebarLink('panel.whatsapp.index', 'WhatsApp', '◈', 'panel.whatsapp*', $slug) !!} @endif

            <div class="pt-2 mt-2 border-t border-white/10 space-y-0.5">
                {!! sidebarLink('panel.support.index', 'Destek', '?', 'panel.support*', $slug) !!}
                @if($isOwner)
                {!! sidebarLink('panel.invoices.index', 'Faturalar', '◈', 'panel.invoices*', $slug) !!}
                {!! sidebarLink('panel.subscription.index', 'Abonelik', '◈', 'panel.subscription*', $slug) !!}
                @endif
                @if($isManager) {!! sidebarLink('panel.settings.index', 'Ayarlar', '⚙', 'panel.settings*', $slug) !!} @endif
            </div>
        </nav>

        <div class="px-3 py-3 border-t border-white/10">
            <div class="flex items-center gap-2.5 px-2 py-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background:#6366F1;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs truncate" style="color:#6B7280;">{{ match($role) { 'firma_sahibi' => 'Firma Sahibi', 'sube_muduru' => 'Şube Müdürü', 'sekreter' => 'Sekreter', 'personel' => 'Personel', default => $role } }}</p>
                </div>
                <form method="POST" action="{{ route('logout', ['tenant_slug' => $slug]) }}">
                    @csrf
                    <button type="submit" title="Çıkış" class="text-gray-500 hover:text-white transition text-sm">⏏</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Desktop Main --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-100 px-6 py-3.5 flex items-center justify-between flex-shrink-0">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">@yield('title', 'Panel')</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('d F Y, l') }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($isSecretary)
                <a href="{{ route('panel.appointments.create', ['tenant_slug' => $slug]) }}" class="btn-primary text-sm px-4 py-2 inline-flex items-center gap-1.5">
                    <span>+</span> Randevu
                </a>
                @endif
                <a href="{{ route('booking.show', ['tenant_slug' => $slug]) }}" target="_blank" class="btn-secondary text-sm px-4 py-2 inline-flex items-center gap-1.5">
                    <span>↗</span> Online Randevu
                </a>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-6">
            @include('layouts._alerts')
            @yield('content')
        </main>
    </div>
</div>

{{-- ==================== MOBİL LAYOUT ==================== --}}
<div class="md:hidden flex flex-col h-screen">

    {{-- Mobil Header --}}
    <div class="mobile-header flex-shrink-0">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#6366F1;">
                <span class="text-white font-bold text-xs">L</span>
            </div>
            <div>
                <p class="text-white font-semibold text-sm leading-tight">{{ Str::limit($tenant->company_name, 20) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if($isSecretary)
            <a href="{{ route('panel.appointments.create', ['tenant_slug' => $slug]) }}"
               class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xl font-light" style="background:#6366F1;">+</a>
            @endif
            <button onclick="openMobileMenu()" class="text-white p-1">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobil İçerik --}}
    <main class="flex-1 overflow-y-auto p-4 main-content">
        @include('layouts._alerts')
        @yield('content')
    </main>

    {{-- Bottom Navigation --}}
    <nav class="bottom-nav flex-shrink-0">
        <div class="flex items-center">
            <a href="{{ route('tenant.home', ['tenant_slug' => $slug]) }}"
               class="bottom-nav-item {{ request()->routeIs('tenant.home') ? 'active' : '' }}">
                <span class="icon">🏠</span>
                <span class="label">Ana Sayfa</span>
            </a>
            <a href="{{ route('panel.appointments.index', ['tenant_slug' => $slug]) }}"
               class="bottom-nav-item {{ request()->routeIs('panel.appointments*') ? 'active' : '' }}">
                <span class="icon">📅</span>
                <span class="label">Randevular</span>
            </a>

            {{-- FAB - Hızlı Randevu --}}
            @if($isSecretary)
            <div class="flex-1 flex justify-center">
                <a href="{{ route('panel.appointments.create', ['tenant_slug' => $slug]) }}" class="fab">
                    <span style="font-size:26px; line-height:1;">+</span>
                </a>
            </div>
            @else
            <div class="flex-1"></div>
            @endif

            <a href="{{ route('panel.customers.index', ['tenant_slug' => $slug]) }}"
               class="bottom-nav-item {{ request()->routeIs('panel.customers*') ? 'active' : '' }}">
                <span class="icon">👥</span>
                <span class="label">Müşteriler</span>
            </a>
            <button onclick="openMobileMenu()" class="bottom-nav-item {{ request()->routeIs('panel.sales*') || request()->routeIs('panel.cash*') || request()->routeIs('panel.reports*') ? 'active' : '' }}">
                <span class="icon">☰</span>
                <span class="label">Menü</span>
            </button>
        </div>
    </nav>
</div>

{{-- ==================== MOBİL SLIDE MENÜ ==================== --}}
<div id="mobileMenu" class="mobile-menu" style="display:none;" onclick="closeMobileMenuIfOverlay(event)">
    <div class="mobile-menu-overlay"></div>
    <div class="mobile-menu-panel">
        {{-- Profil --}}
        <div class="px-4 py-5 border-b border-white/10" style="padding-top: calc(20px + env(safe-area-inset-top))">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-white" style="background:#6366F1;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-white font-semibold text-sm">{{ auth()->user()->name }}</p>
                    <p class="text-xs" style="color:#9CA3AF;">{{ match($role) { 'firma_sahibi' => 'Firma Sahibi', 'sube_muduru' => 'Şube Müdürü', 'sekreter' => 'Sekreter', 'personel' => 'Personel', default => $role } }}</p>
                </div>
            </div>
        </div>

        {{-- Menü Linkleri --}}
        <nav class="px-3 py-3 space-y-0.5">
            @php
            $menuItems = [
                ['route' => 'tenant.home', 'label' => 'Dashboard', 'icon' => '🏠', 'match' => 'tenant.home', 'show' => true],
                ['route' => 'panel.appointments.index', 'label' => 'Randevular', 'icon' => '📅', 'match' => 'panel.appointments*', 'show' => true],
                ['route' => 'panel.waiting.index', 'label' => 'Bekleme Listesi', 'icon' => '⏳', 'match' => 'panel.waiting*', 'show' => $isSecretary],
                ['route' => 'panel.customers.index', 'label' => 'Müşteriler', 'icon' => '👥', 'match' => 'panel.customers*', 'show' => true],
                ['route' => 'panel.crm.index', 'label' => 'CRM', 'icon' => '🎯', 'match' => 'panel.crm*', 'show' => $isSecretary],
                ['route' => 'panel.services.index', 'label' => 'Hizmetler', 'icon' => '✂️', 'match' => 'panel.services*', 'show' => true],
                ['route' => 'panel.packages.index', 'label' => 'Paketler', 'icon' => '📦', 'match' => 'panel.packages*', 'show' => $isSecretary],
                ['route' => 'panel.staff.index', 'label' => 'Personel', 'icon' => '👤', 'match' => 'panel.staff*', 'show' => $isManager],
                ['route' => 'panel.payroll.index', 'label' => 'Bordro', 'icon' => '💰', 'match' => 'panel.payroll*', 'show' => $isManager],
                ['route' => 'panel.sales.index', 'label' => 'Satışlar', 'icon' => '🛍️', 'match' => 'panel.sales*', 'show' => true],
                ['route' => 'panel.inventory.index', 'label' => 'Stok', 'icon' => '📊', 'match' => 'panel.inventory*', 'show' => $isSecretary],
                ['route' => 'panel.cash.index', 'label' => 'Kasa', 'icon' => '💳', 'match' => 'panel.cash*', 'show' => $isSecretary],
                ['route' => 'panel.loyalty.index', 'label' => 'Sadakat', 'icon' => '⭐', 'match' => 'panel.loyalty*', 'show' => true],
                ['route' => 'panel.marketing.index', 'label' => 'Pazarlama', 'icon' => '📢', 'match' => 'panel.marketing*', 'show' => $isManager],
                ['route' => 'panel.branches.index', 'label' => 'Şubeler', 'icon' => '🏪', 'match' => 'panel.branches*', 'show' => $isManager],
                ['route' => 'panel.reports.index', 'label' => 'Raporlar', 'icon' => '📈', 'match' => 'panel.reports*', 'show' => $isManager],
                ['route' => 'panel.whatsapp.index', 'label' => 'WhatsApp', 'icon' => '💬', 'match' => 'panel.whatsapp*', 'show' => $isManager],
                ['route' => 'panel.support.index', 'label' => 'Destek', 'icon' => '🎧', 'match' => 'panel.support*', 'show' => true],
                ['route' => 'panel.invoices.index', 'label' => 'Faturalar', 'icon' => '🧾', 'match' => 'panel.invoices*', 'show' => $isOwner],
                ['route' => 'panel.subscription.index', 'label' => 'Abonelik', 'icon' => '💎', 'match' => 'panel.subscription*', 'show' => $isOwner],
                ['route' => 'panel.settings.index', 'label' => 'Ayarlar', 'icon' => '⚙️', 'match' => 'panel.settings*', 'show' => $isManager],
            ];
            @endphp

            @foreach($menuItems as $item)
            @if($item['show'])
            @php $active = request()->routeIs($item['match']); @endphp
            <a href="{{ route($item['route'], ['tenant_slug' => $slug]) }}"
               onclick="closeMobileMenu()"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ $active ? 'text-white' : '' }}"
               style="{{ $active ? 'background:rgba(99,102,241,0.15);' : '' }}">
                <span class="text-lg w-6 text-center">{{ $item['icon'] }}</span>
                <span class="{{ $active ? 'text-white font-medium' : 'font-normal' }}" style="{{ $active ? '' : 'color:#9CA3AF;' }}">{{ $item['label'] }}</span>
                @if($active) <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-400"></span> @endif
            </a>
            @endif
            @endforeach
        </nav>

        {{-- Çıkış --}}
        <div class="px-4 py-3 border-t border-white/10 mt-2">
            <form method="POST" action="{{ route('logout', ['tenant_slug' => $slug]) }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-3 py-2.5 w-full rounded-xl text-sm" style="color:#EF4444;">
                    <span class="text-lg">🚪</span>
                    <span>Çıkış Yap</span>
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Mobil Menü Script --}}
<script>
function openMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.style.display = 'none';
    document.body.style.overflow = '';
}
function closeMobileMenuIfOverlay(e) {
    if (e.target === e.currentTarget || e.target.classList.contains('mobile-menu-overlay')) {
        closeMobileMenu();
    }
}
</script>

{{-- iOS PWA Banner --}}
<div id="ios-install-banner" style="display:none; position:fixed; bottom:70px; left:12px; right:12px; z-index:200; background:#1F2937; border:1px solid #374151; border-radius:14px; padding:12px 14px; align-items:center; justify-content:space-between; gap:12px; box-shadow:0 8px 24px rgba(0,0,0,0.3);">
    <div style="display:flex; align-items:center; gap:10px;">
        <img src="/icons/icon-72x72.png" style="width:36px; height:36px; border-radius:8px;">
        <div>
            <p style="color:#fff; font-size:13px; font-weight:600; margin:0;">Uygulamayı Yükle</p>
            <p style="color:#9CA3AF; font-size:11px; margin:0;">Ana ekrana ekle</p>
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
        <div style="background:#6366F1; color:#fff; border-radius:8px; padding:5px 12px; font-size:12px; font-weight:600; cursor:pointer;" onclick="showIOSInstructions()">Ekle</div>
        <div style="color:#6B7280; cursor:pointer; padding:4px 6px; font-size:16px;" onclick="dismissBanner()">✕</div>
    </div>
</div>

<div id="ios-modal" style="display:none; position:fixed; inset:0; z-index:300; background:rgba(0,0,0,0.7); align-items:flex-end; justify-content:center;">
    <div style="background:#1F2937; border-radius:20px 20px 0 0; padding:24px; width:100%; padding-bottom:calc(24px + env(safe-area-inset-bottom));">
        <div style="text-align:center; margin-bottom:16px;">
            <img src="/icons/icon-96x96.png" style="width:56px; height:56px; border-radius:12px; margin-bottom:8px;">
            <p style="color:#fff; font-size:16px; font-weight:700; margin:0;">Lattessa'yı Yükle</p>
            <p style="color:#9CA3AF; font-size:12px; margin:4px 0 0;">Uygulama gibi kullan, hızlı eriş</p>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;background:#374151;border-radius:10px;">
                <span style="font-size:20px;">1️⃣</span>
                <p style="color:#E5E7EB;font-size:12px;margin:0;">Tarayıcıdaki <strong style="color:#fff;">Paylaş ⬆</strong> butonuna bas</p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;background:#374151;border-radius:10px;">
                <span style="font-size:20px;">2️⃣</span>
                <p style="color:#E5E7EB;font-size:12px;margin:0;"><strong style="color:#fff;">"Ana Ekrana Ekle"</strong> seçeneğine dokun</p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;background:#374151;border-radius:10px;">
                <span style="font-size:20px;">3️⃣</span>
                <p style="color:#E5E7EB;font-size:12px;margin:0;"><strong style="color:#fff;">"Ekle"</strong> butonuna bas</p>
            </div>
        </div>
        <div style="background:#6366F1;color:#fff;border-radius:12px;padding:12px;text-align:center;font-size:14px;font-weight:600;cursor:pointer;" onclick="document.getElementById('ios-modal').style.display='none'">Anladım</div>
    </div>
</div>

<script>
const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
const isStandalone = window.navigator.standalone === true;
const dismissed = localStorage.getItem('lattessa-pwa-dismissed');
if (isIOS && !isStandalone && !dismissed) {
    setTimeout(() => { document.getElementById('ios-install-banner').style.display = 'flex'; }, 3000);
}
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    if (!localStorage.getItem('lattessa-pwa-dismissed')) {
        setTimeout(() => {
            const btn = document.querySelector('#ios-install-banner [onclick="showIOSInstructions()"]');
            if (btn) btn.setAttribute('onclick', 'installAndroid()');
            document.getElementById('ios-install-banner').style.display = 'flex';
        }, 3000);
    }
});
function installAndroid() {
    if (deferredPrompt) { deferredPrompt.prompt(); deferredPrompt.userChoice.then(() => { deferredPrompt = null; dismissBanner(); }); }
}
function showIOSInstructions() { document.getElementById('ios-modal').style.display = 'flex'; }
function dismissBanner() { document.getElementById('ios-install-banner').style.display = 'none'; localStorage.setItem('lattessa-pwa-dismissed', '1'); }
</script>

@stack('scripts')
</body>
</html>
