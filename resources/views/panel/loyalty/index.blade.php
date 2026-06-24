@extends('layouts.panel')
@section('title', 'Sadakat Programi')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Sadakat Programi</h1>
</div>

{{-- Ozet --}}
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase">Toplam Verilen Puan</p>
        <p class="text-2xl font-semibold text-green-600 mt-1">{{ number_format($totalPointsIssued) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase">Toplam Kullanilan Puan</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($totalPointsRedeemed) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

    {{-- Seviyeler --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-4">Sadakat Seviyeleri</h2>

        @if($tiers->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Henuz seviye tanimlanmamis.</p>
        @else
            <div class="space-y-2 mb-4">
                @foreach($tiers as $tier)
                <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $tier->color }}"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $tier->name }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($tier->min_points) }} puan ve uzeri • %{{ $tier->discount_rate }} indirim</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('panel.loyalty.tiers.destroy', ['tenant_slug' => $tenant->slug, 'id' => $tier->id]) }}"
                          onsubmit="return confirm('Silmek istediginizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Sil</button>
                    </form>
                </div>
                @endforeach
            </div>
        @endif

        {{-- Seviye Ekle --}}
        <form method="POST" action="{{ route('panel.loyalty.tiers.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-3 border-t border-gray-100 pt-4">
            @csrf
            <p class="text-sm font-medium text-gray-700">Yeni Seviye Ekle</p>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Seviye Adi</label>
                    <input type="text" name="name" placeholder="Bronze, Silver..." required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Min. Puan</label>
                    <input type="number" name="min_points" min="0" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Indirim Orani (%)</label>
                    <input type="number" name="discount_rate" min="0" max="100" step="0.5" value="0" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Renk</label>
                    <select name="color" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="#cd7f32">Bronze</option>
                        <option value="#c0c0c0">Silver</option>
                        <option value="#ffd700">Gold</option>
                        <option value="#b9f2ff">Platinum</option>
                        <option value="#6b7280">Gri</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="w-full bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Seviye Ekle
            </button>
        </form>
    </div>

    {{-- En Cok Puanli Musteriler --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-4">En Cok Puanli Musteriler</h2>
        @if($topCustomers->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Henuz puan kazanan musteri yok.</p>
        @else
            <div class="space-y-2">
                @foreach($topCustomers as $i => $customer)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-gray-400 w-5">{{ $i + 1 }}</span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $customer->name }}</p>
                            @if($customer->tier_name)
                                <span class="text-xs px-1.5 py-0.5 rounded-full text-white"
                                      style="background-color: {{ $customer->tier_color }}">
                                    {{ $customer->tier_name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">{{ number_format($customer->loyalty_points) }} puan</p>
                        <a href="{{ route('panel.loyalty.customer', ['tenant_slug' => $tenant->slug, 'customer_id' => $customer->id]) }}"
                           class="text-xs text-gray-500 hover:text-gray-900">Detay</a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
