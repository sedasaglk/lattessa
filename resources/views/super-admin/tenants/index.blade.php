@extends('layouts.super-admin')
@section('title', 'Firmalar')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Firmalar</h1>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
    <form method="GET" class="flex items-center gap-3">
        <input type="text" name="search" value="{{ $search }}" placeholder="Firma adi, email veya slug..."
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none flex-1">
        <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            <option value="">Tum durumlar</option>
            <option value="trial" {{ $status == 'trial' ? 'selected' : '' }}>Deneme</option>
            <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="suspended" {{ $status == 'suspended' ? 'selected' : '' }}>Askida</option>
            <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Iptal</option>
        </select>
        <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            Filtrele
        </button>
        <a href="{{ route('super-admin.tenants.index') }}" class="text-sm text-gray-500 hover:text-gray-900">Temizle</a>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200">
    @if($tenants->isEmpty())
        <div class="p-12 text-center">
            <p class="text-gray-400">Firma bulunamadi.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($tenants as $tenant)
            <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                <div>
                    <p class="font-medium text-gray-900">{{ $tenant->company_name }}</p>
                    <p class="text-sm text-gray-500">
                        {{ $tenant->email }} &bull;
                        {{ $tenant->slug }} &bull;
                        {{ $tenant->business_type }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Kayit: {{ \Carbon\Carbon::parse($tenant->created_at)->format('d.m.Y') }}
                        @if($tenant->trial_ends_at)
                            &bull; Deneme bitis: {{ \Carbon\Carbon::parse($tenant->trial_ends_at)->format('d.m.Y') }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-1 rounded-full
                        {{ $tenant->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $tenant->status === 'trial' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $tenant->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $tenant->status === 'cancelled' ? 'bg-gray-100 text-gray-700' : '' }}
                    ">
                        {{ match($tenant->status) {
                            'active' => 'Aktif',
                            'trial' => 'Deneme',
                            'suspended' => 'Askida',
                            'cancelled' => 'Iptal',
                            default => $tenant->status
                        } }}
                    </span>
                    <a href="{{ route('super-admin.tenants.show', $tenant->id) }}"
                       class="text-sm text-gray-500 hover:text-gray-900">Detay</a>
                </div>
            </div>
            @endforeach
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $tenants->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
