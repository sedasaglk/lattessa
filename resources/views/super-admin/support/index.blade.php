@extends('layouts.super-admin')
@section('title', 'Destek Talepleri')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Destek Talepleri</h1>
</div>

{{-- Filtreler --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
    <div class="flex items-center gap-3 flex-wrap">
        @foreach(['open' => 'Acik', 'in_progress' => 'Islemde', 'resolved' => 'Cozuldu', 'closed' => 'Kapali', 'all' => 'Tumu'] as $val => $label)
        <a href="{{ route('super-admin.support.index', ['status' => $val]) }}"
           class="text-sm px-3 py-1.5 rounded-lg {{ $status === $val ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ $label }}
            @if(isset($counts[$val]))
                <span class="ml-1 text-xs {{ $status === $val ? 'text-gray-300' : 'text-gray-400' }}">({{ $counts[$val] }})</span>
            @endif
        </a>
        @endforeach
    </div>
</div>

{{-- Ticket Listesi --}}
<div class="bg-white rounded-xl border border-gray-200">
    @if($tickets->isEmpty())
        <div class="p-12 text-center">
            <p class="text-gray-400">Destek talebi bulunmuyor.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($tickets as $ticket)
            <a href="{{ route('super-admin.support.show', $ticket->id) }}"
               class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 w-2 h-2 rounded-full flex-shrink-0
                        {{ $ticket->priority === 'high' ? 'bg-red-500' : '' }}
                        {{ $ticket->priority === 'medium' ? 'bg-amber-500' : '' }}
                        {{ $ticket->priority === 'low' ? 'bg-gray-300' : '' }}"></span>
                    <div>
                        <p class="font-medium text-gray-900">{{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $ticket->company_name }} &bull; {{ $ticket->user_name }} &bull;
                            {{ \Carbon\Carbon::parse($ticket->created_at)->diffForHumans() }}
                        </p>
                        <p class="text-sm text-gray-600 mt-1 line-clamp-1">{{ $ticket->message }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $ticket->status === 'open' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $ticket->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $ticket->status === 'resolved' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $ticket->status === 'closed' ? 'bg-gray-100 text-gray-500' : '' }}">
                        {{ match($ticket->status) { 'open' => 'Acik', 'in_progress' => 'Islemde', 'resolved' => 'Cozuldu', 'closed' => 'Kapali', default => $ticket->status } }}
                    </span>
                </div>
            </a>
            @endforeach
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
