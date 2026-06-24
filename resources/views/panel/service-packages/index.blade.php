@extends('layouts.panel')
@section('title', 'Hizmet Paketleri')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Hizmet Paketleri</h1>
    <p class="text-sm text-gray-500 mt-1">Musteri satislarinda kullanilacak hizmet paketleri tanimlayin.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Paket Listesi --}}
    <div>
        <h2 class="font-semibold text-gray-900 mb-3">Mevcut Paketler</h2>
        @if($packages->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <p class="text-gray-400">Henuz paket tanimlanmamis.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($packages as $pkg)
                @php $items = $packageItems[$pkg->id] ?? collect(); @endphp
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $pkg->name }}</p>
                            <p class="text-xs text-gray-500">{{ $pkg->validity_days }} gun gecerli &bull; {{ $salesStats[$pkg->id] ?? 0 }} kez satildi</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-green-600">{{ number_format($pkg->price, 0, ',', '.') }} TL</p>
                            <form method="POST" action="{{ route('panel.packages.destroy', ['tenant_slug' => $tenant->slug, 'id' => $pkg->id]) }}"
                                  onsubmit="return confirm('Silmek istediginizden emin misiniz?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Sil</button>
                            </form>
                        </div>
                    </div>
                    @if($items->isNotEmpty())
                        <div class="space-y-1">
                            @foreach($items as $item)
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <span>{{ $item->service_name }}</span>
                                <span class="font-medium">{{ $item->quantity }} seans</span>
                            </div>
                            @endforeach
                        </div>
                    @endif
                    @if($pkg->description)
                        <p class="text-xs text-gray-400 mt-2">{{ $pkg->description }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Yeni Paket Olustur --}}
    <div>
        <h2 class="font-semibold text-gray-900 mb-3">Yeni Paket Olustur</h2>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <form method="POST" action="{{ route('panel.packages.store', ['tenant_slug' => $tenant->slug]) }}" id="packageForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Paket Adi</label>
                        <input type="text" name="name" required placeholder="ornek: Bakim Paketi 10 Seans"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fiyat (TL)</label>
                            <input type="number" name="price" min="0" step="0.01" required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gecerlilik (gun)</label>
                            <input type="number" name="validity_days" min="1" value="365" required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Aciklama</label>
                        <textarea name="description" rows="2"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
                    </div>

                    {{-- Hizmetler --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">Paket Icerigi</label>
                            <button type="button" onclick="addServiceRow()"
                                    class="text-xs text-gray-600 border border-gray-200 px-2 py-1 rounded hover:bg-gray-50">
                                + Hizmet Ekle
                            </button>
                        </div>
                        <div id="serviceRows" class="space-y-2">
                            <div class="flex items-center gap-2 service-row">
                                <select name="services[0][service_id]" required
                                        class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                                    <option value="">Hizmet secin</option>
                                    @foreach($services as $svc)
                                        <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="services[0][quantity]" value="1" min="1"
                                       placeholder="Adet"
                                       class="w-20 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                        Paket Olustur
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
let rowIndex = 1;
const services = @json($services);

function addServiceRow() {
    const container = document.getElementById('serviceRows');
    const div = document.createElement('div');
    div.className = 'flex items-center gap-2 service-row';
    div.innerHTML = `
        <select name="services[${rowIndex}][service_id]" required
                class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            <option value="">Hizmet secin</option>
            ${services.map(s => `<option value="${s.id}">${s.name}</option>`).join('')}
        </select>
        <input type="number" name="services[${rowIndex}][quantity]" value="1" min="1"
               placeholder="Adet"
               class="w-20 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 text-sm">✕</button>
    `;
    container.appendChild(div);
    rowIndex++;
}
</script>
@endsection
