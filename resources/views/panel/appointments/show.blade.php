@extends('layouts.panel')

@section('title', 'Randevu Detay')

@section('content')
<div class="mb-4 flex items-center gap-3">
    <a href="{{ route('panel.appointments.index', ['tenant_slug' => $tenant->slug]) }}"
       class="text-gray-400 hover:text-gray-900 flex-shrink-0">← Geri</a>
    <h1 class="text-lg font-semibold text-gray-900 flex-1 min-w-0 truncate">Randevu #{{ $appointment->id }}</h1>
    @if(!in_array($appointment->status, ['completed', 'cancelled']))
    <a href="{{ route('panel.appointments.edit', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}"
       class="flex-shrink-0 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
        Düzenle
    </a>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Musteri</p>
                <p class="font-medium text-gray-900 mt-1">{{ $appointment->customer->name }}</p>
                <p class="text-sm text-gray-500">{{ $appointment->customer->phone }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Hizmet</p>
                <p class="font-medium text-gray-900 mt-1">{{ $appointment->service->name }}</p>
                <p class="text-sm text-gray-500">{{ $appointment->service->duration_minutes }} dakika</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Personel</p>
                <p class="font-medium text-gray-900 mt-1">{{ $appointment->staff->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Tarih ve Saat</p>
                <p class="font-medium text-gray-900 mt-1">{{ $appointment->start_time->format('d.m.Y') }}</p>
                <p class="text-sm text-gray-500">{{ $appointment->start_time->format('H:i') }} - {{ $appointment->end_time->format('H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Ucret</p>
                <p class="font-medium text-gray-900 mt-1">{{ number_format($appointment->price, 2, ',', '.') }} TL</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Kaynak</p>
                <p class="font-medium text-gray-900 mt-1">{{ $appointment->source === 'panel' ? 'Panel' : 'Online' }}</p>
            </div>
        </div>
        @if($appointment->notes)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Notlar</p>
            <p class="text-sm text-gray-700 mt-1">{{ $appointment->notes }}</p>
        </div>
        @endif
    </div>

    <div class="space-y-4">
        {{-- Durum Guncelleme --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-700 mb-3">Durum Guncelle</p>
            <form method="POST" action="{{ route('panel.appointments.status', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}" class="space-y-3" id="statusForm">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" id="statusInput" value="{{ $appointment->status }}">
                <input type="hidden" name="payment_method" id="paymentMethodInput" value="cash">

                <div class="grid grid-cols-2 gap-2">
                    @foreach(['pending'=>'Bekliyor','confirmed'=>'Onaylı','cancelled'=>'İptal','no_show'=>'Gelmedi'] as $val => $label)
                    <button type="button" onclick="setStatus('{{ $val }}')"
                            class="status-btn px-3 py-2 rounded-lg text-sm border transition
                            {{ $appointment->status === $val ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}"
                            data-status="{{ $val }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>

                {{-- Tamamlandı — özel buton --}}
                @if($appointment->status !== 'completed')
                <button type="button" onclick="openCompleteModal()"
                        class="w-full bg-green-600 text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-green-700 transition">
                    ✓ Tamamlandı
                </button>
                @else
                <div class="w-full bg-green-50 border border-green-200 text-green-700 py-2.5 rounded-lg text-sm font-semibold text-center">
                    ✓ Tamamlandı
                </div>
                @endif

                {{-- Diğer durumlar için güncelle --}}
                <button type="submit" id="updateBtn"
                        class="w-full bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition hidden">
                    Güncelle
                </button>
            </form>
        </div>

        {{-- Sil --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <form method="POST" action="{{ route('panel.appointments.destroy', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}"
                  onsubmit="return confirm('Bu randevuyu silmek istediginizden emin misiniz?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full bg-red-50 text-red-600 py-2 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                    Randevuyu Sil
                </button>
            </form>
        </div>
    </div>
</div>
{{-- Tekrarlayan Seri --}}
@if($appointment->is_recurring && $seriesAppointments->isNotEmpty())
<div class="mt-4 bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-900">↻ Tekrarlayan Seri ({{ $seriesAppointments->count() }} randevu)</h2>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('panel.appointments.cancel-series', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}">
                @csrf
                <input type="hidden" name="cancel_type" value="from_date">
                <button type="submit" onclick="return confirm('Bu tarihten itibaren tum seri iptal edilsin mi?')"
                        class="text-xs border border-amber-200 text-amber-600 px-3 py-1.5 rounded-lg hover:bg-amber-50">
                    Bu Tarihten Itibaren Iptal
                </button>
            </form>
            <form method="POST" action="{{ route('panel.appointments.cancel-series', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}">
                @csrf
                <input type="hidden" name="cancel_type" value="all">
                <button type="submit" onclick="return confirm('Tum seri iptal edilsin mi?')"
                        class="text-xs border border-red-200 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50">
                    Tum Seriyi Iptal
                </button>
            </form>
        </div>
    </div>
    <div class="space-y-2">
        @foreach($seriesAppointments as $s)
        <div class="flex items-center justify-between p-3 rounded-lg {{ $s->id === $appointment->id ? 'bg-gray-900 text-white' : 'bg-gray-50' }}">
            <div class="flex items-center gap-3">
                <span class="text-sm {{ $s->id === $appointment->id ? 'text-white' : 'text-gray-900' }}">
                    {{ \Carbon\Carbon::parse($s->start_time)->format('d.m.Y H:i') }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs px-2 py-0.5 rounded-full
                    {{ $s->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $s->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $s->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $s->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                    {{ $s->id === $appointment->id ? '!bg-white !text-gray-900' : '' }}">
                    {{ match($s->status) { 'pending' => 'Bekliyor', 'confirmed' => 'Onaylandi', 'completed' => 'Tamamlandi', 'cancelled' => 'Iptal', default => $s->status } }}
                </span>
                @if($s->id !== $appointment->id)
                <a href="{{ route('panel.appointments.show', ['tenant_slug' => $tenant->slug, 'id' => $s->id]) }}"
                   class="text-xs text-gray-500 hover:text-gray-900">Detay</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Tamamlandı Modalı --}}
<div id="completeModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-gray-900">✓ Randevuyu Tamamla</h2>
                <button onclick="closeCompleteModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('panel.appointments.status', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}" id="completeForm">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="completed">

                {{-- Özet --}}
                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">{{ $appointment->service->name }}</span>
                        <span class="font-medium" id="servicePriceLabel">{{ number_format($appointment->price, 2, ',', '.') }} ₺</span>
                    </div>
                    <div id="productLines"></div>
                    <div class="flex justify-between text-sm font-semibold text-gray-900 border-t border-gray-200 mt-2 pt-2">
                        <span>Toplam</span>
                        <span id="totalLabel">{{ number_format($appointment->price, 2, ',', '.') }} ₺</span>
                    </div>
                </div>

                {{-- Ödeme Yöntemi --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ödeme Yöntemi</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="payment-opt cursor-pointer">
                            <input type="radio" name="payment_method" value="cash" checked class="sr-only" onchange="updatePaymentOpt()">
                            <div class="payment-opt-btn px-3 py-2.5 rounded-lg border-2 border-gray-900 bg-gray-900 text-white text-center text-sm font-medium transition">
                                💵 Nakit
                            </div>
                        </label>
                        <label class="payment-opt cursor-pointer">
                            <input type="radio" name="payment_method" value="card" class="sr-only" onchange="updatePaymentOpt()">
                            <div class="payment-opt-btn px-3 py-2.5 rounded-lg border-2 border-gray-200 text-center text-sm font-medium text-gray-600 transition">
                                💳 Kart
                            </div>
                        </label>
                        <label class="payment-opt cursor-pointer">
                            <input type="radio" name="payment_method" value="transfer" class="sr-only" onchange="updatePaymentOpt()">
                            <div class="payment-opt-btn px-3 py-2.5 rounded-lg border-2 border-gray-200 text-center text-sm font-medium text-gray-600 transition">
                                🏦 Havale
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Ürün Satışları --}}
                @if(isset($products) && $products->isNotEmpty())
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ürün Satışı (opsiyonel)</label>
                    <div id="productInputs" class="space-y-2 mb-2"></div>
                    <select id="productSelect"
                            onchange="addProduct(this)"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="">+ Ürün ekle...</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}" data-price="{{ $p->sale_price }}" data-name="{{ $p->name }}" data-unit="{{ $p->unit }}">
                            {{ $p->name }} — {{ number_format($p->sale_price, 2, ',', '.') }} ₺
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <button type="submit"
                        class="w-full bg-green-600 text-white py-3 rounded-xl text-sm font-semibold hover:bg-green-700 transition">
                    Tamamla ve Kasaya Yansıt
                </button>
            </form>
        </div>
    </div>
</div>

<script>
var servicePrice = {{ (float) $appointment->price }};
var addedProducts = {}; // id -> {name, price, qty, unit}

function openCompleteModal() {
    document.getElementById('completeModal').classList.remove('hidden');
    document.getElementById('completeModal').style.display = 'flex';
}
function closeCompleteModal() {
    document.getElementById('completeModal').classList.add('hidden');
    document.getElementById('completeModal').style.display = 'none';
}

function setStatus(val) {
    document.getElementById('statusInput').value = val;
    document.querySelectorAll('.status-btn').forEach(function(btn) {
        var active = btn.dataset.status === val;
        btn.className = btn.className.replace(/bg-gray-900 text-white border-gray-900|border-gray-200 text-gray-600 hover:bg-gray-50/g, '');
        btn.classList.add(...(active ? ['bg-gray-900','text-white','border-gray-900'] : ['border-gray-200','text-gray-600','hover:bg-gray-50']));
    });
    document.getElementById('updateBtn').classList.remove('hidden');
}

function updatePaymentOpt() {
    document.querySelectorAll('.payment-opt input').forEach(function(radio) {
        var div = radio.nextElementSibling;
        if (radio.checked) {
            div.className = div.className.replace('border-gray-200 text-gray-600', 'border-gray-900 bg-gray-900 text-white');
        } else {
            div.className = div.className.replace('border-gray-900 bg-gray-900 text-white', 'border-gray-200 text-gray-600');
        }
    });
}

function addProduct(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;
    var id = parseInt(opt.value);
    if (addedProducts[id]) { sel.value = ''; return; }
    addedProducts[id] = {name: opt.dataset.name, price: parseFloat(opt.dataset.price), qty: 1, unit: opt.dataset.unit};
    sel.value = '';
    renderProducts();
}

function changeQty(id, delta) {
    if (!addedProducts[id]) return;
    addedProducts[id].qty = Math.max(1, addedProducts[id].qty + delta);
    renderProducts();
}

function removeProduct(id) {
    delete addedProducts[id];
    renderProducts();
}

function renderProducts() {
    var container = document.getElementById('productInputs');
    var lines = document.getElementById('productLines');
    var html = '';
    var linesHtml = '';
    var productTotal = 0;

    Object.keys(addedProducts).forEach(function(id) {
        var p = addedProducts[id];
        var sub = p.price * p.qty;
        productTotal += sub;
        html += '<div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg text-sm">' +
            '<input type="hidden" name="products['+id+'][id]" value="'+id+'">' +
            '<input type="hidden" name="products['+id+'][qty]" value="'+p.qty+'">' +
            '<span class="flex-1 text-gray-700">'+p.name+'</span>' +
            '<button type="button" onclick="changeQty('+id+',-1)" class="w-6 h-6 rounded border border-gray-200 text-gray-500 hover:bg-gray-100 flex items-center justify-center leading-none">−</button>' +
            '<span class="w-6 text-center font-medium">'+p.qty+'</span>' +
            '<button type="button" onclick="changeQty('+id+',1)" class="w-6 h-6 rounded border border-gray-200 text-gray-500 hover:bg-gray-100 flex items-center justify-center leading-none">+</button>' +
            '<span class="w-20 text-right text-gray-900 font-medium">'+sub.toFixed(2).replace('.',',')+' ₺</span>' +
            '<button type="button" onclick="removeProduct('+id+')" class="text-red-400 hover:text-red-600 ml-1">✕</button>' +
            '</div>';
        linesHtml += '<div class="flex justify-between text-sm text-gray-500"><span>'+p.name+' x'+p.qty+'</span><span>'+sub.toFixed(2).replace('.',',')+' ₺</span></div>';
    });

    container.innerHTML = html;
    lines.innerHTML = linesHtml;

    var total = servicePrice + productTotal;
    document.getElementById('totalLabel').textContent = total.toFixed(2).replace('.',',') + ' ₺';
}
</script>

@endsection