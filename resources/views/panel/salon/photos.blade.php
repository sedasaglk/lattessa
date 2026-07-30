@extends('layouts.panel')
@section('title', 'Salon Fotoğrafları')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Salon Fotoğrafları</h1>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-sm font-medium text-gray-700 mb-4">Yeni Fotoğraf Yükle</h2>
        <form method="POST" action="{{ route('panel.salon.photos.store', $tenant->slug) }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="photos[]" multiple accept="image/*"
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-900 file:text-white hover:file:bg-gray-800">
            <button type="submit" class="mt-3 bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">Yükle</button>
        </form>
    </div>

    @if($photos->count())
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($photos as $photo)
        <div class="relative group rounded-xl overflow-hidden border border-gray-200">
            <img src="{{ '/' . $photo->path }}" class="w-full h-48 object-cover">
            <form method="POST" action="{{ route('panel.salon.photos.destroy', [$tenant->slug, $photo->id]) }}" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Sil?')" class="bg-red-500 text-white rounded-full w-7 h-7 flex items-center justify-center text-xs hover:bg-red-600">✕</button>
            </form>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-sm text-gray-400 text-center py-8">Henüz fotoğraf yüklenmedi.</p>
    @endif
</div>
@endsection
