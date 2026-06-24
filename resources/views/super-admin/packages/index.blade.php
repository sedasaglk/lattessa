@extends('layouts.super-admin')
@section('title', 'Paket Yonetimi')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Paket Yonetimi</h1>
</div>

{{-- Paket Listesi --}}
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-5 border-b border-gray-100">
        <h2 class="font-semibold text-gray-900">Mevcut Paketler</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-4 text-xs text-gray-500 font-medium">Paket</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Aylik</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Yillik</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Personel</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Sube</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">SMS</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Depo</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Kullanici</th>
                    <th class="text-center py-3 px-4 text-xs text-gray-500 font-medium">Durum</th>
                    <th class="py-3 px-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($packages as $pkg)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4">
                        <p class="font-medium text-gray-900">{{ $pkg->name }}</p>
                        <p class="text-xs text-gray-400">{{ $pkg->slug }}</p>
                    </td>
                    <td class="py-3 px-4 text-right">{{ number_format($pkg->price_monthly, 0, ',', '.') }} TL</td>
                    <td class="py-3 px-4 text-right">{{ number_format($pkg->price_yearly, 0, ',', '.') }} TL</td>
                    <td class="py-3 px-4 text-right">{{ $pkg->staff_limit ?? '∞' }}</td>
                    <td class="py-3 px-4 text-right">{{ $pkg->branch_limit ?? '∞' }}</td>
                    <td class="py-3 px-4 text-right">{{ number_format($pkg->sms_limit) }}</td>
                    <td class="py-3 px-4 text-right">{{ $pkg->storage_limit_mb }} MB</td>
                    <td class="py-3 px-4 text-right">
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">
                            {{ $stats[$pkg->id] ?? 0 }} firma
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $pkg->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $pkg->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <a href="{{ route('super-admin.packages.edit', $pkg->id) }}"
                           class="text-sm text-gray-500 hover:text-gray-900">Duzenle</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Yeni Paket --}}
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h2 class="font-semibold text-gray-900 mb-4">Yeni Paket Olustur</h2>
    <form method="POST" action="{{ route('super-admin.packages.store') }}" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Paket Adi</label>
            <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Slug</label>
            <input type="text" name="slug" required placeholder="baslangic" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Aylik Fiyat (TL)</label>
            <input type="number" name="price_monthly" required min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Yillik Fiyat (TL)</label>
            <input type="number" name="price_yearly" required min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Personel Limiti</label>
            <input type="number" name="staff_limit" placeholder="Bos=sinirsiz" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Sube Limiti</label>
            <input type="number" name="branch_limit" placeholder="Bos=sinirsiz" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">SMS Limiti</label>
            <input type="number" name="sms_limit" required value="100" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Depo (MB)</label>
            <input type="number" name="storage_limit_mb" required value="1024" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Sira</label>
            <input type="number" name="sort_order" required value="10" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Olustur
            </button>
        </div>
    </form>
</div>
@endsection
