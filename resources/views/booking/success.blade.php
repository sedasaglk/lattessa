<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Onaylandi - {{ $tenant->company_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="max-w-md mx-auto px-4 py-8 text-center">
    <div class="bg-white rounded-2xl border border-gray-200 p-8">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-xl font-semibold text-gray-900 mb-2">Randevunuz Alindi!</h1>
        <p class="text-gray-500 text-sm mb-6">{{ $tenant->company_name }} sizi bekliyor.</p>

        <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 mb-6">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Ad Soyad</span>
                <span class="font-medium text-gray-900">{{ $booking['customer_name'] }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Hizmet</span>
                <span class="font-medium text-gray-900">{{ $booking['service_name'] }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Tarih</span>
                <span class="font-medium text-gray-900">{{ $booking['date'] }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Saat</span>
                <span class="font-medium text-gray-900">{{ $booking['time'] }}</span>
            </div>
        </div>

        <a href="{{ route('booking.show', ['tenant_slug' => $tenant->slug]) }}"
           class="block w-full bg-gray-900 text-white py-3 rounded-xl text-sm font-medium hover:bg-gray-800 transition">
            Yeni Randevu Al
        </a>
    </div>
</div>
</body>
</html>
