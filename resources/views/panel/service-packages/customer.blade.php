@extends('layouts.panel')
@section('title', $customer->name . ' - Paketler')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.customers.show', ['tenant_slug' => $tenant->slug, 'id' => $customer->id]) }}"
       class="text-gray-400 hover:text-gray-900">← Musteri</a>
    <h1 class="text-2xl font-semibold text-gray-900">{{ $customer->name }} — Paketler</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Musteri Paketleri --}}
    <div class="lg:col-span-2 space-y-4">

        @if($customerPackages->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <p class="text-gray-400">Bu musteriye taninmis paket bulunmuyor.</p>
            </div>
        @else
            @foreach($customerPackages as $cp)
            @php
                $detail = $usageDetails[$cp->id];
                $isExpired = now()->isAfter($cp->expires_at);
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 p-5
                {{ $cp->status === 'completed' ? 'opacity-60' : '' }}
                {{ $isExpired && $cp->status === 'active' ? 'border-red-200' : '' }}">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $cp->package_name }}</p>
                        <p class="text-xs text-gray-500">
                            Bitis: {{ \Carbon\Carbon::parse($cp->expires_at)->format('d.m.Y') }}
                            @if($isExpired && $cp->status === 'active')
                                <span class="text-red-500">(Suresi Dolmus)</span>
                            @endif
                        </p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $cp->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $cp->status === 'completed' ? 'bg-gray-100 text-gray-600' : '' }}
                        {{ $cp->status === 'expired' ? 'bg-red-100 text-red-600' : '' }}">
                        {{ match($cp->status) { 'active' => 'Aktif', 'completed' => 'Tamamlandi', 'expired' => 'Suresi Doldu', default => $cp->status } }}
                    </span>
                </div>

                {{-- Hizmet Kullanim Durumu --}}
                <div class="space-y-3">
                    @foreach($detail['items'] as $item)
                    @php
                        $usedCount = isset($detail['usages'][$item->service_id])
                            ? $detail['usages'][$item->service_id]->sum('quantity')
                            : 0;
                        $remaining = $item->quantity - $usedCount;
                        $pct = min(100, ($usedCount / $item->quantity) * 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-700">{{ $item->service_name }}</span>
                            <span class="font-medium {{ $remaining <= 0 ? 'text-gray-400' : 'text-gray-900' }}">
                                {{ $usedCount }}/{{ $item->quantity }} kullanildi
                                ({{ $remaining }} kalan)
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full {{ $pct >= 100 ? 'bg-gray-400' : 'bg-green-500' }}"
                                 style="width: {{ $pct }}%"></div>
                        </div>

                        {{-- Seans Kullan --}}
                        @if($cp->status === 'active' && !$isExpired && $remaining > 0)
                        <form method="POST" action="{{ route('panel.packages.use', ['tenant_slug' => $tenant->slug, 'customer_package_id' => $cp->id]) }}"
                              class="mt-2 flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="service_id" value="{{ $item->service_id }}">
                            <input type="text" name="notes" placeholder="Not (opsiyonel)"
                                   class="flex-1 px-2 py-1 border border-gray-200 rounded text-xs focus:ring-1 focus:ring-gray-900 outline-none">
                            <button type="submit" class="bg-green-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-green-700 transition">
                                Seans Kullan
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        @endif

    </div>

    {{-- Paket Tani --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-4">Paket Tani</h2>
        <form method="POST" action="{{ route('panel.packages.sell', ['tenant_slug' => $tenant->slug, 'customer_id' => $customer->id]) }}"
              class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Paket Sec</label>
                <select name="package_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    <option value="">Paket secin</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}">{{ $pkg->name }} — {{ number_format($pkg->price, 0, ',', '.') }} TL</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Odeme Yontemi</label>
                <select name="payment_method" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    <option value="cash">Nakit</option>
                    <option value="card">Kart</option>
                    <option value="transfer">Havale</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Paketi Tani
            </button>
        </form>

        @if($packages->isEmpty())
            <p class="text-xs text-gray-400 mt-3 text-center">
                <a href="{{ route('panel.packages.index', ['tenant_slug' => $tenant->slug]) }}" class="underline">
                    Once paket olusturun
                </a>
            </p>
        @endif
    </div>

</div>
@endsection
