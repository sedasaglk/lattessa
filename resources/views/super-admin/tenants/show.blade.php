@extends('layouts.super-admin')
@section('title', $tenant->company_name)
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('super-admin.tenants.index') }}" class="text-gray-400 hover:text-gray-900">← Geri</a>
    <h1 class="text-2xl font-semibold text-gray-900">{{ $tenant->company_name }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Firma Bilgileri --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Firma Bilgileri</h2>
        <div class="space-y-3">
            <div>
                <p class="text-xs text-gray-400">Firma Adi</p>
                <p class="text-sm font-medium text-gray-900">{{ $tenant->company_name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Sahip</p>
                <p class="text-sm font-medium text-gray-900">{{ $tenant->owner_name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">E-posta</p>
                <p class="text-sm font-medium text-gray-900">{{ $tenant->email }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Telefon</p>
                <p class="text-sm font-medium text-gray-900">{{ $tenant->phone }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Slug</p>
                <p class="text-sm font-medium text-gray-900">{{ $tenant->slug }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Is Turu</p>
                <p class="text-sm font-medium text-gray-900">{{ $tenant->business_type }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Kayit Tarihi</p>
                <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($tenant->created_at)->format('d.m.Y H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- Abonelik + Istatistikler --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Abonelik</h2>
            @if($subscription)
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Paket</span>
                        <span class="font-medium">{{ $subscription->package_name }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Durum</span>
                        <span class="font-medium">{{ $subscription->status }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Bitis</span>
                        <span class="font-medium">{{ \Carbon\Carbon::parse($subscription->ends_at)->format('d.m.Y') }}</span>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-400">Abonelik bulunamadi.</p>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Kullanim</h2>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Randevular</span>
                    <span class="font-medium">{{ $stats['total_appointments'] }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Musteriler</span>
                    <span class="font-medium">{{ $stats['total_customers'] }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Personel</span>
                    <span class="font-medium">{{ $stats['total_staff'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Durum + Paket Yonetimi --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Durum Yonet</h2>
            <form method="POST" action="{{ route('super-admin.tenants.status', $tenant->id) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    <option value="trial" {{ $tenant->status === 'trial' ? 'selected' : '' }}>Deneme</option>
                    <option value="active" {{ $tenant->status === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="suspended" {{ $tenant->status === 'suspended' ? 'selected' : '' }}>Askiya Al</option>
                    <option value="cancelled" {{ $tenant->status === 'cancelled' ? 'selected' : '' }}>Iptal Et</option>
                </select>
                <button type="submit" class="w-full bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Durumu Guncelle
                </button>
            </form>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="/{{ $tenant->slug }}/giris" target="_blank"
                   class="block w-full text-center border border-gray-200 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                    Firma Paneline Git
                </a>
            </div>
        </div>

        {{-- Paket Ata --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Paket Ata</h2>
            @if(session('success'))
                <div class="mb-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">{{ session('success') }}</div>
            @endif
            <form method="POST" action="{{ route('super-admin.tenants.assign-package', $tenant->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Paket</label>
                    <select name="package_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}" {{ $subscription && $subscription->package_id == $pkg->id ? 'selected' : '' }}>
                                {{ $pkg->name }} — {{ number_format($pkg->price_monthly, 0, ',', '.') }} TL/ay
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Abonelik Durumu</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="active">Aktif</option>
                        <option value="trial">Deneme</option>
                        <option value="past_due">Gecikmiş</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Başlangıç</label>
                        <input type="date" name="starts_at" value="{{ now()->format('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Bitiş</label>
                        <input type="date" name="ends_at" value="{{ now()->addYear()->format('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Paketi Ata
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
