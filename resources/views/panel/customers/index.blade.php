@extends('layouts.panel')
@section('title', 'Musteriler')
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Musteriler</h1>
    <a href="{{ route('panel.customers.create', ['tenant_slug' => $tenant->slug]) }}"
       class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
        + Yeni Musteri
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
    <form method="GET" class="flex items-center gap-3">
        <input type="text" name="search" value="{{ $search }}" placeholder="Ad veya telefon ara..."
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none flex-1">
        <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            Ara
        </button>
        @if($search)
            <a href="{{ route('panel.customers.index', ['tenant_slug' => $tenant->slug]) }}"
               class="text-sm text-gray-500 hover:text-gray-900">Temizle</a>
        @endif
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200">
    @if($customers->isEmpty())
        <div class="p-12 text-center">
            <p class="text-gray-400">Henuz musteri eklenmemis.</p>
            <a href="{{ route('panel.customers.create', ['tenant_slug' => $tenant->slug]) }}"
               class="inline-block mt-4 text-sm text-gray-900 underline">Ilk musteriyi ekle</a>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($customers as $customer)
            <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                <div>
                    <p class="font-medium text-gray-900">{{ $customer->name }}</p>
                    <p class="text-sm text-gray-500">{{ $customer->phone }}
                        @if($customer->email) &bull; {{ $customer->email }}@endif
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right text-sm text-gray-500">
                        <p>{{ $customer->visit_count }} ziyaret</p>
                        <p>{{ number_format($customer->total_spent, 0, ',', '.') }} TL</p>
                    </div>
                    <a href="{{ route('panel.customers.show', ['tenant_slug' => $tenant->slug, 'id' => $customer->id]) }}"
                       class="text-sm text-gray-500 hover:text-gray-900">Detay</a>
                </div>
            </div>
            @endforeach
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $customers->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
