@extends('layouts.super-admin')
@section('title', 'Paket Duzenle')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('super-admin.packages.index') }}" class="text-gray-400 hover:text-gray-900">← Paketler</a>
    <h1 class="text-2xl font-semibold text-gray-900">{{ $package->name }} — Duzenle</h1>
</div>

<div class="max-w-2xl bg-white rounded-xl border border-gray-200 p-6">
    <form method="POST" action="{{ route('super-admin.packages.update', $package->id) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Paket Adi</label>
                <input type="text" name="name" value="{{ $package->name }}" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sira</label>
                <input type="number" name="sort_order" value="{{ $package->sort_order }}" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Aylik Fiyat (TL)</label>
                <input type="number" name="price_monthly" value="{{ $package->price_monthly }}" required step="0.01"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Yillik Fiyat (TL)</label>
                <input type="number" name="price_yearly" value="{{ $package->price_yearly }}" required step="0.01"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Personel Limiti (bos=sinirsiz)</label>
                <input type="number" name="staff_limit" value="{{ $package->staff_limit }}"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sube Limiti (bos=sinirsiz)</label>
                <input type="number" name="branch_limit" value="{{ $package->branch_limit }}"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SMS Limiti (aylik)</label>
                <input type="number" name="sms_limit" value="{{ $package->sms_limit }}" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Depolama Limiti (MB)</label>
                <input type="number" name="storage_limit_mb" value="{{ $package->storage_limit_mb }}" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
            </div>
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $package->is_active ? 'checked' : '' }}
                       class="rounded border-gray-300">
                <span class="text-sm font-medium text-gray-700">Paket Aktif</span>
            </label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Kaydet
            </button>
            <a href="{{ route('super-admin.packages.index') }}"
               class="px-6 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                Iptal
            </a>
        </div>
    </form>
</div>
@endsection
