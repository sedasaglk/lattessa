@extends('layouts.panel')
@section('title', $customer->name)
@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.customers.index', ['tenant_slug' => $tenant->slug]) }}"
           class="text-gray-400 hover:text-gray-900">← Geri</a>
        <h1 class="text-2xl font-semibold text-gray-900">{{ $customer->name }}</h1>
    </div>
    <div class="flex gap-2">
        @if(in_array($tenant->business_type, ['klinik', 'diyetisyen', 'psikolog', 'estetik']))
        <a href="{{ route('panel.client-files.show', ['tenant_slug' => $tenant->slug, 'customer_id' => $customer->id]) }}"
           class="border border-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition">
            Danisan Dosyasi
        </a>
        @endif
        <a href="{{ route('panel.packages.customer', ['tenant_slug' => $tenant->slug, 'customer_id' => $customer->id]) }}"
           class="border border-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition">
            Paketler
        </a>
        <a href="{{ route('panel.loyalty.customer', ['tenant_slug' => $tenant->slug, 'customer_id' => $customer->id]) }}"
           class="border border-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition">
            Sadakat
        </a>
        <a href="{{ route('panel.customers.edit', ['tenant_slug' => $tenant->slug, 'id' => $customer->id]) }}"
           class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            Duzenle
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Musteri Bilgileri</h2>
        <div class="space-y-3">
            <div>
                <p class="text-xs text-gray-400 uppercase">Telefon</p>
                <p class="text-sm font-medium text-gray-900">{{ $customer->phone }}</p>
            </div>
            @if($customer->email)
            <div>
                <p class="text-xs text-gray-400 uppercase">E-posta</p>
                <p class="text-sm font-medium text-gray-900">{{ $customer->email }}</p>
            </div>
            @endif
            @if($customer->birth_date)
            <div>
                <p class="text-xs text-gray-400 uppercase">Dogum Tarihi</p>
                <p class="text-sm font-medium text-gray-900">{{ $customer->birth_date->format('d.m.Y') }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs text-gray-400 uppercase">Toplam Harcama</p>
                <p class="text-sm font-medium text-gray-900">{{ number_format($customer->total_spent, 2, ',', '.') }} TL</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase">Ziyaret Sayisi</p>
                <p class="text-sm font-medium text-gray-900">{{ $customer->visit_count }}</p>
            </div>
            @if($customer->notes)
            <div>
                <p class="text-xs text-gray-400 uppercase">Notlar</p>
                <p class="text-sm text-gray-700">{{ $customer->notes }}</p>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-3">Mesaj Gonder</h2>
            <form method="POST" action="{{ route('panel.notifications.send', ['tenant_slug' => $tenant->slug, 'customer_id' => $customer->id]) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Kanal</label>
                    <select name="channel" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="auto">Otomatik (WhatsApp oncelikli)</option>
                        <option value="whatsapp">Sadece WhatsApp</option>
                        <option value="sms">Sadece SMS</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Mesaj</label>
                    <textarea name="message" rows="3" required maxlength="500"
                              placeholder="Musteriye gonderilecek mesaj..."
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Gonder
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Randevu Gecmisi</h2>
        @if($appointments->isEmpty())
            <p class="text-gray-400 text-sm">Henuz randevu bulunmuyor.</p>
        @else
            <div class="space-y-2">
                @foreach($appointments as $appointment)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $appointment->service->name }}</p>
                        <p class="text-xs text-gray-500">{{ $appointment->staff->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-900">{{ $appointment->start_time->format('d.m.Y H:i') }}</p>
                        <p class="text-xs text-gray-500">{{ number_format($appointment->price, 0, ',', '.') }} TL</p>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
