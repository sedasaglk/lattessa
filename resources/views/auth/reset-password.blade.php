<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırla - Lattessa</title>
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
        <h1 class="text-2xl font-bold text-gray-900">Yeni Şifre Belirle</h1>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-8">
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm">
                @foreach($errors->all() as $error){{ $error }}@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.reset', ['tenant_slug' => $tenant_slug]) }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                       placeholder="En az 8 karakter">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Şifre Tekrar</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                       placeholder="Şifreyi tekrar girin">
            </div>
            <button type="submit"
                    class="w-full py-3 rounded-xl font-semibold text-white text-sm"
                    style="background:#111;">
                Şifremi Güncelle
            </button>
        </form>
    </div>
</div>
</body>
</html>
