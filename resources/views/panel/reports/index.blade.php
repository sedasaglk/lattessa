@extends('layouts.panel')
@section('title', 'Raporlar')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Raporlar</h1>
</div>

{{-- Period Filtresi --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <form method="GET" class="flex items-center gap-2 flex-wrap">
        @foreach([
            'today' => 'Bugun',
            'this_week' => 'Bu Hafta',
            'this_month' => 'Bu Ay',
            'last_month' => 'Gecen Ay',
            'this_year' => 'Bu Yil',
        ] as $value => $label)
        <a href="?period={{ $value }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
               {{ $period === $value ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ $label }}
        </a>
        @endforeach
        <span class="text-xs text-gray-400 ml-2">
            {{ \Carbon\Carbon::parse($startDate)->format('d.m.Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->format('d.m.Y') }}
        </span>
    </form>
</div>

{{-- Ana Metrikler --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Toplam Ciro</p>
        <p class="text-2xl font-semibold text-green-600 mt-1">{{ number_format($totalRevenue, 2, ',', '.') }} TL</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Toplam Gider</p>
        <p class="text-2xl font-semibold text-red-600 mt-1">{{ number_format($totalExpense, 2, ',', '.') }} TL</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Net Kar</p>
        <p class="text-2xl font-semibold {{ ($totalRevenue - $totalExpense) >= 0 ? 'text-gray-900' : 'text-red-600' }} mt-1">
            {{ number_format($totalRevenue - $totalExpense, 2, ',', '.') }} TL
        </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Yeni Musteri</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $newCustomers }}</p>
    </div>
</div>

{{-- Randevu Ozeti --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <h2 class="font-semibold text-gray-900 mb-4">Randevu Ozeti</h2>
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="text-center">
            <p class="text-3xl font-semibold text-gray-900">{{ $appointmentStats->total ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Toplam</p>
        </div>
        <div class="text-center">
            <p class="text-3xl font-semibold text-green-600">{{ $appointmentStats->completed ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Tamamlandi</p>
        </div>
        <div class="text-center">
            <p class="text-3xl font-semibold text-red-500">{{ $appointmentStats->cancelled ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Iptal</p>
        </div>
        <div class="text-center">
            <p class="text-3xl font-semibold text-gray-400">{{ $appointmentStats->no_show ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Gelmedi</p>
        </div>
        <div class="text-center">
            <p class="text-3xl font-semibold text-blue-600">{{ $appointmentStats->online ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Online</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

    {{-- Personel Performansi --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-4">Personel Performansi</h2>
        @if($staffPerformance->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Bu donemde tamamlanan randevu yok.</p>
        @else
            <div class="space-y-3">
                @foreach($staffPerformance as $staff)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $staff->staff_name }}</p>
                        <p class="text-xs text-gray-500">{{ $staff->total_appointments }} randevu</p>
                    </div>
                    <span class="text-sm font-semibold text-green-600">
                        {{ number_format($staff->total_revenue, 0, ',', '.') }} TL
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Hizmet Performansi --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-4">En Cok Yapilan Hizmetler</h2>
        @if($servicePerformance->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Bu donemde tamamlanan randevu yok.</p>
        @else
            <div class="space-y-3">
                @foreach($servicePerformance as $service)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $service->service_name }}</p>
                        <p class="text-xs text-gray-500">{{ $service->total_count }} kez yapildi</p>
                    </div>
                    <span class="text-sm font-semibold text-green-600">
                        {{ number_format($service->total_revenue, 0, ',', '.') }} TL
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

{{-- Gunluk Ciro Tablosu --}}
@if($dailyRevenue->isNotEmpty() || $dailyAppointments->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h2 class="font-semibold text-gray-900 mb-4">Gunluk Detay</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left py-2 px-3 text-xs text-gray-500 font-medium">Tarih</th>
                    <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Ciro</th>
                    <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Randevu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php
                    $start = \Carbon\Carbon::parse($startDate);
                    $end = \Carbon\Carbon::parse($endDate);
                    $current = $start->copy();
                @endphp
                @while($current->lte($end))
                    @php
                        $dateStr = $current->format('Y-m-d');
                        $revenue = $dailyRevenue->get($dateStr)?->total ?? 0;
                        $appts = $dailyAppointments->get($dateStr)?->total ?? 0;
                    @endphp
                    @if($revenue > 0 || $appts > 0)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-3 text-gray-700">{{ $current->format('d.m.Y') }}</td>
                        <td class="py-2 px-3 text-right font-medium text-green-600">
                            {{ $revenue > 0 ? number_format($revenue, 0, ',', '.') . ' TL' : '-' }}
                        </td>
                        <td class="py-2 px-3 text-right text-gray-700">
                            {{ $appts > 0 ? $appts : '-' }}
                        </td>
                    </tr>
                    @endif
                    @php $current->addDay() @endphp
                @endwhile
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
