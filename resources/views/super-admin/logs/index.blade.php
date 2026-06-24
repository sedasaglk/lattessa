@extends('layouts.super-admin')
@section('title', 'Sistem Loglari')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Sistem Loglari</h1>
</div>

{{-- Sistem Ozeti --}}
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Toplam Firma</p>
        <p class="text-2xl font-semibold text-gray-900">{{ $systemStats['total_tenants'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Aktif / Deneme</p>
        <p class="text-2xl font-semibold text-gray-900">{{ $systemStats['active_tenants'] }} / {{ $systemStats['trial_tenants'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Bugun Randevu</p>
        <p class="text-2xl font-semibold text-gray-900">{{ $systemStats['total_appointments_today'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Bugun SMS</p>
        <p class="text-2xl font-semibold text-gray-900">{{ $systemStats['total_sms_today'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Bugun Basarisiz SMS</p>
        <p class="text-2xl font-semibold text-red-600">{{ $systemStats['failed_sms_today'] }}</p>
    </div>
</div>

{{-- Sekmeler --}}
<div class="flex gap-2 mb-4">
    <a href="{{ route('super-admin.logs.index', ['type' => 'activity']) }}"
       class="text-sm px-4 py-2 rounded-lg {{ $type === 'activity' ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
        Aktivite Loglari
    </a>
    <a href="{{ route('super-admin.logs.index', ['type' => 'laravel']) }}"
       class="text-sm px-4 py-2 rounded-lg {{ $type === 'laravel' ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
        Laravel Loglari
    </a>
    <a href="{{ route('super-admin.logs.index', ['type' => 'sms']) }}"
       class="text-sm px-4 py-2 rounded-lg {{ $type === 'sms' ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
        SMS Loglari
    </a>
</div>

@if($type === 'activity')
<div class="bg-white rounded-xl border border-gray-200">
    @if($activityLogs->isEmpty())
        <div class="p-8 text-center"><p class="text-gray-400">Aktivite logu yok.</p></div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($activityLogs as $log)
            <div class="flex items-center justify-between p-4">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $log->description }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $log->company_name }} &bull; {{ $log->user_name ?? 'Sistem' }} &bull; {{ $log->ip_address }}
                    </p>
                </div>
                <p class="text-xs text-gray-400 flex-shrink-0 ml-4">
                    {{ \Carbon\Carbon::parse($log->created_at)->format('d.m.Y H:i') }}
                </p>
            </div>
            @endforeach
        </div>
    @endif
</div>

@elseif($type === 'laravel')
<div class="bg-gray-900 rounded-xl p-4 overflow-auto max-h-screen">
    @if(empty($laravelLog))
        <p class="text-gray-400 text-sm">Log dosyasi bos veya bulunamadi.</p>
    @else
        @foreach($laravelLog as $line)
        @php
            $color = 'text-gray-300';
            if (str_contains($line, '.ERROR')) $color = 'text-red-400';
            elseif (str_contains($line, '.WARNING')) $color = 'text-amber-400';
            elseif (str_contains($line, '.INFO')) $color = 'text-green-400';
        @endphp
        <p class="text-xs font-mono {{ $color }} leading-5">{{ $line }}</p>
        @endforeach
    @endif
</div>

@elseif($type === 'sms')
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h2 class="font-semibold text-gray-900 mb-4">Son 7 Gun SMS Ozeti</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100">
                <tr>
                    <th class="text-left py-2 text-xs text-gray-500">Tarih</th>
                    <th class="text-right py-2 text-xs text-gray-500">Gonderilen</th>
                    <th class="text-right py-2 text-xs text-gray-500">Bekleyen</th>
                    <th class="text-right py-2 text-xs text-gray-500">Basarisiz</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($smsStats->groupBy('date') as $date => $rows)
                <tr>
                    <td class="py-2 text-gray-900">{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</td>
                    <td class="py-2 text-right text-green-600">{{ $rows->where('status', 'sent')->sum('count') }}</td>
                    <td class="py-2 text-right text-amber-600">{{ $rows->where('status', 'pending')->sum('count') }}</td>
                    <td class="py-2 text-right text-red-600">{{ $rows->where('status', 'failed')->sum('count') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
