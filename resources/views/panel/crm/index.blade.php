@extends('layouts.panel')
@section('title', 'CRM')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">CRM & Segmentasyon</h1>
    <a href="{{ route('panel.crm.customers', ['tenant_slug' => $tenant->slug]) }}"
       class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
        Musteri Listesi
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Segmentler --}}
    <div>
        <h2 class="font-semibold text-gray-900 mb-3">Musteri Segmentleri</h2>
        <div class="space-y-3">
            @foreach($segments as $key => $segment)
            <a href="{{ route('panel.crm.customers', ['tenant_slug' => $tenant->slug, 'segment' => $key]) }}"
               class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200 hover:border-gray-300 hover:shadow-sm transition">
                <div>
                    <p class="font-medium text-gray-900">{{ $segment['label'] }}</p>
                    <p class="text-xs text-gray-500">{{ $segment['description'] }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-semibold text-gray-900">{{ $segment['count'] }}</span>
                    <span class="text-xs text-gray-400">musteri →</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Etiketler --}}
    <div>
        <h2 class="font-semibold text-gray-900 mb-3">Musteri Etiketleri</h2>

        {{-- Etiket Listesi --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
            @if($tags->isEmpty())
                <p class="text-sm text-gray-400 text-center py-4">Henuz etiket olusturulmamis.</p>
            @else
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($tags as $tag)
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200">
                        <div class="w-2 h-2 rounded-full" style="background-color: {{ $tag->color }}"></div>
                        <span class="text-sm text-gray-700">{{ $tag->name }}</span>
                        <span class="text-xs text-gray-400">({{ $tagCounts[$tag->id] ?? 0 }})</span>
                        <form method="POST" action="{{ route('panel.crm.tags.destroy', ['tenant_slug' => $tenant->slug, 'id' => $tag->id]) }}"
                              onsubmit="return confirm('Silmek istediginizden emin misiniz?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-300 hover:text-red-500 ml-1 text-xs">✕</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @endif

            {{-- Etiket Ekle --}}
            <form method="POST" action="{{ route('panel.crm.tags.store', ['tenant_slug' => $tenant->slug]) }}"
                  class="flex items-center gap-2">
                @csrf
                <input type="text" name="name" placeholder="Etiket adi..." required
                       class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                <select name="color" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    <option value="#ef4444">Kirmizi</option>
                    <option value="#f97316">Turuncu</option>
                    <option value="#eab308">Sari</option>
                    <option value="#22c55e">Yesil</option>
                    <option value="#3b82f6">Mavi</option>
                    <option value="#8b5cf6">Mor</option>
                    <option value="#ec4899">Pembe</option>
                    <option value="#6b7280">Gri</option>
                </select>
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Ekle
                </button>
            </form>
        </div>

        {{-- Etiket ile Filtrele --}}
        @if($tags->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-700 mb-3">Etikete Gore Filtrele</p>
            <div class="space-y-2">
                @foreach($tags as $tag)
                <a href="{{ route('panel.crm.customers', ['tenant_slug' => $tenant->slug, 'tag_id' => $tag->id]) }}"
                   class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $tag->color }}"></div>
                        <span class="text-sm text-gray-700">{{ $tag->name }}</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ $tagCounts[$tag->id] ?? 0 }} musteri →</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
