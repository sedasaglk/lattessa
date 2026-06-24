@extends('layouts.super-admin')
@section('title', 'Dashboard')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
    <p class="text-gray-500 text-sm mt-1">{{ now()->format('d.m.Y H:i') }}</p>
</div>

{{-- Ana Metrikler --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Toplam Firma</p>
        <p class="text-3xl font-semibold text-gray-900 mt-1">{{ $totalTenants }}</p>
        <p class="text-xs text-gray-400 mt-1">Bu ay +{{ $newThisMonth }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Aktif</p>
        <p class="text-3xl font-semibold text-green-600 mt-1">{{ $activeTenants }}</p>
        <p class="text-xs text-gray-400 mt-1">Odeme yapan</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Deneme</p>
        <p class="text-3xl font-semibold text-amber-600 mt-1">{{ $trialTenants }}</p>
        <p class="text-xs text-gray-400 mt-1">14 gun trial</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Askiya Alindi</p>
        <p class="text-3xl font-semibold text-red-600 mt-1">{{ $suspendedTenants }}</p>
        <p class="text-xs text-gray-400 mt-1">Odeme bekleniyor</p>
    </div>
</div>

{{-- MRR / ARR --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">MRR (Aylik Tekrarlayan Gelir)</p>
        <p class="text-4xl font-semibold text-gray-900">{{ number_format($totalMrr, 0, ',', '.') }} TL</p>
        <p class="text-sm text-gray-400 mt-2">Aktif aboneliklerden</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">ARR (Yillik Tekrarlayan Gelir)</p>
        <p class="text-4xl font-semibold text-gray-900">{{ number_format($arr, 0, ',', '.') }} TL</p>
        <p class="text-sm text-gray-400 mt-2">MRR x 12</p>
    </div>
</div>

{{-- Paket Dagilimi + Son Kayitlar --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-4">Paket Dagilimi</h2>
        @if($packageDistribution->isEmpty())
            <p class="text-sm text-gray-400">Henuz abonelik yok.</p>
        @else
            <div class="space-y-3">
                @foreach($packageDistribution as $pkg)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">{{ $pkg->name }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $pkg->count }}</span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-900">Son Kayit Olan Firmalar</h2>
            <a href="{{ route('super-admin.tenants.index') }}" class="text-sm text-gray-500 hover:text-gray-900">
                Tumunu Gor
            </a>
        </div>
        @if($recentTenants->isEmpty())
            <p class="text-sm text-gray-400">Henuz kayit yok.</p>
        @else
            <div class="space-y-2">
                @foreach($recentTenants as $tenant)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $tenant->company_name }}</p>
                        <p class="text-xs text-gray-500">{{ $tenant->email }} &bull; {{ $tenant->business_type }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs px-2 py-1 rounded-full
                            {{ $tenant->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $tenant->status === 'trial' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $tenant->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}
                        ">
                            {{ match($tenant->status) {
                                'active' => 'Aktif',
                                'trial' => 'Deneme',
                                'suspended' => 'Askida',
                                'cancelled' => 'Iptal',
                                default => $tenant->status
                            } }}
                        </span>
                        <a href="{{ route('super-admin.tenants.show', $tenant->id) }}"
                           class="text-xs text-gray-500 hover:text-gray-900">Detay</a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
