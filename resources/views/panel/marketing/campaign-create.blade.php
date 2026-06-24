@extends('layouts.panel')
@section('title', 'Yeni Kampanya')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.marketing.index', ['tenant_slug' => $tenant->slug]) }}"
       class="text-gray-400 hover:text-gray-900">← Geri</a>
    <h1 class="text-2xl font-semibold text-gray-900">Yeni Kampanya</h1>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('panel.marketing.campaign.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kampanya Adi</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                   placeholder="Yaz Sezonu Kampanyasi">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kanal</label>
            <select name="type" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                <option value="email">Email</option>
                <option value="sms">SMS (deploy sonrasi aktif)</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hedef Kitle</label>
            <select name="segment_type" id="segmentType" onchange="updateSegmentValue()"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                <option value="">Tum Musteriler ({{ $totalCustomers }} kisi)</option>
                <option value="last_visit_days">Son X gundur gelmeyen</option>
                <option value="min_spent">Minimum harcama yapan</option>
                <option value="birthday_this_month">Bu ay dogum gunu olan</option>
                @foreach($loyaltyTiers as $tier)
                    <option value="loyalty_tier" data-value="{{ $tier->id }}">{{ $tier->name }} seviyesi</option>
                @endforeach
            </select>
            <div id="segmentValueDiv" class="mt-2 hidden">
                <input type="number" name="segment_value" id="segmentValue" min="0"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                       placeholder="Deger girin">
            </div>
            <input type="hidden" name="segment_value" id="segmentValueHidden">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mesaj Icerigi</label>
            <textarea name="content" rows="6" required
                      class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                      placeholder="Kampanya mesajinizi yazin...">{{ old('content') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Zamanlama (opsiyonel)</label>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                   min="{{ now()->format('Y-m-d\TH:i') }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            <p class="text-xs text-gray-400 mt-1">Bos birakir hemen taslak olarak kaydedilir, sonra manuel gonderebilirsiniz.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Kampanya Olustur
            </button>
            <a href="{{ route('panel.marketing.index', ['tenant_slug' => $tenant->slug]) }}"
               class="px-6 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                Iptal
            </a>
        </div>
    </form>
</div>

<script>
function updateSegmentValue() {
    const select = document.getElementById('segmentType');
    const div = document.getElementById('segmentValueDiv');
    const input = document.getElementById('segmentValue');
    const hidden = document.getElementById('segmentValueHidden');
    const selected = select.options[select.selectedIndex];

    if (select.value === 'last_visit_days') {
        div.classList.remove('hidden');
        input.placeholder = 'Kac gundur gelmeyen (ornek: 30)';
        hidden.name = '';
        input.name = 'segment_value';
    } else if (select.value === 'min_spent') {
        div.classList.remove('hidden');
        input.placeholder = 'Minimum harcama tutari (TL)';
        hidden.name = '';
        input.name = 'segment_value';
    } else if (select.value === 'loyalty_tier') {
        div.classList.add('hidden');
        hidden.name = 'segment_value';
        hidden.value = selected.dataset.value || '';
        input.name = '';
    } else {
        div.classList.add('hidden');
        input.name = '';
        hidden.name = 'segment_value';
        hidden.value = '';
    }
}
</script>
@endsection
