@extends('layouts.panel')
@section('title', 'Hizmetler')
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Hizmetler</h1>
    <a href="{{ route('panel.services.create', ['tenant_slug' => $tenant->slug]) }}"
       class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
        + Yeni Hizmet
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200">
    @if($services->isEmpty())
        <div class="p-12 text-center">
            <p class="text-gray-400">Henuz hizmet eklenmemis.</p>
            <a href="{{ route('panel.services.create', ['tenant_slug' => $tenant->slug]) }}"
               class="inline-block mt-4 text-sm text-gray-900 underline">Ilk hizmeti ekle</a>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($services as $service)
            <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                <div>
                    <p class="font-medium text-gray-900">{{ $service->name }}</p>
                    <p class="text-sm text-gray-500">
                        {{ isset($service->category_id) && isset($categories[$service->category_id]) ? $categories[$service->category_id] : 'Kategorisiz' }}
                        &bull; {{ $service->duration_minutes }} dk
                        @if($service->is_online_bookable)
                            &bull; <span class="text-green-600">Online rezervasyon acik</span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="font-medium text-gray-900">{{ number_format($service->price, 0, ',', '.') }} TL</span>
                    <span class="text-xs px-2 py-1 rounded-full {{ $service->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $service->status === 'active' ? 'Aktif' : 'Pasif' }}
                    </span>
                    <a href="{{ route('panel.services.edit', ['tenant_slug' => $tenant->slug, 'id' => $service->id]) }}"
                       class="text-sm text-gray-500 hover:text-gray-900">Duzenle</a>
                    <form method="POST" action="{{ route('panel.services.destroy', ['tenant_slug' => $tenant->slug, 'id' => $service->id]) }}"
                          onsubmit="return confirm('Bu hizmeti silmek istediginizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700">Sil</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
