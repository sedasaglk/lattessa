@extends('layouts.panel')
@section('title', 'Satislar')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Satislar</h1>
    <a href="{{ route('panel.sales.create', ['tenant_slug' => $tenant->slug]) }}"
       class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
        + Yeni Satis
    </a>
</div>

{{-- Ozet --}}
<div class="grid grid-cols-2 gap-4 mb-4">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase">Bugunun Cirosu</p>
        <p class="text-2xl font-semibold text-green-600 mt-1">{{ number_format($todayTotal, 2, ',', '.') }} TL</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase">Bugunun Satisi</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $todayCount }} islem</p>
    </div>
</div>

{{-- Filtre --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
    <form method="GET" class="flex items-center gap-3">
        <input type="date" name="date" value="{{ $date }}"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
        <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            Filtrele
        </button>
        <a href="?date={{ today()->format('Y-m-d') }}" class="text-sm text-gray-500 hover:text-gray-900">Bugun</a>
    </form>
</div>

{{-- Satis Listesi --}}
<div class="bg-white rounded-xl border border-gray-200">
    @if($sales->isEmpty())
        <div class="p-12 text-center">
            <p class="text-gray-400">Bu tarihte satis bulunmuyor.</p>
            <a href="{{ route('panel.sales.create', ['tenant_slug' => $tenant->slug]) }}"
               class="inline-block mt-4 text-sm text-gray-900 underline">Yeni satis ekle</a>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($sales as $sale)
            <div class="flex items-center justify-between p-4 hover:bg-gray-50">
                <div>
                    <p class="font-medium text-gray-900">
                        {{ $sale->customer_name ?? 'Anonim Musteri' }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ \Carbon\Carbon::parse($sale->created_at)->format('H:i') }}
                        @if($sale->staff_name) &bull; {{ $sale->staff_name }} @endif
                        &bull;
                        {{ match($sale->payment_method) {
                            'cash' => 'Nakit',
                            'card' => 'Kart',
                            'transfer' => 'Havale',
                            'mixed' => 'Karma',
                            default => $sale->payment_method
                        } }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-semibold text-gray-900">
                        {{ number_format($sale->total_amount, 2, ',', '.') }} TL
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $sale->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $sale->status === 'completed' ? 'Tamamlandi' : 'Iade' }}
                    </span>
                    <a href="{{ route('panel.sales.show', ['tenant_slug' => $tenant->slug, 'id' => $sale->id]) }}"
                       class="text-sm text-gray-500 hover:text-gray-900">Detay</a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
