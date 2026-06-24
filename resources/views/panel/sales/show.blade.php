@extends('layouts.panel')
@section('title', 'Satis #' . $sale->id)
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.sales.index', ['tenant_slug' => $tenant->slug]) }}"
           class="text-gray-400 hover:text-gray-900">← Geri</a>
        <h1 class="text-2xl font-semibold text-gray-900">Satis #{{ $sale->id }}</h1>
    </div>
    @if($sale->status === 'completed')
    <form method="POST" action="{{ route('panel.sales.refund', ['tenant_slug' => $tenant->slug, 'id' => $sale->id]) }}"
          onsubmit="return confirm('Bu satisi iade etmek istediginizden emin misiniz?')">
        @csrf
        <button type="submit" class="border border-red-200 text-red-600 text-sm px-4 py-2 rounded-lg hover:bg-red-50 transition">
            Iade Et
        </button>
    </form>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Satis Kalemleri</h2>
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100">
                <tr>
                    <th class="text-left py-2 text-xs text-gray-500">Urun/Hizmet</th>
                    <th class="text-right py-2 text-xs text-gray-500">Adet</th>
                    <th class="text-right py-2 text-xs text-gray-500">Birim Fiyat</th>
                    <th class="text-right py-2 text-xs text-gray-500">Indirim</th>
                    <th class="text-right py-2 text-xs text-gray-500">Toplam</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($items as $item)
                <tr>
                    <td class="py-2">
                        <p class="font-medium text-gray-900">{{ $item->item_name }}</p>
                        <p class="text-xs text-gray-400">{{ $item->item_type === 'product' ? 'Urun' : 'Hizmet' }}</p>
                    </td>
                    <td class="py-2 text-right text-gray-700">{{ $item->quantity }}</td>
                    <td class="py-2 text-right text-gray-700">{{ number_format($item->unit_price, 2, ',', '.') }} TL</td>
                    <td class="py-2 text-right text-gray-500">{{ number_format($item->discount, 2, ',', '.') }} TL</td>
                    <td class="py-2 text-right font-medium text-gray-900">{{ number_format($item->total_price, 2, ',', '.') }} TL</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t border-gray-200">
                <tr>
                    <td colspan="4" class="py-2 text-right text-sm text-gray-500">Ara Toplam</td>
                    <td class="py-2 text-right font-medium">{{ number_format($sale->subtotal, 2, ',', '.') }} TL</td>
                </tr>
                @if($sale->discount_amount > 0)
                <tr>
                    <td colspan="4" class="py-2 text-right text-sm text-gray-500">Indirim</td>
                    <td class="py-2 text-right text-red-600">-{{ number_format($sale->discount_amount, 2, ',', '.') }} TL</td>
                </tr>
                @endif
                <tr>
                    <td colspan="4" class="py-2 text-right font-semibold">Genel Toplam</td>
                    <td class="py-2 text-right font-bold text-green-600 text-lg">{{ number_format($sale->total_amount, 2, ',', '.') }} TL</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3 text-sm">
            <h2 class="font-semibold text-gray-900">Satis Bilgileri</h2>
            <div class="flex justify-between">
                <span class="text-gray-500">Tarih</span>
                <span class="font-medium">{{ \Carbon\Carbon::parse($sale->created_at)->format('d.m.Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Musteri</span>
                <span class="font-medium">{{ $sale->customer_name ?? 'Anonim' }}</span>
            </div>
            @if($sale->staff_name)
            <div class="flex justify-between">
                <span class="text-gray-500">Personel</span>
                <span class="font-medium">{{ $sale->staff_name }}</span>
            </div>
            @endif
            <div class="flex justify-between">
                <span class="text-gray-500">Odeme</span>
                <span class="font-medium">
                    {{ match($sale->payment_method) {
                        'cash' => 'Nakit',
                        'card' => 'Kart',
                        'transfer' => 'Havale',
                        'mixed' => 'Karma',
                        default => $sale->payment_method
                    } }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Durum</span>
                <span class="font-medium {{ $sale->status === 'completed' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $sale->status === 'completed' ? 'Tamamlandi' : 'Iade Edildi' }}
                </span>
            </div>
            @if($sale->notes)
            <div class="pt-2 border-t border-gray-100">
                <p class="text-gray-500">Not</p>
                <p class="text-gray-700 mt-1">{{ $sale->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
