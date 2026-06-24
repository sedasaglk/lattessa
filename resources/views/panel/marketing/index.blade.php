@extends('layouts.panel')
@section('title', 'Pazarlama')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Pazarlama</h1>
    <a href="{{ route('panel.marketing.campaign.create', ['tenant_slug' => $tenant->slug]) }}"
       class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
        + Yeni Kampanya
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Kampanyalar --}}
    <div>
        <h2 class="font-semibold text-gray-900 mb-3">Kampanyalar</h2>
        <div class="bg-white rounded-xl border border-gray-200">
            @if($campaigns->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-gray-400 text-sm">Henuz kampanya olusturulmamis.</p>
                    <a href="{{ route('panel.marketing.campaign.create', ['tenant_slug' => $tenant->slug]) }}"
                       class="inline-block mt-3 text-sm text-gray-900 underline">Ilk kampanyayi olustur</a>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($campaigns as $campaign)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between mb-1">
                            <p class="font-medium text-gray-900">{{ $campaign->name }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $campaign->status === 'sent' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $campaign->status === 'draft' ? 'bg-gray-100 text-gray-600' : '' }}
                                {{ $campaign->status === 'scheduled' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $campaign->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                            ">
                                {{ match($campaign->status) {
                                    'draft' => 'Taslak',
                                    'scheduled' => 'Zamanlanmis',
                                    'sent' => 'Gonderildi',
                                    'cancelled' => 'Iptal',
                                    default => $campaign->status
                                } }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">
                            {{ $campaign->type === 'email' ? 'Email' : 'SMS' }} &bull;
                            {{ $campaign->recipient_count }} alici
                            @if($campaign->sent_at)
                                &bull; {{ \Carbon\Carbon::parse($campaign->sent_at)->format('d.m.Y H:i') }}
                            @endif
                        </p>
                        <div class="flex gap-3 mt-2">
                            @if($campaign->status !== 'sent')
                            <form method="POST" action="{{ route('panel.marketing.campaign.send', ['tenant_slug' => $tenant->slug, 'id' => $campaign->id]) }}"
                                  onsubmit="return confirm('Kampanyayi simdi gondermek istediginizden emin misiniz?')">
                                @csrf
                                <button type="submit" class="text-xs text-green-600 hover:text-green-800 font-medium">Gonder</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('panel.marketing.campaign.destroy', ['tenant_slug' => $tenant->slug, 'id' => $campaign->id]) }}"
                                  onsubmit="return confirm('Silmek istediginizden emin misiniz?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Sil</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Kuponlar --}}
    <div>
        <h2 class="font-semibold text-gray-900 mb-3">Kuponlar</h2>

        {{-- Kupon Ekle --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
            <p class="text-sm font-medium text-gray-700 mb-3">Yeni Kupon Olustur</p>
            <form method="POST" action="{{ route('panel.marketing.coupon.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-3">
                @csrf
                <div>
                    <input type="text" name="name" placeholder="Kupon adi (ornek: Yaz Indirimi)" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Kupon Kodu (bos birakir otomatik)</label>
                        <input type="text" name="code" placeholder="YAZ2026"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none uppercase">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Indirim Turu</label>
                        <select name="discount_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                            <option value="percentage">Yuzde (%)</option>
                            <option value="fixed">Sabit (TL)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Indirim Degeri</label>
                        <input type="number" name="discount_value" min="0" step="0.01" required
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Min. Tutar (TL)</label>
                        <input type="number" name="min_amount" min="0" step="0.01" value="0"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Kullanim Limiti</label>
                        <input type="number" name="usage_limit" min="1" placeholder="Sinirsiz"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Gecerlilik Bitis</label>
                        <input type="date" name="valid_until"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Kupon Olustur
                </button>
            </form>
        </div>

        {{-- Kupon Listesi --}}
        <div class="bg-white rounded-xl border border-gray-200">
            @if($coupons->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-gray-400 text-sm">Henuz kupon olusturulmamis.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($coupons as $coupon)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between mb-1">
                            <div>
                                <p class="font-medium text-gray-900">{{ $coupon->name }}</p>
                                <p class="text-xs font-mono bg-gray-100 text-gray-700 px-2 py-0.5 rounded inline-block mt-0.5">
                                    {{ $coupon->code }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">
                                    @if($coupon->discount_type === 'percentage')
                                        %{{ number_format($coupon->discount_value, 0) }}
                                    @else
                                        {{ number_format($coupon->discount_value, 0) }} TL
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }} kullanim</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs {{ $coupon->status === 'active' ? 'text-green-600' : 'text-gray-400' }}">
                                {{ $coupon->status === 'active' ? 'Aktif' : 'Pasif' }}
                                @if($coupon->valid_until)
                                    &bull; {{ \Carbon\Carbon::parse($coupon->valid_until)->format('d.m.Y') }}'e kadar
                                @endif
                            </span>
                            <div class="flex gap-3">
                                <form method="POST" action="{{ route('panel.marketing.coupon.toggle', ['tenant_slug' => $tenant->slug, 'id' => $coupon->id]) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-gray-500 hover:text-gray-900">
                                        {{ $coupon->status === 'active' ? 'Pasifles' : 'Aktifles' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('panel.marketing.coupon.destroy', ['tenant_slug' => $tenant->slug, 'id' => $coupon->id]) }}"
                                      onsubmit="return confirm('Silmek istediginizden emin misiniz?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Sil</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
