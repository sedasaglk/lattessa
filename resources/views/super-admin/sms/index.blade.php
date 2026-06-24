@extends('layouts.super-admin')
@section('title', 'SMS Yonetimi')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">SMS Saglayici Yonetimi</h1>
</div>

{{-- Istatistikler --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500">Son 7 Gun Gonderilen</p>
        <p class="text-2xl font-semibold text-green-600 mt-1">{{ $stats['sent'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500">Bekleyen</p>
        <p class="text-2xl font-semibold text-amber-600 mt-1">{{ $stats['pending'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500">Basarisiz</p>
        <p class="text-2xl font-semibold text-red-600 mt-1">{{ $stats['failed'] ?? 0 }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Mevcut Saglayi­cilar --}}
    <div>
        <h2 class="font-semibold text-gray-900 mb-3">Sistem SMS Saglayicilari</h2>
        @if($providers->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <p class="text-gray-400">Henuz saglayici eklenmemis.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($providers as $provider)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ $provider->display_name }}</p>
                            <p class="text-xs text-gray-500">{{ $provider->provider }} &bull; Oncelik: {{ $provider->priority }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($provider->is_system_default)
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Varsayilan</span>
                            @else
                                <form method="POST" action="{{ route('super-admin.sms.default', $provider->id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs border border-gray-200 px-2 py-1 rounded hover:bg-gray-50">
                                        Varsayilan Yap
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('super-admin.sms.toggle', $provider->id) }}">
                                @csrf
                                <button type="submit"
                                        class="text-xs px-2 py-1 rounded {{ $provider->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $provider->is_active ? 'Aktif' : 'Pasif' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('super-admin.sms.destroy', $provider->id) }}"
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

        {{-- Yeni Saglayici --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mt-4">
            <h2 class="font-semibold text-gray-900 mb-4">Saglayici Ekle</h2>
            <form method="POST" action="{{ route('super-admin.sms.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Saglayici</label>
                    <select name="provider" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
                        <option value="vatansms">VatanSMS</option>
                        <option value="netgsm">Netgsm</option>
                        <option value="iletimerkezi">Ileti Merkezi</option>
                        <option value="mutlucell">Mutlucell</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Goruntu­lenecek Ad</label>
                    <input type="text" name="display_name" placeholder="VatanSMS (Ana)"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Kullanici Adi</label>
                    <input type="text" name="username"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Sifre/API Key</label>
                    <input type="password" name="password"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Gonderici Basligi</label>
                    <input type="text" name="sender" placeholder="LATTESSA"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Oncelik</label>
                    <input type="number" name="priority" value="1" min="1"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-gray-900">
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Saglayici Ekle
                </button>
            </form>
        </div>
    </div>

    {{-- Son SMS Loglari --}}
    <div>
        <h2 class="font-semibold text-gray-900 mb-3">Son SMS Loglari</h2>
        <div class="bg-white rounded-xl border border-gray-200">
            @if($recentLogs->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-gray-400">Henuz SMS log yok.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
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
                        <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($log->created_at)->format('d.m.Y H:i') }}</p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
