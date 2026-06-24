@extends('layouts.panel')
@section('title', 'Stok')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Stok Yonetimi</h1>
    <div class="flex gap-2">
        <a href="{{ route('panel.inventory.categories', ['tenant_slug' => $tenant->slug]) }}"
           class="border border-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition">
            Kategoriler
        </a>
        <a href="{{ route('panel.inventory.suppliers', ['tenant_slug' => $tenant->slug]) }}"
           class="border border-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition">
            Tedarikciler
        </a>
        <a href="{{ route('panel.inventory.create', ['tenant_slug' => $tenant->slug]) }}"
           class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            + Yeni Urun
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
    <form method="GET" class="flex items-center gap-3 flex-wrap">
        <input type="text" name="search" value="{{ $search }}" placeholder="Urun adi, barkod veya SKU..."
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none flex-1 min-w-48">
        <select name="category_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            <option value="">Tum Kategoriler</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            Filtrele
        </button>
        <a href="{{ route('panel.inventory.index', ['tenant_slug' => $tenant->slug]) }}"
           class="text-sm text-gray-500 hover:text-gray-900">Temizle</a>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200">
    @if($products->isEmpty())
        <div class="p-12 text-center">
            <p class="text-gray-400">Henuz urun eklenmemis.</p>
            <a href="{{ route('panel.inventory.create', ['tenant_slug' => $tenant->slug]) }}"
               class="inline-block mt-4 text-sm text-gray-900 underline">Ilk urunu ekle</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs text-gray-500 font-medium">Urun</th>
                        <th class="text-left py-3 px-4 text-xs text-gray-500 font-medium">Barkod/SKU</th>
                        <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Alis</th>
                        <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Satis</th>
                        <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Stok</th>
                        <th class="text-center py-3 px-4 text-xs text-gray-500 font-medium">Durum</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($products as $product)
                    @php $stock = $stockLevels[$product->id] ?? 0; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4">
                            <p class="font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-xs text-gray-400">{{ $product->category_name ?? 'Kategorisiz' }}</p>
                        </td>
                        <td class="py-3 px-4 text-gray-500">{{ $product->barcode ?? $product->sku ?? '-' }}</td>
                        <td class="py-3 px-4 text-right text-gray-700">{{ number_format($product->purchase_price, 2, ',', '.') }} TL</td>
                        <td class="py-3 px-4 text-right font-medium text-gray-900">{{ number_format($product->sale_price, 2, ',', '.') }} TL</td>
                        <td class="py-3 px-4 text-right">
                            <span class="font-medium {{ $stock <= $product->min_stock_level ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $stock }} {{ $product->unit }}
                            </span>
                            @if($stock <= $product->min_stock_level)
                                <p class="text-xs text-red-500">Dusuk stok!</p>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $product->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $product->status === 'active' ? 'Aktif' : 'Pasif' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="{{ route('panel.inventory.show', ['tenant_slug' => $tenant->slug, 'id' => $product->id]) }}"
                               class="text-sm text-gray-500 hover:text-gray-900">Detay</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $products->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
