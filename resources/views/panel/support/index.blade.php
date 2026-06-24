@extends('layouts.panel')
@section('title', 'Destek Talepleri')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Destek Talepleri</h1>
    <a href="{{ route('panel.support.create', ['tenant_slug' => $tenant->slug]) }}"
       class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
        + Yeni Talep
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200">
    @if($tickets->isEmpty())
        <div class="p-12 text-center">
            <p class="text-gray-400 mb-4">Henuz destek talebiniz bulunmuyor.</p>
            <a href="{{ route('panel.support.create', ['tenant_slug' => $tenant->slug]) }}"
               class="bg-gray-900 text-white text-sm px-6 py-2.5 rounded-lg hover:bg-gray-800 transition">
                Ilk Talebinizi Olusturun
            </a>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($tickets as $ticket)
            <a href="{{ route('panel.support.show', ['tenant_slug' => $tenant->slug, 'id' => $ticket->id]) }}"
               class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                <div class="flex items-start gap-3">
                    <span class="mt-1.5 w-2 h-2 rounded-full flex-shrink-0
                        {{ $ticket->priority === 'high' ? 'bg-red-500' : '' }}
                        {{ $ticket->priority === 'medium' ? 'bg-amber-500' : '' }}
                        {{ $ticket->priority === 'low' ? 'bg-gray-300' : '' }}"></span>
                    <div>
                        <p class="font-medium text-gray-900">{{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            #{{ $ticket->id }} &bull;
                            {{ \Carbon\Carbon::parse($ticket->created_at)->diffForHumans() }}
                        </p>
                        <p class="text-sm text-gray-600 mt-1 line-clamp-1">{{ $ticket->message }}</p>
                    </div>
                </div>
                <span class="flex-shrink-0 ml-4 text-xs px-2 py-0.5 rounded-full
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
            </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
