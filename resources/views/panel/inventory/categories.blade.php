@extends('layouts.panel')
@section('title', 'Urun Kategorileri')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.inventory.index', ['tenant_slug' => $tenant->slug]) }}"
       class="text-gray-400 hover:text-gray-900">← Stok</a>
    <h1 class="text-2xl font-semibold text-gray-900">Urun Kategorileri</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Kategori Listesi --}}
    <div class="bg-white rounded-xl border border-gray-200">
        @if($categories->isEmpty())
            <div class="p-12 text-center">
                <p class="text-gray-400">Henuz kategori eklenmemis.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($categories as $category)
                <div class="flex items-center justify-between p-4 hover:bg-gray-50">
                    <p class="font-medium text-gray-900">{{ $category->name }}</p>
                    <form method="POST" action="{{ route('panel.inventory.categories.destroy', ['tenant_slug' => $tenant->slug, 'id' => $category->id]) }}"
                          onsubmit="return confirm('Silmek istediginizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700">Sil</button>
                    </form>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Kategori Ekle --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Yeni Kategori</h2>
        <form method="POST" action="{{ route('panel.inventory.categories.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Adi</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                       placeholder="Ornek: Sac Boyasi, Bakim Urunleri">
            </div>
            <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Kategori Ekle
            </button>
        </form>
    </div>

</div>
@endsection
