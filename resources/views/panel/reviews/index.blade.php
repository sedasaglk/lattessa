@extends('layouts.panel')
@section('title', 'Yorumlar')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Yorumlar</h1>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
@endif

{{-- İstatistik kartları --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Toplam Yorum</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $stats->total ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Ortalama Puan</p>
        <p class="text-2xl font-semibold text-yellow-500 mt-1">
            {{ $stats->avg_rating ? number_format($stats->avg_rating, 1) : '—' }} ★
        </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Yayında</p>
        <p class="text-2xl font-semibold text-green-600 mt-1">{{ $stats->published ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Gizli</p>
        <p class="text-2xl font-semibold text-gray-400 mt-1">{{ $stats->hidden ?? 0 }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @if($reviews->isEmpty())
        <div class="p-12 text-center text-gray-400">Henüz yorum yok.</div>
    @else
    <div class="divide-y divide-gray-100">
        @foreach($reviews as $review)
        <div class="p-4 flex items-start gap-4">
            {{-- Avatar --}}
            <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-sm flex-shrink-0">
                {{ strtoupper(substr($review->customer_name ?? 'M', 0, 1)) }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-medium text-gray-900 text-sm">{{ $review->customer_name ?? 'Müşteri' }}</span>
                    @if($review->rating > 0)
                    <span class="text-yellow-500 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                    @endif
                    @if($review->service_name)
                    <span class="text-xs text-gray-400">· {{ $review->service_name }}</span>
                    @endif
                    <span class="text-xs text-gray-400">· {{ \Carbon\Carbon::parse($review->created_at)->format('d.m.Y') }}</span>
                    @if($review->is_published)
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Yayında</span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Gizli</span>
                    @endif
                </div>
                @if($review->comment)
                <p class="text-sm text-gray-600 mt-1">{{ $review->comment }}</p>
                @else
                <p class="text-xs text-gray-400 mt-1 italic">Yorum metni yok</p>
                @endif
            </div>

            {{-- Aksiyonlar --}}
            <div class="flex gap-2 flex-shrink-0">
                @if($review->is_published)
                <form method="POST" action="{{ route('panel.reviews.hide', ['tenant_slug' => $tenant->slug, 'id' => $review->id]) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Gizle</button>
                </form>
                @else
                <form method="POST" action="{{ route('panel.reviews.publish', ['tenant_slug' => $tenant->slug, 'id' => $review->id]) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-green-200 text-green-700 hover:bg-green-50 transition">Yayınla</button>
                </form>
                @endif
                <form method="POST" action="{{ route('panel.reviews.destroy', ['tenant_slug' => $tenant->slug, 'id' => $review->id]) }}"
                      onsubmit="return confirm('Bu yorumu silmek istediğinizden emin misiniz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition">Sil</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $reviews->links() }}
    </div>
    @endif
</div>
@endsection
