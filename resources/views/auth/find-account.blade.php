<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giris Yap - Lattessa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<nav class="border-b border-gray-100 px-6 py-4 bg-white">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
        <a href="/" class="text-xl font-semibold text-gray-900">Lattessa</a>
    </div>
</nav>

<div class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl border border-gray-200 p-8">
            <h1 class="text-2xl font-semibold text-gray-900 mb-2">Hesabiniza giris yapin</h1>
            <p class="text-gray-500 text-sm mb-8">E-posta adresinizi girin, sisteminizi bulalim.</p>

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="/giris">
                @csrf
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">E-posta adresi</label>
                    <input
                        type="email"
                        name="email"
                        required
                        autofocus
                        value="{{ old('email') }}"
                        placeholder="ornek@isletme.com"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                    >
                </div>

                <button type="submit" class="w-full bg-gray-900 text-white py-3 rounded-xl text-sm font-medium hover:bg-gray-800 transition">
                    Devam Et
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Hesabiniz yok mu?
                <a href="/kayit" class="text-gray-900 font-medium hover:underline">Ucretsiz baslatin</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
