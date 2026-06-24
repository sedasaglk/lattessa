@extends('layouts.panel')
@section('title', 'Destek Talebi')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.support.index', ['tenant_slug' => $tenant->slug]) }}"
       class="text-gray-400 hover:text-gray-900">← Talepler</a>
    <h1 class="text-xl font-semibold text-gray-900">{{ $ticket->subject }}</h1>
    <span class="text-xs px-2 py-0.5 rounded-full
        {{ $ticket->status === 'open' ? 'bg-red-100 text-red-700' : '' }}
        {{ $ticket->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : '' }}
        {{ $ticket->status === 'resolved' ? 'bg-green-100 text-green-700' : '' }}
        {{ $ticket->status === 'closed' ? 'bg-gray-100 text-gray-500' : '' }}">
        {{ match($ticket->status) {
            'open' => 'Acik',
            'in_progress' => 'Islemde',
            'resolved' => 'Cozuldu',
            'closed' => 'Kapali',
            default => $ticket->status
        } }}
    </span>
</div>

<div class="max-w-3xl space-y-4">

    {{-- Orijinal mesaj --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="font-medium text-gray-900">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($ticket->created_at)->format('d.m.Y H:i') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs px-2 py-0.5 rounded-full
                    {{ $ticket->priority === 'high' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $ticket->priority === 'medium' ? 'bg-amber-100 text-amber-700' : '' }}
                    {{ $ticket->priority === 'low' ? 'bg-gray-100 text-gray-600' : '' }}">
                    {{ match($ticket->priority) { 'high' => 'Yuksek', 'medium' => 'Orta', 'low' => 'Dusuk', default => $ticket->priority } }} Oncelik
                </span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">#{{ $ticket->id }}</span>
            </div>
        </div>
        <p class="text-gray-700 whitespace-pre-line">{{ $ticket->message }}</p>
    </div>

    {{-- Yanitlar --}}
    @foreach($replies as $reply)
    <div class="rounded-xl border p-5
        {{ $reply->is_admin_reply ? 'bg-blue-50 border-blue-200' : 'bg-white border-gray-200' }}">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="font-medium text-gray-900">
                    {{ $reply->is_admin_reply ? '🛡 Lattessa Destek' : ($reply->user_name ?? 'Siz') }}
                </p>
                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($reply->created_at)->format('d.m.Y H:i') }}</p>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $reply->is_admin_reply ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $reply->is_admin_reply ? 'Destek Ekibi' : 'Siz' }}
            </span>
        </div>
        <p class="text-gray-700 whitespace-pre-line">{{ $reply->message }}</p>
    </div>
    @endforeach

    {{-- Yanitla --}}
    @if($ticket->status !== 'closed')
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-3">Yanit Gonder</h2>
        <form method="POST" action="{{ route('panel.support.reply', ['tenant_slug' => $tenant->slug, 'id' => $ticket->id]) }}"
              class="space-y-3">
            @csrf
            <textarea name="message" rows="4" required
                      placeholder="Yanitinizi yazin..."
                      class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
            @error('message')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
            <div class="flex gap-3">
                <button type="submit"
                        class="bg-gray-900 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Gonder
                </button>
                @if(in_array($ticket->status, ['resolved']))
                <form method="POST" action="{{ route('panel.support.close', ['tenant_slug' => $tenant->slug, 'id' => $ticket->id]) }}">
                    @csrf
                    <button type="submit"
                            class="border border-gray-200 text-gray-600 px-6 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        Talebi Kapat
                    </button>
                </form>
                @endif
            </div>
        </form>
    </div>
    @else
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 text-center">
        <p class="text-sm text-gray-500">Bu destek talebi kapatilmis.</p>
        <a href="{{ route('panel.support.create', ['tenant_slug' => $tenant->slug]) }}"
           class="inline-block mt-3 text-sm text-gray-900 underline">Yeni talep olustur</a>
    </div>
    @endif

</div>
@endsection
