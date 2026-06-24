@extends('layouts.super-admin')
@section('title', 'WhatsApp Yonetimi')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">WhatsApp Entegrasyonu</h1>
    <p class="text-sm text-gray-500 mt-1">Randevu hatirlatma ve bilgilendirme mesajlari icin WhatsApp baglantisi.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Baglanti Durumu --}}
    <div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
            <h2 class="font-semibold text-gray-900 mb-4">Baglanti Durumu</h2>

            @if($connection && $connection->status === 'connected')
                <div class="flex items-center gap-3 p-4 bg-green-50 rounded-lg">
                    <span class="text-2xl">✓</span>
                    <div>
                        <p class="font-medium text-green-800">Bagli</p>
                        <p class="text-sm text-green-600">{{ $connection->phone_number }} ({{ $connection->user_name }})</p>
                        <p class="text-xs text-green-500">{{ $connection->platform }} &bull; {{ \Carbon\Carbon::parse($connection->connected_at)->diffForHumans() }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('super-admin.whatsapp.disconnect', $connection->id) }}" class="mt-3"
                      onsubmit="return confirm('WhatsApp baglantisi kesilsin mi?')">
                    @csrf
                    <button type="submit" class="text-sm border border-red-200 text-red-600 px-4 py-2 rounded-lg hover:bg-red-50 transition">
                        Baglantiyi Kes
                    </button>
                </form>

            @elseif($connection && $connection->status === 'pending')
                <div class="p-4 bg-amber-50 rounded-lg" id="pendingBox" data-connection-id="{{ $connection->id }}">
                    <p class="font-medium text-amber-800 mb-2">Baglanti Bekleniyor</p>
                    @if($connection->pairing_code)
                        <p class="text-sm text-amber-700">Eslestirme kodu: <span class="font-mono font-bold text-lg">{{ $connection->pairing_code }}</span></p>
                        <p class="text-xs text-amber-600 mt-1">WhatsApp &gt; Baglanti Cihazlar &gt; Telefon Numarasiyla Baglan kismindan bu kodu girin.</p>
                    @elseif($connection->qr_code)
                        <p class="text-sm text-amber-700 mb-2">QR kodu WhatsApp uygulamanizdan okutun:</p>
                        <div class="bg-white p-3 rounded-lg inline-block">
                            <p class="text-xs font-mono break-all text-gray-400">{{ Str::limit($connection->qr_code, 60) }}</p>
                        </div>
                    @endif
                    <p class="text-xs text-amber-500 mt-3" id="statusCheck">Durum kontrol ediliyor...</p>
                </div>
                <script>
                setInterval(() => {
                    fetch('{{ route('super-admin.whatsapp.check', $connection->id) }}')
                        .then(r => r.json())
                        .then(data => {
                            if (data.connected) location.reload();
                        });
                }, 3000);
                </script>

            @else
                <div class="p-4 bg-gray-50 rounded-lg text-center">
                    <p class="text-gray-500 text-sm">WhatsApp henuz baglanmamis.</p>
                </div>
            @endif
        </div>

        {{-- Yeni Baglanti --}}
        @if(!$connection || $connection->status !== 'connected')
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Yeni Baglanti Baslat</h2>

            <div class="space-y-4">
                <form method="POST" action="{{ route('super-admin.whatsapp.login.phone') }}" class="space-y-2">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700">Telefon ile Baglan</label>
                    <div class="flex gap-2">
                        <input type="text" name="phone" placeholder="905XXXXXXXXX" required
                               class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
                        <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                            Baslat
                        </button>
                    </div>
                </form>

                <div class="text-center text-xs text-gray-400">veya</div>

                <form method="POST" action="{{ route('super-admin.whatsapp.login.qr') }}">
                    @csrf
                    <button type="submit" class="w-full border border-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        QR Kod ile Baglan
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Son Mesajlar --}}
    <div>
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Gonderilen</p>
                <p class="text-xl font-semibold text-green-600">{{ $stats['sent'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Bekleyen</p>
                <p class="text-xl font-semibold text-amber-600">{{ $stats['pending'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Basarisiz</p>
                <p class="text-xl font-semibold text-red-600">{{ $stats['failed'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200">
            <div class="p-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Son Mesajlar</h2>
            </div>
            @if($recentLogs->isEmpty())
                <div class="p-8 text-center"><p class="text-gray-400 text-sm">Henuz mesaj yok.</p></div>
            @else
                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    @foreach($recentLogs as $log)
                    <div class="p-3">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-900">{{ $log->company_name }}</span>
                            <span class="text-xs px-1.5 py-0.5 rounded
                                {{ $log->status === 'sent' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $log->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $log->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                                {{ $log->status }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">{{ $log->phone }} &bull; {{ $log->type }}</p>
                        <p class="text-xs text-gray-600 truncate">{{ $log->message }}</p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
