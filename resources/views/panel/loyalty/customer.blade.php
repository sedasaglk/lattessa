@extends('layouts.panel')
@section('title', $customer->name . ' - Sadakat')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.loyalty.index', ['tenant_slug' => $tenant->slug]) }}"
       class="text-gray-400 hover:text-gray-900">← Sadakat</a>
    <h1 class="text-2xl font-semibold text-gray-900">{{ $customer->name }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Musteri Puan Bilgisi --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="text-center mb-6">
            @if($customer->tier_name)
                <span class="inline-block text-white text-xs font-medium px-3 py-1 rounded-full mb-3"
                      style="background-color: {{ $customer->tier_color }}">
                    {{ $customer->tier_name }}
                </span>
            @endif
            <p class="text-4xl font-bold text-gray-900">{{ number_format($customer->loyalty_points) }}</p>
            <p class="text-sm text-gray-500 mt-1">Mevcut Puan</p>
            @if($customer->discount_rate > 0)
                <p class="text-sm text-green-600 mt-2">%{{ $customer->discount_rate }} indirim hakki</p>
            @endif
        </div>

        {{-- Sonraki Seviye --}}
        @php
            $nextTier = $tiers->where('min_points', '>', $customer->loyalty_points)->first();
        @endphp
        @if($nextTier)
            @php
                $needed = $nextTier->min_points - $customer->loyalty_points;
                $pct = $customer->loyalty_points > 0
                    ? min(100, ($customer->loyalty_points / $nextTier->min_points) * 100)
                    : 0;
            @endphp
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 mb-2">{{ $nextTier->name }} seviyesine {{ number_format($needed) }} puan kaldi</p>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full bg-gray-900" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        @endif

        {{-- Puan Ekle --}}
        <form method="POST" action="{{ route('panel.loyalty.add', ['tenant_slug' => $tenant->slug, 'customer_id' => $customer->id]) }}"
              class="mt-4 space-y-2">
            @csrf
            <input type="number" name="points" min="1" placeholder="Puan miktari" required
                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            <input type="text" name="description" placeholder="Aciklama (opsiyonel)"
                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                Puan Ekle
            </button>
        </form>

        {{-- Puan Kullan --}}
        <form method="POST" action="{{ route('panel.loyalty.redeem', ['tenant_slug' => $tenant->slug, 'customer_id' => $customer->id]) }}"
              class="mt-2 space-y-2">
            @csrf
            <input type="number" name="points" min="1" max="{{ $customer->loyalty_points }}" placeholder="Kullanilacak puan" required
                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            <input type="text" name="description" placeholder="Aciklama (opsiyonel)"
                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            <button type="submit" class="w-full border border-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                Puan Kullan
            </button>
        </form>
    </div>

    {{-- Puan Gecmisi --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-4">Puan Gecmisi</h2>
        @if($history->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">Henuz puan hareketi yok.</p>
        @else
            <div class="space-y-2">
                @foreach($history as $tx)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $tx->description }}</p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($tx->created_at)->format('d.m.Y H:i') }}</p>
                    </div>
                    <span class="font-semibold {{ $tx->points > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points) }} puan
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
