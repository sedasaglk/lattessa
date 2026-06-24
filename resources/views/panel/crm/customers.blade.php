@extends('layouts.panel')
@section('title', 'CRM Musteri Listesi')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.crm.index', ['tenant_slug' => $tenant->slug]) }}"
           class="text-gray-400 hover:text-gray-900">← CRM</a>
        <h1 class="text-2xl font-semibold text-gray-900">Musteri Listesi</h1>
        @if(request('segment'))
            <span class="text-sm text-gray-500">/ {{ $segments[request('segment')]['label'] ?? '' }}</span>
        @endif
    </div>
    <span class="text-sm text-gray-500">{{ $customers->total() }} musteri</span>
</div>

{{-- Filtreler --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
    <form method="GET" class="flex items-center gap-3 flex-wrap">
        <input type="text" name="search" value="{{ $search }}" placeholder="Ad, telefon veya email..."
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none flex-1 min-w-48">
        <select name="tag_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            <option value="">Tum Etiketler</option>
            @foreach($tags as $tag)
                <option value="{{ $tag->id }}" {{ $tagId == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
            @endforeach
        </select>
        <select name="segment" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            <option value="">Tum Segmentler</option>
            @foreach($segments as $key => $seg)
                <option value="{{ $key }}" {{ request('segment') === $key ? 'selected' : '' }}>{{ $seg['label'] }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            Filtrele
        </button>
        <a href="{{ route('panel.crm.customers', ['tenant_slug' => $tenant->slug]) }}"
           class="text-sm text-gray-500 hover:text-gray-900">Temizle</a>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200">
    @if($customers->isEmpty())
        <div class="p-12 text-center">
            <p class="text-gray-400">Bu kriterlere uyan musteri bulunamadi.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($customers as $customer)
            @php $cTags = $customerTags->get($customer->id, collect()); @endphp
            <div class="p-4 hover:bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center font-semibold text-gray-600 text-sm">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-gray-900">{{ $customer->name }}</p>
                                @if($customer->tier_name)
                                    <span class="text-xs px-1.5 py-0.5 rounded-full text-white"
                                          style="background-color: {{ $customer->tier_color }}">
                                        {{ $customer->tier_name }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500">
                                {{ $customer->phone }}
                                @if($customer->email) &bull; {{ $customer->email }} @endif
                            </p>
                            @if($cTags->isNotEmpty())
                                <div class="flex gap-1 mt-1 flex-wrap">
                                    @foreach($cTags as $tag)
                                        <span class="text-xs px-2 py-0.5 rounded-full text-white"
                                              style="background-color: {{ $tag->color }}">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-medium text-gray-900">{{ number_format($customer->total_spent, 0, ',', '.') }} TL</p>
                            <p class="text-xs text-gray-500">{{ $customer->visit_count }} ziyaret</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="openTagModal({{ $customer->id }}, {{ json_encode($cTags->pluck('tag_id')->values()->toArray()) }})"
                                    class="text-xs text-gray-500 hover:text-gray-900 border border-gray-200 px-2 py-1 rounded-lg">
                                Etiket
                            </button>
                            <a href="{{ route('panel.customers.show', ['tenant_slug' => $tenant->slug, 'id' => $customer->id]) }}"
                               class="text-xs text-gray-500 hover:text-gray-900 border border-gray-200 px-2 py-1 rounded-lg">
                                Detay
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $customers->appends(request()->query())->links() }}
        </div>
    @endif
</div>

{{-- Etiket Modal --}}
<div id="tagModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Etiket Guncelle</h3>
            <button onclick="closeTagModal()" class="text-gray-400 hover:text-gray-900">✕</button>
        </div>
        <form id="tagForm" method="POST" class="space-y-3">
            @csrf
            @if($tags->isEmpty())
                <p class="text-sm text-gray-400 text-center py-4">Once CRM sayfasından etiket olusturun.</p>
            @else
                <div class="space-y-2">
                    @foreach($tags as $tag)
                    <label class="flex items-center gap-2 cursor-pointer p-2 hover:bg-gray-50 rounded-lg">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                               class="tag-checkbox rounded border-gray-300"
                               data-tag-id="{{ $tag->id }}">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $tag->color }}"></div>
                        <span class="text-sm text-gray-700">{{ $tag->name }}</span>
                    </label>
                    @endforeach
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Kaydet
                </button>
            @endif
        </form>
    </div>
</div>

<script>
function openTagModal(customerId, currentTagIds) {
    const modal = document.getElementById('tagModal');
    const form = document.getElementById('tagForm');
    form.action = '/{{ $tenant->slug }}/crm/musteri/' + customerId + '/etiket';

    document.querySelectorAll('.tag-checkbox').forEach(cb => {
        cb.checked = currentTagIds.includes(parseInt(cb.dataset.tagId));
    });

    modal.classList.remove('hidden');
}

function closeTagModal() {
    document.getElementById('tagModal').classList.add('hidden');
}

document.getElementById('tagModal').addEventListener('click', function(e) {
    if (e.target === this) closeTagModal();
});
</script>
@endsection
