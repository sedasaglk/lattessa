@extends('layouts.panel')

@section('title', 'Randevu Detay')

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.appointments.index', ['tenant_slug' => $tenant->slug]) }}"
       class="text-gray-400 hover:text-gray-900">← Geri</a>
    <h1 class="text-2xl font-semibold text-gray-900">Randevu #{{ $appointment->id }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Musteri</p>
                <p class="font-medium text-gray-900 mt-1">{{ $appointment->customer->name }}</p>
                <p class="text-sm text-gray-500">{{ $appointment->customer->phone }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Hizmet</p>
                <p class="font-medium text-gray-900 mt-1">{{ $appointment->service->name }}</p>
                <p class="text-sm text-gray-500">{{ $appointment->service->duration_minutes }} dakika</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Personel</p>
                <p class="font-medium text-gray-900 mt-1">{{ $appointment->staff->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Tarih ve Saat</p>
                <p class="font-medium text-gray-900 mt-1">{{ $appointment->start_time->format('d.m.Y') }}</p>
                <p class="text-sm text-gray-500">{{ $appointment->start_time->format('H:i') }} - {{ $appointment->end_time->format('H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Ucret</p>
                <p class="font-medium text-gray-900 mt-1">{{ number_format($appointment->price, 2, ',', '.') }} TL</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Kaynak</p>
                <p class="font-medium text-gray-900 mt-1">{{ $appointment->source === 'panel' ? 'Panel' : 'Online' }}</p>
            </div>
        </div>
        @if($appointment->notes)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Notlar</p>
            <p class="text-sm text-gray-700 mt-1">{{ $appointment->notes }}</p>
        </div>
        @endif
    </div>

    <div class="space-y-4">
        {{-- Durum Guncelleme --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-700 mb-3">Durum Guncelle</p>
            <form method="POST" action="{{ route('panel.appointments.status', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <select name="status"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    <option value="pending" {{ $appointment->status === 'pending' ? 'selected' : '' }}>Bekliyor</option>
                    <option value="confirmed" {{ $appointment->status === 'confirmed' ? 'selected' : '' }}>Onaylandi</option>
                    <option value="completed" {{ $appointment->status === 'completed' ? 'selected' : '' }}>Tamamlandi</option>
                    <option value="cancelled" {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>Iptal</option>
                    <option value="no_show" {{ $appointment->status === 'no_show' ? 'selected' : '' }}>Gelmedi</option>
                </select>
                <button type="submit"
                        class="w-full bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Guncelle
                </button>
            </form>
        </div>

        {{-- Sil --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <form method="POST" action="{{ route('panel.appointments.destroy', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}"
                  onsubmit="return confirm('Bu randevuyu silmek istediginizden emin misiniz?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full bg-red-50 text-red-600 py-2 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                    Randevuyu Sil
                </button>
            </form>
        </div>
    </div>
</div>
{{-- Tekrarlayan Seri --}}
@if($appointment->is_recurring && $seriesAppointments->isNotEmpty())
<div class="mt-4 bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-900">↻ Tekrarlayan Seri ({{ $seriesAppointments->count() }} randevu)</h2>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('panel.appointments.cancel-series', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}">
                @csrf
                <input type="hidden" name="cancel_type" value="from_date">
                <button type="submit" onclick="return confirm('Bu tarihten itibaren tum seri iptal edilsin mi?')"
                        class="text-xs border border-amber-200 text-amber-600 px-3 py-1.5 rounded-lg hover:bg-amber-50">
                    Bu Tarihten Itibaren Iptal
                </button>
            </form>
            <form method="POST" action="{{ route('panel.appointments.cancel-series', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}">
                @csrf
                <input type="hidden" name="cancel_type" value="all">
                <button type="submit" onclick="return confirm('Tum seri iptal edilsin mi?')"
                        class="text-xs border border-red-200 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50">
                    Tum Seriyi Iptal
                </button>
            </form>
        </div>
    </div>
    <div class="space-y-2">
        @foreach($seriesAppointments as $s)
        <div class="flex items-center justify-between p-3 rounded-lg {{ $s->id === $appointment->id ? 'bg-gray-900 text-white' : 'bg-gray-50' }}">
            <div class="flex items-center gap-3">
                <span class="text-sm {{ $s->id === $appointment->id ? 'text-white' : 'text-gray-900' }}">
                    {{ \Carbon\Carbon::parse($s->start_time)->format('d.m.Y H:i') }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs px-2 py-0.5 rounded-full
                    {{ $s->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $s->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $s->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $s->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                    {{ $s->id === $appointment->id ? '!bg-white !text-gray-900' : '' }}">
                    {{ match($s->status) { 'pending' => 'Bekliyor', 'confirmed' => 'Onaylandi', 'completed' => 'Tamamlandi', 'cancelled' => 'Iptal', default => $s->status } }}
                </span>
                @if($s->id !== $appointment->id)
                <a href="{{ route('panel.appointments.show', ['tenant_slug' => $tenant->slug, 'id' => $s->id]) }}"
                   class="text-xs text-gray-500 hover:text-gray-900">Detay</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection