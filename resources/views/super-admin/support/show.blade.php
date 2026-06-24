@extends('layouts.super-admin')
@section('title', 'Destek Talebi')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('super-admin.support.index') }}" class="text-gray-400 hover:text-gray-900">← Talepler</a>
    <h1 class="text-xl font-semibold text-gray-900">{{ $ticket->subject }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <div class="lg:col-span-2 space-y-4">

        {{-- Orijinal mesaj --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="font-medium text-gray-900">{{ $ticket->user_name }}</p>
                    <p class="text-xs text-gray-500">{{ $ticket->company_name }} &bull; {{ \Carbon\Carbon::parse($ticket->created_at)->format('d.m.Y H:i') }}</p>
                </div>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Kullanici</span>
            </div>
            <p class="text-gray-700 whitespace-pre-line">{{ $ticket->message }}</p>
        </div>

        {{-- Yanitlar --}}
        @foreach($replies as $reply)
        <div class="bg-white rounded-xl border {{ $reply->is_admin_reply ? 'border-blue-200 bg-blue-50' : 'border-gray-200' }} p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="font-medium text-gray-900">{{ $reply->is_admin_reply ? 'Destek Ekibi' : $reply->user_name }}</p>
                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($reply->created_at)->format('d.m.Y H:i') }}</p>
                </div>
                <span class="text-xs px-2 py-1 rounded {{ $reply->is_admin_reply ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $reply->is_admin_reply ? 'Destek' : 'Kullanici' }}
                </span>
            </div>
            <p class="text-gray-700 whitespace-pre-line">{{ $reply->message }}</p>
        </div>
        @endforeach

        {{-- Yanitla --}}
        @if(!in_array($ticket->status, ['closed']))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Yanit Gonder</h2>
            <form method="POST" action="{{ route('super-admin.support.reply', $ticket->id) }}" class="space-y-3">
                @csrf
                <textarea name="message" rows="5" required
                          placeholder="Yanitinizi yazin..."
                          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
                <div class="flex items-center gap-3">
                    <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Acik</option>
                        <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>Islemde</option>
                        <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Cozuldu</option>
                        <option value="closed">Kapat</option>
                    </select>
                    <button type="submit" class="bg-gray-900 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                        Gonder
                    </button>
                </div>
            </form>
        </div>
        @endif

    </div>

    {{-- Sag: Ticket Bilgileri --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Talep Bilgileri</h2>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-xs text-gray-500">Firma</p>
                    <p class="font-medium text-gray-900">{{ $ticket->company_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Gonderen</p>
                    <p class="font-medium text-gray-900">{{ $ticket->user_name }}</p>
                    <p class="text-gray-500">{{ $ticket->user_email }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Durum</p>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $ticket->status === 'open' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $ticket->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $ticket->status === 'resolved' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $ticket->status === 'closed' ? 'bg-gray-100 text-gray-500' : '' }}">
                        {{ match($ticket->status) { 'open' => 'Acik', 'in_progress' => 'Islemde', 'resolved' => 'Cozuldu', 'closed' => 'Kapali', default => $ticket->status } }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Oncelik</p>
                    <form method="POST" action="{{ route('super-admin.support.priority', $ticket->id) }}" class="flex gap-2">
                        @csrf
                        <select name="priority" class="flex-1 px-2 py-1 border border-gray-200 rounded text-xs">
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Dusuk</option>
                            <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Orta</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>Yuksek</option>
                        </select>
                        <button type="submit" class="text-xs bg-gray-100 px-2 py-1 rounded hover:bg-gray-200">Guncelle</button>
                    </form>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Olusturulma</p>
                    <p class="text-gray-700">{{ \Carbon\Carbon::parse($ticket->created_at)->format('d.m.Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
