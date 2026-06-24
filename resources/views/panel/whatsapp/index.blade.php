@extends('layouts.panel')
@section('title', 'WhatsApp Bağlantısı')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">WhatsApp Bağlantısı</h1>
    <p class="text-sm text-gray-500 mt-1">Kendi WhatsApp numaranızı bağlayın, randevu hatırlatmaları ve mesajlar bu numaradan gönderilsin.</p>
</div>

<div class="max-w-xl">

@if($connection && $connection->status === 'connected')
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
        <div class="flex items-center gap-3 p-4 bg-green-50 rounded-lg">
            <span class="text-2xl">✓</span>
            <div>
                <p class="font-medium text-green-800">Bağlı</p>
                <p class="text-sm text-green-600">{{ $connection->phone_number }} ({{ $connection->user_name }})</p>
                <p class="text-xs text-green-500">{{ $connection->platform }} &bull; {{ \Carbon\Carbon::parse($connection->connected_at)->diffForHumans() }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('panel.whatsapp.disconnect', ['tenant_slug' => $tenant->slug, 'id' => $connection->id]) }}" class="mt-3"
              onsubmit="return confirm('WhatsApp bağlantısı kesilsin mi?')">
            @csrf
            <button type="submit" class="text-sm border border-red-200 text-red-600 px-4 py-2 rounded-lg hover:bg-red-50 transition">
                Bağlantıyı Kes
            </button>
        </form>
    </div>

@elseif($connection && $connection->status === 'pending')
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
        <div class="p-4 bg-amber-50 rounded-lg">
            <p class="font-medium text-amber-800 mb-2">Bağlantı Bekleniyor</p>
            @if($connection->pairing_code)
                <p class="text-sm text-amber-700">Eşleştirme kodu: <span class="font-mono font-bold text-lg">{{ $connection->pairing_code }}</span></p>
                <p class="text-xs text-amber-600 mt-1">WhatsApp &gt; Ayarlar &gt; Bağlı Cihazlar &gt; Cihaz Bağla &gt; Telefon Numarasıyla Bağlan kısmından bu kodu girin.</p>
            @elseif($connection->qr_code)
                <p class="text-sm text-amber-700 mb-2">QR kodu WhatsApp uygulamanızdan okutun:</p>
                <div class="bg-white p-4 rounded-lg inline-block">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($connection->qr_code) }}"
                         alt="WhatsApp QR Kod" width="220" height="220">
                </div>
            @endif
            <p class="text-xs text-amber-500 mt-3">Durum otomatik kontrol ediliyor...</p>
        </div>
    </div>
    <script>
    setInterval(() => {
        fetch('{{ route('panel.whatsapp.check', ['tenant_slug' => $tenant->slug, 'id' => $connection->id]) }}')
            .then(r => r.json())
            .then(data => { if (data.connected) location.reload(); });
    }, 3000);
    </script>

@else
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4 text-center">
        <p class="text-gray-500 text-sm">WhatsApp henüz bağlanmamış.</p>
    </div>
@endif

@if(!$connection || $connection->status !== 'connected')
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-900 mb-4">Yeni Bağlantı Başlat</h2>

    <div class="mb-4 p-3 bg-blue-50 rounded-lg text-sm text-blue-700">
        <p class="font-medium mb-1">Nasıl çalışır?</p>
        <p>İşletmenizin WhatsApp numarasını bağlayın. Randevu hatırlatmaları ve müşteri mesajları bu numaradan otomatik gönderilir.</p>
    </div>

    <div class="space-y-4">
        <form method="POST" action="{{ route('panel.whatsapp.login.phone', ['tenant_slug' => $tenant->slug]) }}" class="space-y-2">
            @csrf
            <label class="block text-sm font-medium text-gray-700">Telefon ile Bağlan</label>
            <div class="flex gap-2">
                <input type="text" name="phone" placeholder="05XX XXX XX XX" required
                       class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Başlat
                </button>
            </div>
        </form>

        <div class="text-center text-xs text-gray-400">veya</div>

        <form method="POST" action="{{ route('panel.whatsapp.login.qr', ['tenant_slug' => $tenant->slug]) }}">
            @csrf
            <button type="submit" class="w-full border border-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                QR Kod ile Bağlan
            </button>
        </form>
    </div>
</div>
@endif

</div>
@endsection
