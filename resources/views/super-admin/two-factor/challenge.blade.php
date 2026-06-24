<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Doğrulama - Lattessa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Lattessa</h1>
        <p class="text-gray-500 text-sm mt-1">Super Admin — 2FA Doğrulama</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl">🔐</span>
            </div>
            <h2 class="font-semibold text-gray-900">İki Faktörlü Doğrulama</h2>
            <p class="text-sm text-gray-500 mt-1">Google Authenticator uygulamasındaki 6 haneli kodu girin.</p>
        </div>

        @error('code')
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                {{ $message }}
            </div>
        @enderror

        <form method="POST" action="{{ route('super-admin.2fa.verify') }}" class="space-y-4">
            @csrf
            <input type="text" name="code" maxlength="6" placeholder="000000"
                   autofocus autocomplete="off"
                   class="w-full px-4 py-4 border border-gray-200 rounded-xl text-center text-3xl tracking-widest font-mono focus:ring-2 focus:ring-gray-900 outline-none">
            <button type="submit"
                    class="w-full bg-gray-900 text-white py-3 rounded-xl text-sm font-medium hover:bg-gray-800 transition">
                Doğrula
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('super-admin.login') }}" class="text-xs text-gray-400 hover:text-gray-600">
                Geri dön
            </a>
        </div>
    </div>
</div>

</body>
</html>
