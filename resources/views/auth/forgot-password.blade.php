<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Unuttum - Lattessa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl mb-4" style="background:#6366F1;">
            <span class="text-white font-bold text-xl">L</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Şifremi Unuttum</h1>
        <p class="text-gray-500 text-sm mt-1">E-posta adresinize sıfırlama linki göndereceğiz</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-8">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 mb-6 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm">
                @foreach($errors->all() as $error){{ $error }}@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.forgot.send', ['tenant_slug' => $tenant_slug]) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                       placeholder="ornek@mail.com">
            </div>
            <button type="submit"
                    class="w-full py-3 rounded-xl font-semibold text-white text-sm"
                    style="background:#111;">
                Sıfırlama Linki Gönder
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login.form', ['tenant_slug' => $tenant_slug]) }}"
               class="text-sm text-indigo-600 hover:underline">← Giriş sayfasına dön</a>
        </div>
    </div>
</div>
</body>
</html>
