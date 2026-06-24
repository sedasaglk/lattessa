@extends('layouts.panel')
@section('title', 'Dashboard')
@section('content')

{{-- Stat Kartlar --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <p class="stat-label">Bugünkü Randevu</p>
        <p class="stat-value">{{ $todayAppointments ?? 0 }}</p>
        <p class="stat-delta" style="color:#6366F1;">{{ $pendingAppointments ?? 0 }} bekliyor</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Bugünkü Ciro</p>
        <p class="stat-value" style="font-size:22px;">{{ number_format($todayRevenue ?? 0, 0, ',', '.') }} ₺</p>
        <p class="stat-delta text-gray-400">Bu ay: {{ number_format($monthRevenue ?? 0, 0, ',', '.') }} ₺</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Toplam Müşteri</p>
        <p class="stat-value">{{ $totalCustomers ?? 0 }}</p>
        <p class="stat-delta" style="color:#22C55E;">+{{ $newCustomersThisMonth ?? 0 }} bu ay</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Aktif Personel</p>
        <p class="stat-value">{{ $activeStaff ?? 0 }}</p>
        <p class="stat-delta text-gray-400">{{ $totalStaff ?? 0 }} toplam</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Bugünkü Randevular --}}
    <div class="lg:col-span-2 card p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-900">Bugünkü Randevular</h2>
            <a href="{{ route('panel.appointments.index', ['tenant_slug' => $tenant->slug]) }}"
               class="text-xs font-medium" style="color:#6366F1;">Tümü →</a>
        </div>

        @if(isset($todayAppointmentList) && $todayAppointmentList->isNotEmpty())
        <div class="space-y-2">
            @foreach($todayAppointmentList as $appt)
            <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition">
                <div class="w-1 h-10 rounded-full flex-shrink-0"
                     style="background: {{ match($appt->status) { 'confirmed' => '#22C55E', 'pending' => '#F59E0B', 'completed' => '#6366F1', 'cancelled' => '#EF4444', default => '#9CA3AF' } }};"></div>
                <div class="w-12 text-center flex-shrink-0">
                    <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($appt->start_time)->format('H:i') }}</p>
                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($appt->start_time)->format('d/m') }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $appt->customer_name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $appt->service_name }} · {{ $appt->staff_name }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-sm font-semibold text-gray-700">{{ number_format($appt->price, 0, ',', '.') }} ₺</span>
                    <span class="badge-{{ match($appt->status) { 'confirmed' => 'green', 'pending' => 'amber', 'completed' => 'indigo', 'cancelled' => 'red', default => 'gray' } }}">
                        {{ match($appt->status) { 'confirmed' => 'Onaylı', 'pending' => 'Bekliyor', 'completed' => 'Tamamlandı', 'cancelled' => 'İptal', default => $appt->status } }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="py-10 text-center">
            <p class="text-gray-400 text-sm">Bugün henüz randevu yok.</p>
            <a href="{{ route('panel.appointments.create', ['tenant_slug' => $tenant->slug]) }}"
               class="inline-block mt-3 btn-primary text-sm px-4 py-2">
                Randevu Oluştur
            </a>
        </div>
        @endif
    </div>

    {{-- Sag Kolon --}}
    <div class="space-y-4">

        {{-- Hizli islemler --}}
        <div class="card p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Hızlı İşlem</h2>
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('panel.appointments.create', ['tenant_slug' => $tenant->slug]) }}"
                   class="flex flex-col items-center gap-1.5 p-3 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 transition text-center">
                    <span class="text-xl">◷</span>
                    <span class="text-xs font-medium text-gray-700">Randevu</span>
                </a>
                <a href="{{ route('panel.customers.create', ['tenant_slug' => $tenant->slug]) }}"
                   class="flex flex-col items-center gap-1.5 p-3 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 transition text-center">
                    <span class="text-xl">◉</span>
                    <span class="text-xs font-medium text-gray-700">Müşteri</span>
                </a>
                <a href="{{ route('panel.sales.create', ['tenant_slug' => $tenant->slug]) }}"
                   class="flex flex-col items-center gap-1.5 p-3 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 transition text-center">
                    <span class="text-xl">◆</span>
                    <span class="text-xs font-medium text-gray-700">Satış</span>
                </a>
                <a href="{{ route('panel.cash.index', ['tenant_slug' => $tenant->slug]) }}"
                   class="flex flex-col items-center gap-1.5 p-3 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 transition text-center">
                    <span class="text-xl">◑</span>
                    <span class="text-xs font-medium text-gray-700">Kasa</span>
                </a>
            </div>
        </div>

        {{-- Son musteriler --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-900">Son Müşteriler</h2>
                <a href="{{ route('panel.customers.index', ['tenant_slug' => $tenant->slug]) }}"
                   class="text-xs font-medium" style="color:#6366F1;">Tümü →</a>
            </div>
            @if(isset($recentCustomers) && $recentCustomers->isNotEmpty())
            <div class="space-y-2">
                @foreach($recentCustomers as $c)
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                         style="background:#6366F1;">
                        {{ strtoupper(substr($c->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $c->name }}</p>
                        <p class="text-xs text-gray-400">{{ $c->phone }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-3">Henüz müşteri yok.</p>
            @endif
        </div>

    </div>
</div>

@endsection
