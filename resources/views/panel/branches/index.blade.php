@extends('layouts.panel')
@section('title', 'Subeler')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Sube Yonetimi</h1>
</div>

{{-- Period Filtresi --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <form method="GET" class="flex items-center gap-2 flex-wrap">
        @foreach([
            'today' => 'Bugun',
            'this_week' => 'Bu Hafta',
            'this_month' => 'Bu Ay',
            'last_month' => 'Gecen Ay',
        ] as $value => $label)
        <a href="?period={{ $value }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
               {{ $period === $value ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ $label }}
        </a>
        @endforeach
        <span class="text-xs text-gray-400 ml-2">
            {{ \Carbon\Carbon::parse($startDate)->format('d.m.Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->format('d.m.Y') }}
        </span>
    </form>
</div>

{{-- Sube Karsilastirma --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    @foreach($branches as $branch)
    @php $stats = $branchStats[$branch->id] ?? ['revenue' => 0, 'appointments' => 0, 'staff_count' => 0]; @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="font-semibold text-gray-900">{{ $branch->name }}</p>
                @if($branch->address)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $branch->address }}</p>
                @endif
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $branch->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $branch->status === 'active' ? 'Aktif' : 'Pasif' }}
            </span>
        </div>

        <div class="space-y-3">
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-500">Ciro</span>
                <span class="font-semibold text-green-600">{{ number_format($stats['revenue'], 0, ',', '.') }} TL</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-500">Randevu</span>
                <span class="font-semibold text-gray-900">{{ $stats['appointments'] }}</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-500">Aktif Personel</span>
                <span class="font-semibold text-gray-900">{{ $stats['staff_count'] }}</span>
            </div>
        </div>

        {{-- Duzenle --}}
        <div class="mt-4 pt-4 border-t border-gray-100">
            <form method="POST" action="{{ route('panel.branches.update', ['tenant_slug' => $tenant->slug, 'id' => $branch->id]) }}"
                  class="space-y-2">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="name" value="{{ $branch->name }}" required
                           class="col-span-2 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    <input type="text" name="phone" value="{{ $branch->phone }}" placeholder="Telefon"
                           class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="active" {{ $branch->status === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ $branch->status === 'inactive' ? 'selected' : '' }}>Pasif</option>
                    </select>
                    <input type="text" name="address" value="{{ $branch->address }}" placeholder="Adres"
                           class="col-span-2 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                        Kaydet
                    </button>
                    <form method="POST" action="{{ route('panel.branches.destroy', ['tenant_slug' => $tenant->slug, 'id' => $branch->id]) }}"
                          onsubmit="return confirm('Subeyi silmek istediginizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm hover:bg-red-50 transition">
                            Sil
                        </button>
                    </form>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    {{-- Yeni Sube Ekle --}}
    <div class="bg-white rounded-xl border border-dashed border-gray-300 p-5">
        <p class="font-medium text-gray-700 mb-4">Yeni Sube Ekle</p>
        <form method="POST" action="{{ route('panel.branches.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-gray-600 mb-1">Sube Adi</label>
                <input type="text" name="name" required placeholder="Merkez Sube"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Telefon</label>
                <input type="text" name="phone" placeholder="0212 XXX XX XX"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Adres</label>
                <input type="text" name="address" placeholder="Mahalle, Sokak, No"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            </div>
            <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Sube Ekle
            </button>
        </form>
    </div>
</div>

{{-- Sube Bazli Karsilastirma Tablosu --}}
@if($branches->count() > 1)
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h2 class="font-semibold text-gray-900 mb-4">Sube Karsilastirmasi</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100">
                <tr>
                    <th class="text-left py-2 px-3 text-xs text-gray-500 font-medium">Sube</th>
                    <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Ciro</th>
                    <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Randevu</th>
                    <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Personel</th>
                    <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Ortalama Randevu Degeri</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($branches as $branch)
                @php
                    $stats = $branchStats[$branch->id] ?? ['revenue' => 0, 'appointments' => 0, 'staff_count' => 0];
                    $avgValue = $stats['appointments'] > 0 ? $stats['revenue'] / $stats['appointments'] : 0;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-3 font-medium text-gray-900">{{ $branch->name }}</td>
                    <td class="py-2 px-3 text-right text-green-600 font-medium">{{ number_format($stats['revenue'], 0, ',', '.') }} TL</td>
                    <td class="py-2 px-3 text-right text-gray-700">{{ $stats['appointments'] }}</td>
                    <td class="py-2 px-3 text-right text-gray-700">{{ $stats['staff_count'] }}</td>
                    <td class="py-2 px-3 text-right text-gray-700">{{ number_format($avgValue, 0, ',', '.') }} TL</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t border-gray-200">
                @php
                    $totalRevenue = array_sum(array_column($branchStats, 'revenue'));
                    $totalAppts = array_sum(array_column($branchStats, 'appointments'));
                @endphp
                <tr class="font-semibold">
                    <td class="py-2 px-3 text-gray-900">Toplam</td>
                    <td class="py-2 px-3 text-right text-green-600">{{ number_format($totalRevenue, 0, ',', '.') }} TL</td>
                    <td class="py-2 px-3 text-right text-gray-900">{{ $totalAppts }}</td>
                    <td class="py-2 px-3 text-right text-gray-900">-</td>
                    <td class="py-2 px-3 text-right text-gray-900">-</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

@endsection
