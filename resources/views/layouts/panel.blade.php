<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    colors: {
                        brand: { DEFAULT: '#6366F1', light: '#EEF2FF', dark: '#4F46E5' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #F8F8F8; }
        .sidebar-link { position: relative; transition: all 0.15s ease; }
        .sidebar-link.active { background: rgba(99,102,241,0.12); color: #fff; }
        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 60%;
            background: #6366F1;
            border-radius: 0 4px 4px 0;
        }
        .sidebar-link:not(.active):hover { background: rgba(255,255,255,0.07); }
        .card { background: #fff; border: 1px solid #E5E7EB; border-radius: 16px; }
        .btn-primary { background: #111; color: #fff; border-radius: 10px; font-weight: 500; transition: all 0.15s; }
        .btn-primary:hover { background: #333; }
        .btn-secondary { background: #fff; color: #111; border: 1px solid #E5E7EB; border-radius: 10px; font-weight: 500; transition: all 0.15s; }
        .btn-secondary:hover { background: #F8F8F8; }
        input, select, textarea {
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #6366F1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }
        .badge-green { background: #DCFCE7; color: #166534; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }
        .badge-red { background: #FEE2E2; color: #991B1B; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }
        .badge-amber { background: #FEF3C7; color: #92400E; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }
        .badge-blue { background: #DBEAFE; color: #1E40AF; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }
        .badge-gray { background: #F3F4F6; color: #374151; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }
        .badge-indigo { background: #EEF2FF; color: #4338CA; border-radius: 999px; font-size: 12px; font-weight: 500; padding: 2px 10px; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 999px; }
        .stat-card { background: #fff; border: 1px solid #E5E7EB; border-radius: 16px; padding: 20px; }
        .stat-card .stat-value { font-size: 28px; font-weight: 700; color: #111; line-height: 1; }
        .stat-card .stat-label { font-size: 12px; color: #9CA3AF; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
        .stat-card .stat-delta { font-size: 12px; font-weight: 500; margin-top: 6px; }
        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-size: 22px; font-weight: 700; color: #111; }
        .page-header p { font-size: 14px; color: #9CA3AF; margin-top: 2px; }
        .table-row:hover { background: #FAFAFA; }
        .alert-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; border-radius: 12px; padding: 12px 16px; font-size: 14px; }
        .alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 12px; padding: 12px 16px; font-size: 14px; }
    </style>
    @stack('styles')
</head>
<body class="h-full">

<div class="flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    <aside class="w-[220px] flex-shrink-0 flex flex-col" style="background:#111111;">
        {{-- Logo --}}
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

        {{-- Deneme uyarisi --}}
        @if($tenant->status === 'trial')
        @php $daysLeft = max(0, (int) ceil(now()->diffInHours($tenant->trial_ends_at, false) / 24)); @endphp
        <div class="mx-3 mt-3 px-3 py-2 rounded-lg" style="background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.3);">
            <p class="text-xs font-medium" style="color:#FCD34D;">⏱ {{ $daysLeft }} gün deneme kaldı</p>
            <a href="{{ route('panel.subscription.index', ['tenant_slug' => $tenant->slug]) }}"
               class="text-xs underline" style="color:#FCD34D; opacity:0.8;">Yükselt →</a>
        </div>
        @endif

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5">
            @php
            $navItems = [
                ['route' => 'tenant.home', 'label' => 'Dashboard', 'icon' => '▦', 'match' => 'tenant.home'],
                ['route' => 'panel.appointments.index', 'label' => 'Randevular', 'icon' => '◷', 'match' => 'panel.appointments*'],
                ['route' => 'panel.waiting.index', 'label' => 'Bekleme Listesi', 'icon' => '◈', 'match' => 'panel.waiting*'],
                ['route' => 'panel.customers.index', 'label' => 'Müşteriler', 'icon' => '◉', 'match' => 'panel.customers*'],
                ['route' => 'panel.crm.index', 'label' => 'CRM', 'icon' => '◎', 'match' => 'panel.crm*'],
                ['route' => 'panel.services.index', 'label' => 'Hizmetler', 'icon' => '✦', 'match' => 'panel.services*'],
                ['route' => 'panel.packages.index', 'label' => 'Paketler', 'icon' => '⊞', 'match' => 'panel.packages*'],
                ['route' => 'panel.staff.index', 'label' => 'Personel', 'icon' => '◈', 'match' => 'panel.staff*'],
                ['route' => 'panel.payroll.index', 'label' => 'Bordro', 'icon' => '◑', 'match' => 'panel.payroll*'],
                ['route' => 'panel.sales.index', 'label' => 'Satışlar', 'icon' => '◈', 'match' => 'panel.sales*'],
                ['route' => 'panel.inventory.index', 'label' => 'Stok', 'icon' => '⊟', 'match' => 'panel.inventory*'],
                ['route' => 'panel.cash.index', 'label' => 'Kasa', 'icon' => '◆', 'match' => 'panel.cash*'],
                ['route' => 'panel.loyalty.index', 'label' => 'Sadakat', 'icon' => '★', 'match' => 'panel.loyalty*'],
                ['route' => 'panel.marketing.index', 'label' => 'Pazarlama', 'icon' => '◈', 'match' => 'panel.marketing*'],
                ['route' => 'panel.branches.index', 'label' => 'Şubeler', 'icon' => '⊕', 'match' => 'panel.branches*'],
                ['route' => 'panel.reports.index', 'label' => 'Raporlar', 'icon' => '◈', 'match' => 'panel.reports*'],
            ];
            @endphp

            @foreach($navItems as $item)
            <a href="{{ route($item['route'], ['tenant_slug' => $tenant->slug]) }}"
               class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs($item['match']) ? 'active' : '' }}">
                <span class="text-base opacity-70">{{ $item['icon'] }}</span>
                <span class="{{ request()->routeIs($item['match']) ? 'text-white font-medium' : 'font-normal' }}" style="{{ request()->routeIs($item['match']) ? '' : 'color:#9CA3AF;' }}">
                    {{ $item['label'] }}
                </span>
            </a>
            @endforeach

            <div class="pt-2 mt-2 border-t border-white/10 space-y-0.5">
                <a href="{{ route('panel.whatsapp.index', ['tenant_slug' => $tenant->slug]) }}"
                   class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('panel.whatsapp*') ? 'active' : '' }}">
                    <span class="text-base opacity-70">◈</span>
                    <span class="{{ request()->routeIs('panel.whatsapp*') ? 'text-white font-medium' : 'font-normal' }}" style="{{ request()->routeIs('panel.whatsapp*') ? '' : 'color:#9CA3AF;' }}">WhatsApp</span>
                </a>
                <a href="{{ route('panel.support.index', ['tenant_slug' => $tenant->slug]) }}"
                   class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('panel.support*') ? 'active' : '' }}">
                    <span class="text-base opacity-70">?</span>
                    <span class="{{ request()->routeIs('panel.support*') ? 'text-white font-medium' : 'font-normal' }}" style="{{ request()->routeIs('panel.support*') ? '' : 'color:#9CA3AF;' }}">Destek</span>
                </a>
                <a href="{{ route('panel.subscription.index', ['tenant_slug' => $tenant->slug]) }}"
                   class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('panel.subscription*') ? 'active' : '' }}">
                    <span class="text-base opacity-70">◈</span>
                    <span class="{{ request()->routeIs('panel.subscription*') ? 'text-white font-medium' : 'font-normal' }}" style="{{ request()->routeIs('panel.subscription*') ? '' : 'color:#9CA3AF;' }}">Abonelik</span>
                </a>
                <a href="{{ route('panel.settings.index', ['tenant_slug' => $tenant->slug]) }}"
                   class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('panel.settings*') ? 'active' : '' }}">
                    <span class="text-base opacity-70">⚙</span>
                    <span class="{{ request()->routeIs('panel.settings*') ? 'text-white font-medium' : 'font-normal' }}" style="{{ request()->routeIs('panel.settings*') ? '' : 'color:#9CA3AF;' }}">Ayarlar</span>
                </a>
            </div>
        </nav>

        {{-- Kullanici --}}
        <div class="px-3 py-3 border-t border-white/10">
            <div class="flex items-center gap-2.5 px-2 py-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                     style="background:#6366F1;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs truncate" style="color:#6B7280;">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout', ['tenant_slug' => $tenant->slug]) }}">
                    @csrf
                    <button type="submit" title="Çıkış" class="text-gray-500 hover:text-white transition text-sm">⏏</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-100 px-6 py-3.5 flex items-center justify-between flex-shrink-0">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">@yield('title', 'Panel')</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('d F Y, l') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('panel.appointments.create', ['tenant_slug' => $tenant->slug]) }}"
                   class="btn-primary text-sm px-4 py-2 inline-flex items-center gap-1.5">
                    <span>+</span> Randevu
                </a>
                <a href="{{ route('booking.show', ['tenant_slug' => $tenant->slug]) }}" target="_blank"
                   class="btn-secondary text-sm px-4 py-2 inline-flex items-center gap-1.5">
                    <span>↗</span> Online Randevu
                </a>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-6">
            @if(session('success'))
                <div class="alert-success mb-5 flex items-center gap-2">
                    <span>✓</span> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert-error mb-5 flex items-center gap-2">
                    <span>✕</span> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert-error mb-5">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="flex items-center gap-2"><span>✕</span> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>

</div>

@stack('scripts')
</body>
</html>
