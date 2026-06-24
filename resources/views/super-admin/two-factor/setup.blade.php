@extends('layouts.super-admin')
@section('title', '2FA Kurulumu')
@section('content')

<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">İki Faktörlü Doğrulama (2FA)</h1>
        <p class="text-sm text-gray-500 mt-1">Super admin hesabınızı ekstra güvenlik katmanıyla koruyun.</p>
    </div>

    {{-- Mevcut Durum --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-900">2FA Durumu</p>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if($user->two_factor_enabled)
                        Google Authenticator ile aktif
                    @else
                        Aktif değil
                    @endif
                </p>
            </div>
            <span class="text-sm px-3 py-1 rounded-full {{ $user->two_factor_enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $user->two_factor_enabled ? '✓ Aktif' : 'Pasif' }}
            </span>
        </div>
    </div>

    @if(!$user->two_factor_enabled)
    {{-- 2FA Kurulum --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
        <h2 class="font-semibold text-gray-900 mb-4">2FA Kurulumu</h2>

        <div class="space-y-4">
            <div class="p-4 bg-blue-50 rounded-lg text-sm text-blue-700">
                <p class="font-medium mb-1">Nasıl kurulur?</p>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Google Authenticator veya Authy uygulamasını telefonunuza indirin</li>
                    <li>Uygulamada "+" butonuna basın ve QR kodu tarayın</li>
                    <li>Uygulama size 6 haneli bir kod verecek, aşağıya girin</li>
                </ol>
            </div>

            @if($qrCode)
            <div class="flex justify-center p-4 bg-white border border-gray-200 rounded-lg">
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Kod" class="w-48 h-48">
            </div>
            @else
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs font-medium text-gray-700 mb-1">Manuel giriş için secret key:</p>
                <code class="text-sm bg-white border border-gray-200 px-3 py-2 rounded block break-all">{{ $user->two_factor_secret }}</code>
            </div>
            @endif

            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-xs font-medium text-gray-700 mb-1">Manuel giriş kodu:</p>
                <code class="text-xs text-gray-600 break-all">{{ $user->two_factor_secret }}</code>
            </div>

            <form method="POST" action="{{ route('super-admin.2fa.enable') }}" class="space-y-3">
                @csrf
                @error('code')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Uygulamadan 6 haneli kodu girin
                    </label>
                    <input type="text" name="code" maxlength="6" placeholder="000000"
                           autofocus autocomplete="off"
                           class="w-full px-4 py-3 border border-gray-200 rounded-lg text-center text-2xl tracking-widest font-mono focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    2FA'yı Etkinleştir
                </button>
            </form>
        </div>
    </div>

    @else
    {{-- 2FA Devre Dışı Bırak --}}
    <div class="bg-white rounded-xl border border-red-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-4">2FA Devre Dışı Bırak</h2>
        <p class="text-sm text-gray-500 mb-4">Devre dışı bırakmak için uygulamadan mevcut kodu girin.</p>
        <form method="POST" action="{{ route('super-admin.2fa.disable') }}" class="space-y-3">
            @csrf
            @error('code')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            <input type="text" name="code" maxlength="6" placeholder="000000"
                   autofocus autocomplete="off"
                   class="w-full px-4 py-3 border border-red-200 rounded-lg text-center text-2xl tracking-widest font-mono focus:ring-2 focus:ring-red-500 outline-none">
            <button type="submit"
                    onclick="return confirm('2FA devre dışı bırakılsın mı? Bu güvenliğinizi azaltır.')"
                    class="w-full bg-red-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-red-700 transition">
                2FA Devre Dışı Bırak
            </button>
        </form>
    </div>
    @endif

</div>
@endsection
