@extends('layouts.panel')
@section('title', 'Bekleme Listesi')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Bekleme Listesi</h1>
    <span class="bg-amber-100 text-amber-700 text-sm font-medium px-3 py-1 rounded-full">
        {{ $waiting->count() }} bekliyor
    </span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Bekleme Listesi --}}
    <div class="lg:col-span-2 space-y-4">

        <div class="bg-white rounded-xl border border-gray-200">
            @if($waiting->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-400">Bekleme listesi bos.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($waiting as $entry)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="font-medium text-gray-900">{{ $entry->customer_name }}</p>
                                <p class="text-sm text-gray-500">{{ $entry->customer_phone }}</p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $entry->status === 'waiting' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $entry->status === 'waiting' ? 'Bekliyor' : 'Bilgilendirildi' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 mb-3">
                            @if($entry->service_name)
                            <div>
                                <span class="text-gray-400">Hizmet:</span>
                                <span class="text-gray-700">{{ $entry->service_name }}</span>
                            </div>
                            @endif
                            @if($entry->staff_name)
                            <div>
                                <span class="text-gray-400">Personel:</span>
                                <span class="text-gray-700">{{ $entry->staff_name }}</span>
                            </div>
                            @endif
                            @if($entry->preferred_date)
                            <div>
                                <span class="text-gray-400">Tercih:</span>
                                <span class="text-gray-700">
                                    {{ \Carbon\Carbon::parse($entry->preferred_date)->format('d.m.Y') }}
                                    @if($entry->preferred_time) {{ $entry->preferred_time }} @endif
                                </span>
                            </div>
                            @endif
                            <div>
                                <span class="text-gray-400">Eklenme:</span>
                                <span class="text-gray-700">{{ \Carbon\Carbon::parse($entry->created_at)->format('d.m.Y H:i') }}</span>
                            </div>
                        </div>

                        @if($entry->notes)
                            <p class="text-xs text-gray-500 bg-gray-50 rounded p-2 mb-3">{{ $entry->notes }}</p>
                        @endif

                        <div class="flex gap-2">
                            @if($entry->status === 'waiting')
                            <form method="POST" action="{{ route('panel.waiting.notify', ['tenant_slug' => $tenant->slug, 'id' => $entry->id]) }}">
                                @csrf
                                <button type="submit" class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition">
                                    Bilgilendir
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('panel.waiting.book', ['tenant_slug' => $tenant->slug, 'id' => $entry->id]) }}">
                                @csrf
                                <button type="submit" class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition">
                                    Randevuya Al
                                </button>
                            </form>
                            <form method="POST" action="{{ route('panel.waiting.cancel', ['tenant_slug' => $tenant->slug, 'id' => $entry->id]) }}"
                                  onsubmit="return confirm('Listeden cikarmak istediginizden emin misiniz?')">
                                @csrf
                                <button type="submit" class="text-xs border border-red-200 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                                    Cikar
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Gecmis --}}
        @if($history->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Gecmis (Son 20)</h2>
            <div class="space-y-2">
                @foreach($history as $entry)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $entry->customer_name }}</p>
                        <p class="text-xs text-gray-500">{{ $entry->customer_phone }} @if($entry->service_name) &bull; {{ $entry->service_name }} @endif</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $entry->status === 'booked' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $entry->status === 'booked' ? 'Randevuya Alindi' : 'Iptal' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Yeni Kayit Formu --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-4">Bekleme Listesine Ekle</h2>
        <form method="POST" action="{{ route('panel.waiting.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Ad Soyad</label>
                <input type="text" name="customer_name" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Telefon</label>
                <input type="text" name="customer_phone" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Sube</label>
                <select name="branch_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Hizmet (opsiyonel)</label>
                <select name="service_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    <option value="">Secilmedi</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Personel (opsiyonel)</label>
                <select name="staff_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    <option value="">Farketmez</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tercih Edilen Tarih</label>
                <input type="date" name="preferred_date"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tercih Edilen Saat</label>
                <input type="time" name="preferred_time"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Not</label>
                <textarea name="notes" rows="2"
                          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
            </div>
            <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Listeye Ekle
            </button>
        </form>
    </div>

</div>
@endsection
