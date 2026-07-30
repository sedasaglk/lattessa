<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Değerlendirme - {{ $tenant->company_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl shadow-sm p-8 w-full max-w-md">
    <div class="text-center mb-6">
        <h1 class="text-xl font-semibold text-gray-900">{{ $tenant->company_name }}</h1>
        <p class="text-gray-500 text-sm mt-1">Deneyiminizi değerlendirin</p>
    </div>

    <div class="bg-gray-50 rounded-xl p-4 mb-6 text-sm text-gray-600">
        <p><span class="font-medium">Hizmet:</span> {{ $appointment->service_name }}</p>
        <p><span class="font-medium">Personel:</span> {{ $appointment->staff_name }}</p>
    </div>

    <form method="POST" action="{{ route('booking.review.store', [$tenant->slug, $token]) }}">
        @csrf
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-3">Puanınız</label>
            <div class="flex gap-2 justify-center" id="stars">
                @for($i = 1; $i <= 5; $i++)
                <label class="cursor-pointer">
                    <input type="radio" name="rating" value="{{ $i }}" class="hidden" required>
                    <span class="text-4xl text-gray-300 hover:text-yellow-400 transition star" data-val="{{ $i }}">★</span>
                </label>
                @endfor
            </div>
        </div>
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">Yorumunuz (opsiyonel)</label>
            <textarea name="comment" rows="3" placeholder="Deneyiminizi paylaşın..."
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
        </div>
        <button type="submit" class="w-full bg-gray-900 text-white py-3 rounded-lg font-medium text-sm hover:bg-gray-800">
            Değerlendirmeyi Gönder
        </button>
    </form>
</div>
<script>
const stars = document.querySelectorAll('.star');
stars.forEach(star => {
    star.addEventListener('click', function() {
        const val = parseInt(this.dataset.val);
        stars.forEach((s, i) => {
            s.style.color = i < val ? '#FBBF24' : '#D1D5DB';
        });
        this.previousElementSibling.checked = true;
    });
});
</script>
</body>
</html>
