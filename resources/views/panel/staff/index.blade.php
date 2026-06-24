@extends('layouts.panel')
@section('title', 'Personel')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Personel</h1>
    <a href="{{ route('panel.staff.create', ['tenant_slug' => $tenant->slug]) }}"
       class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
        + Yeni Personel
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200">
    @if($staff->isEmpty())
        <div class="p-12 text-center">
            <p class="text-gray-400">Henuz personel eklenmemis.</p>
            <a href="{{ route('panel.staff.create', ['tenant_slug' => $tenant->slug]) }}"
               class="inline-block mt-4 text-sm text-gray-900 underline">Personel ekle</a>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($staff as $member)
            @php $stats = $monthlyStats[$member->id] ?? null; @endphp
            <div class="flex items-center justify-between p-4 hover:bg-gray-50">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center font-semibold text-gray-600">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $member->name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ match($member->role) {
                                'firma_sahibi' => 'Firma Sahibi',
                                'sube_muduru' => 'Sube Muduru',
                                'sekreter' => 'Sekreter',
                                'personel' => 'Personel',
                                'muhasebe' => 'Muhasebe',
                                default => $member->role
                            } }}
                            @if($member->phone) &bull; {{ $member->phone }} @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-medium text-gray-900">{{ $stats->total_appointments ?? 0 }} randevu</p>
                        <p class="text-xs text-gray-500">{{ number_format($stats->total_revenue ?? 0, 0, ',', '.') }} TL (bu ay)</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $member->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $member->status === 'active' ? 'Aktif' : 'Pasif' }}
                    </span>
                    <a href="{{ route('panel.staff.show', ['tenant_slug' => $tenant->slug, 'id' => $member->id]) }}"
                       class="text-sm text-gray-500 hover:text-gray-900">Detay</a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
