@extends('layouts.panel')
@section('title', 'Dashboard')
@section('content')

<div class="mb-5">
    <h1 class="text-xl font-semibold text-gray-900">Merhaba, {{ $user->name }} 👋</h1>
    <p class="text-sm text-gray-400">{{ now()->locale('tr')->isoFormat('D MMMM YYYY, dddd') }}</p>
</div>

{{-- Stat Kartlar --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <p class="stat-label">Bugünkü Randevularım</p>
        <p class="stat-value">{{ $todayAppointments->count() }}</p>
        <p class="stat-delta" style="color:#6366F1;">
            {{ $todayAppointments->whereIn('status', ['pending','confirmed'])->count() }} bekliyor
        </p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Bu Hafta</p>
        <p class="stat-value">{{ $weekStats->total ?? 0 }}</p>
        <p class="stat-delta text-gray-400">{{ $weekStats->completed ?? 0 }} tamamlandı</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Bu Ay Ciro</p>
        <p class="stat-value" style="font-size:20px;">{{ number_format($monthStats->revenue ?? 0, 0, ',', '.') }} ₺</p>
        <p class="stat-delta text-gray-400">{{ $monthStats->completed ?? 0 }} randevu</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Bu Ay Prim</p>
        <p class="stat-value" style="font-size:20px; color:#22C55E;">{{ number_format($monthCommission ?? 0, 0, ',', '.') }} ₺</p>
        @if($fixedSalary > 0)
        <p class="stat-delta text-gray-400">+ {{ number_format($fixedSalary, 0, ',', '.') }} ₺ sabit</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Bugünkü Randevularım --}}
    <div class="lg:col-span-2 card p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-900">Bugünkü Randevularım</h2>
            <a href="{{ route('panel.appointments.index', ['tenant_slug' => $tenant->slug]) }}"
               class="text-xs font-medium" style="color:#6366F1;">Takvime Git →</a>
        </div>

        @if($todayAppointments->isNotEmpty())
        <div class="space-y-2">
            @foreach($todayAppointments as $appt)
            <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition">
                <div class="w-1 h-10 rounded-full flex-shrink-0"
                     style="background: {{ match($appt->status) { 'confirmed'=>'#22C55E','pending'=>'#F59E0B','completed'=>'#6366F1','cancelled'=>'#EF4444',default=>'#9CA3AF' } }};"></div>
                <div class="w-14 text-center flex-shrink-0">
                    <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($appt->start_time)->format('H:i') }}</p>
                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($appt->end_time)->format('H:i') }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $appt->customer_name }}</p>
                    <p class="text-xs text-gray-400">{{ $appt->service_name }}</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full flex-shrink-0
                    {{ $appt->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $appt->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                    {{ $appt->status === 'completed' ? 'bg-indigo-100 text-indigo-700' : '' }}
                    {{ $appt->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ match($appt->status) { 'confirmed'=>'Onaylı','pending'=>'Bekliyor','completed'=>'Tamamlandı','cancelled'=>'İptal','no_show'=>'Gelmedi',default=>$appt->status } }}
                </span>
            </div>
            @endforeach
        </div>
        @else
        <div class="py-10 text-center">
            <p class="text-4xl mb-3">📅</p>
            <p class="text-gray-400 text-sm">Bugün için randevunuz yok.</p>
        </div>
        @endif
    </div>

    {{-- Sağ Kolon --}}
    <div class="space-y-4">

        {{-- İzin Uyarısı --}}
        @if($activeLeaves)
        <div class="card p-4 border-l-4 border-amber-400 bg-amber-50">
            <p class="text-sm font-semibold text-amber-700">🌴 Bugün İzinlisiniz</p>
            <p class="text-xs text-amber-600 mt-0.5">
                {{ \Carbon\Carbon::parse($activeLeaves->start_date)->format('d.m') }} –
                {{ \Carbon\Carbon::parse($activeLeaves->end_date)->format('d.m.Y') }}
            </p>
        </div>
        @endif

        {{-- Çalışma Takvimi --}}
        <div class="card p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Çalışma Takvimim</h2>
            <div class="space-y-1.5">
                @foreach([1=>'Pzt',2=>'Sal',3=>'Çar',4=>'Per',5=>'Cum',6=>'Cmt',0=>'Paz'] as $dayNum => $dayLabel)
                @php
                    $sch = $schedules[$dayNum] ?? null;
                    $isToday = now()->dayOfWeek === $dayNum;
                    $isWorking = $sch && !($sch->is_day_off ?? false);
                @endphp
                <div class="flex items-center gap-3 text-sm {{ $isToday ? 'bg-indigo-50 rounded-lg px-2 py-1 -mx-2' : '' }}">
                    <span class="w-8 text-xs font-medium {{ $isToday ? 'text-indigo-600' : 'text-gray-400' }}">{{ $dayLabel }}</span>
                    @if($isWorking)
                        <span class="w-2 h-2 rounded-full bg-green-400 flex-shrink-0"></span>
                        <span class="text-gray-600 text-xs">{{ substr($sch->start_time, 0, 5) }} – {{ substr($sch->end_time, 0, 5) }}</span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-gray-200 flex-shrink-0"></span>
                        <span class="text-gray-300 text-xs">Kapalı</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Bu Ay Performans --}}
        <div class="card p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Bu Ay Performans</h2>
            @php
                $total = $monthStats->total ?? 0;
                $completed = $monthStats->completed ?? 0;
                $cancelled = $monthStats->cancelled ?? 0;
                $noShow = $monthStats->no_show ?? 0;
                $completionRate = $total > 0 ? round($completed / $total * 100) : 0;
            @endphp
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Toplam Randevu</span>
                    <span class="font-medium text-gray-900">{{ $total }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Tamamlanan</span>
                    <span class="font-medium text-green-600">{{ $completed }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">İptal</span>
                    <span class="font-medium text-red-500">{{ $cancelled }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Gelmedi</span>
                    <span class="font-medium text-gray-500">{{ $noShow }}</span>
                </div>
                {{-- Tamamlama oranı bar --}}
                <div class="pt-1">
                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                        <span>Tamamlama Oranı</span>
                        <span>%{{ $completionRate }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all"
                             style="width:{{ $completionRate }}%; background:#6366F1;"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
