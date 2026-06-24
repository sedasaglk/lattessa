@extends('layouts.panel')
@section('title', 'Tedarikciler')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.inventory.index', ['tenant_slug' => $tenant->slug]) }}"
           class="text-gray-400 hover:text-gray-900">← Stok</a>
        <h1 class="text-2xl font-semibold text-gray-900">Tedarikciler</h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Tedarikci Listesi --}}
    <div class="bg-white rounded-xl border border-gray-200">
        @if($suppliers->isEmpty())
            <div class="p-12 text-center">
                <p class="text-gray-400">Henuz tedarikci eklenmemis.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($suppliers as $supplier)
                <div class="flex items-center justify-between p-4 hover:bg-gray-50">
                    <div>
                        <p class="font-medium text-gray-900">{{ $supplier->name }}</p>
                        <p class="text-sm text-gray-500">
                            @if($supplier->phone) {{ $supplier->phone }} @endif
                            @if($supplier->email) &bull; {{ $supplier->email }} @endif
                        </p>
                    </div>
                    <form method="POST" action="{{ route('panel.inventory.suppliers.destroy', ['tenant_slug' => $tenant->slug, 'id' => $supplier->id]) }}"
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

    {{-- Tedarikci Ekle --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Yeni Tedarikci</h2>
        <form method="POST" action="{{ route('panel.inventory.suppliers.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tedarikci Adi</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                <input type="text" name="phone"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
                <input type="email" name="email"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
                <textarea name="address" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"></textarea>
            </div>
            <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Tedarikci Ekle
            </button>
        </form>
    </div>

</div>
@endsection
