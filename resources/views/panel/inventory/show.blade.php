@extends('layouts.panel')
@section('title', $product->name)
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.inventory.index', ['tenant_slug' => $tenant->slug]) }}"
           class="text-gray-400 hover:text-gray-900">← Geri</a>
        <h1 class="text-2xl font-semibold text-gray-900">{{ $product->name }}</h1>
    </div>
    <a href="{{ route('panel.inventory.edit', ['tenant_slug' => $tenant->slug, 'id' => $product->id]) }}"
       class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
        Duzenle
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Urun Bilgileri --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Urun Bilgileri</h2>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Kategori</span>
                <span class="font-medium text-gray-900">{{ $product->category_name ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Barkod</span>
                <span class="font-medium text-gray-900">{{ $product->barcode ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">SKU</span>
                <span class="font-medium text-gray-900">{{ $product->sku ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Tedarikci</span>
                <span class="font-medium text-gray-900">{{ $product->supplier_name ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Alis Fiyati</span>
                <span class="font-medium text-gray-900">{{ number_format($product->purchase_price, 2, ',', '.') }} TL</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Satis Fiyati</span>
                <span class="font-medium text-gray-900">{{ number_format($product->sale_price, 2, ',', '.') }} TL</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Birim</span>
                <span class="font-medium text-gray-900">{{ $product->unit }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Min. Stok</span>
                <span class="font-medium text-gray-900">{{ $product->min_stock_level }}</span>
            </div>
            <div class="flex justify-between border-t border-gray-100 pt-3">
                <span class="text-gray-500">Mevcut Stok</span>
                <span class="text-xl font-semibold {{ $currentStock <= $product->min_stock_level ? 'text-red-600' : 'text-green-600' }}">
                    {{ $currentStock }} {{ $product->unit }}
                </span>
            </div>
        </div>
    </div>

    {{-- Stok Hareketi Ekle --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Stok Hareketi Ekle</h2>
        <form method="POST" action="{{ route('panel.inventory.stock', ['tenant_slug' => $tenant->slug, 'id' => $product->id]) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Islem Turu</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    <option value="in">Stok Girisi</option>
                    <option value="out">Stok Cikisi</option>
                    <option value="adjustment">Duzeltme</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Miktar</label>
                <input type="number" name="quantity" step="0.01" min="0.01" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Birim Fiyat (opsiyonel)</label>
                <input type="number" name="unit_price" step="0.01" min="0" value="{{ $product->purchase_price }}"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Not</label>
                <input type="text" name="notes"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            </div>
            <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Kaydet
            </button>
        </form>
    </div>

    {{-- Hareket Gecmisi --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Hareket Gecmisi</h2>
        @if($movements->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Henuz hareket yok.</p>
        @else
            <div class="space-y-2">
                @foreach($movements as $movement)
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-xs font-medium {{ $movement->type === 'in' ? 'text-green-600' : ($movement->type === 'out' ? 'text-red-600' : 'text-blue-600') }}">
                            {{ match($movement->type) { 'in' => 'Giris', 'out' => 'Cikis', 'adjustment' => 'Duzeltme', default => $movement->type } }}
                        </p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($movement->created_at)->format('d.m.Y H:i') }}</p>
                        @if($movement->notes)<p class="text-xs text-gray-500">{{ $movement->notes }}</p>@endif
                    </div>
                    <span class="font-semibold text-sm {{ $movement->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $movement->type === 'in' ? '+' : '-' }}{{ $movement->quantity }} {{ $product->unit }}
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
