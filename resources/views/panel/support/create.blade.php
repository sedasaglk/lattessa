@extends('layouts.panel')
@section('title', 'Yeni Destek Talebi')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.support.index', ['tenant_slug' => $tenant->slug]) }}"
       class="text-gray-400 hover:text-gray-900">← Geri</a>
    <h1 class="text-2xl font-semibold text-gray-900">Yeni Destek Talebi</h1>
</div>

<div class="max-w-2xl bg-white rounded-xl border border-gray-200 p-6">

    <div class="mb-6 p-4 bg-blue-50 rounded-lg text-sm text-blue-700">
        <p class="font-medium mb-1">Destek ekibimiz hakkında</p>
        <p>Taleplerinize genellikle 24 saat icinde yanit veriyoruz. Acil durumlar icin yuksek oncelik secenegi seciniz.</p>
    </div>

    <form method="POST" action="{{ route('panel.support.store', ['tenant_slug' => $tenant->slug]) }}"
          class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Konu</label>
            <input type="text" name="subject" value="{{ old('subject') }}" required
                   placeholder="Sorununuzu kısaca belirtin..."
                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            @error('subject')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Oncelik</label>
            <div class="grid grid-cols-3 gap-3">
                @foreach(['low' => ['Dusuk', 'Genel sorular'], 'medium' => ['Orta', 'Islevsel sorunlar'], 'high' => ['Yuksek', 'Kritik sorunlar']] as $val => [$label, $desc])
                <label class="cursor-pointer">
                    <input type="radio" name="priority" value="{{ $val }}"
                           {{ old('priority', 'medium') === $val ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-gray-900 peer-checked:bg-gray-50 transition">
                        <p class="text-sm font-medium text-gray-900">{{ $label }}</p>
                        <p class="text-xs text-gray-500">{{ $desc }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mesaj</label>
            <textarea name="message" rows="6" required
                      placeholder="Sorununuzu detayli sekilde aciklayin. Ekran goruntusu veya hata mesaji varsa belirtin..."
                      class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">{{ old('message') }}</textarea>
            @error('message')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Talebi Gonder
            </button>
            <a href="{{ route('panel.support.index', ['tenant_slug' => $tenant->slug]) }}"
               class="px-6 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                Iptal
            </a>
        </div>
    </form>
</div>
@endsection
