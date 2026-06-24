@extends('layouts.panel')
@section('title', 'Yeni Satis')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.sales.index', ['tenant_slug' => $tenant->slug]) }}"
       class="text-gray-400 hover:text-gray-900">← Geri</a>
    <h1 class="text-2xl font-semibold text-gray-900">Hizli Satis</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Sol: Urun/Hizmet Sec --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Arama --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <input type="text" id="itemSearch" placeholder="Urun veya hizmet ara..."
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                   onkeyup="filterItems()">
        </div>

        {{-- Urunler --}}
        @if($products->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm font-medium text-gray-700 mb-3">Urunler</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2" id="productList">
                @foreach($products as $product)
                @php $stock = $stockLevels[$product->id] ?? 0; @endphp
                <button type="button"
                        onclick="addItem('product', {{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->sale_price }}, {{ $stock }})"
                        class="item-card text-left p-3 border border-gray-200 rounded-lg hover:border-gray-900 hover:bg-gray-50 transition {{ $stock <= 0 ? 'opacity-50' : '' }}"
                        data-name="{{ strtolower($product->name) }}"
                        {{ $stock <= 0 ? 'disabled' : '' }}>
                    <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                    <p class="text-xs text-gray-500">{{ number_format($product->sale_price, 0, ',', '.') }} TL</p>
                    <p class="text-xs {{ $stock <= $product->min_stock_level ? 'text-red-500' : 'text-gray-400' }}">
                        Stok: {{ $stock }}
                    </p>
                </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Hizmetler --}}
        @if($services->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm font-medium text-gray-700 mb-3">Hizmetler</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2" id="serviceList">
                @foreach($services as $service)
                <button type="button"
                        onclick="addItem('service', {{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->price }}, 999)"
                        class="item-card text-left p-3 border border-gray-200 rounded-lg hover:border-gray-900 hover:bg-gray-50 transition"
                        data-name="{{ strtolower($service->name) }}">
                    <p class="text-sm font-medium text-gray-900">{{ $service->name }}</p>
                    <p class="text-xs text-gray-500">{{ number_format($service->price, 0, ',', '.') }} TL</p>
                    <p class="text-xs text-gray-400">{{ $service->duration_minutes }} dk</p>
                </button>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Sag: Sepet --}}
    <div class="space-y-4">
        <form method="POST" action="{{ route('panel.sales.store', ['tenant_slug' => $tenant->slug]) }}" id="saleForm">
            @csrf

            {{-- Sube --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sube</label>
                <select name="branch_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Sepet --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-700 mb-3">Sepet</p>
                <div id="cartItems" class="space-y-2 mb-3">
                    <p class="text-sm text-gray-400 text-center py-4" id="emptyCart">Sepet bos</p>
                </div>

                <div class="border-t border-gray-100 pt-3 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Ara Toplam</span>
                        <span id="subtotalDisplay">0,00 TL</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Indirim</span>
                        <span id="discountDisplay">0,00 TL</span>
                    </div>
                    <div class="flex justify-between font-semibold text-base border-t border-gray-100 pt-2 mt-2">
                        <span>Toplam</span>
                        <span id="totalDisplay" class="text-green-600">0,00 TL</span>
                    </div>
                </div>
            </div>

            {{-- Musteri & Personel --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Musteri (opsiyonel)</label>
                    <select name="customer_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="">Anonim</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Personel (opsiyonel)</label>
                    <select name="staff_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="">Secilmedi</option>
                        @foreach($staff as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Odeme Yontemi</label>
                    <select name="payment_method" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="cash">Nakit</option>
                        <option value="card">Kart</option>
                        <option value="transfer">Havale</option>
                        <option value="mixed">Karma</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Not</label>
                    <input type="text" name="notes"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
            </div>

            <div id="itemsContainer"></div>

            <button type="button" onclick="submitSale()"
                    class="w-full bg-green-600 text-white py-3.5 rounded-xl font-semibold text-sm hover:bg-green-700 transition">
                Satisi Tamamla
            </button>
        </form>
    </div>
</div>

<script>
let cart = [];
let itemIndex = 0;

function addItem(type, id, name, price, stock) {
    const existing = cart.find(i => i.type === type && i.id === id);
    if (existing) {
        existing.quantity++;
        existing.total = (existing.quantity * existing.price) - existing.discount;
    } else {
        cart.push({ type, id, name, price, quantity: 1, discount: 0, total: price, index: itemIndex++ });
    }
    renderCart();
}

function removeItem(idx) {
    cart = cart.filter(i => i.index !== idx);
    renderCart();
}

function updateQuantity(idx, qty) {
    const item = cart.find(i => i.index === idx);
    if (item) {
        item.quantity = parseFloat(qty) || 1;
        item.total = (item.quantity * item.price) - item.discount;
        renderCart();
    }
}

function updateDiscount(idx, disc) {
    const item = cart.find(i => i.index === idx);
    if (item) {
        item.discount = parseFloat(disc) || 0;
        item.total = (item.quantity * item.price) - item.discount;
        renderCart();
    }
}

function renderCart() {
    const container = document.getElementById('cartItems');
    const empty = document.getElementById('emptyCart');

    if (cart.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400 text-center py-4" id="emptyCart">Sepet bos</p>';
        updateTotals();
        return;
    }

    container.innerHTML = cart.map(item => `
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-gray-900">${item.name}</p>
                <button type="button" onclick="removeItem(${item.index})" class="text-gray-400 hover:text-red-500 text-xs">✕</button>
            </div>
            <div class="flex items-center gap-2">
                <input type="number" value="${item.quantity}" min="0.01" step="0.01"
                       onchange="updateQuantity(${item.index}, this.value)"
                       class="w-16 px-2 py-1 border border-gray-200 rounded text-xs text-center focus:ring-1 focus:ring-gray-900 outline-none">
                <span class="text-xs text-gray-500">x ${item.price.toLocaleString('tr-TR')} TL</span>
                <input type="number" value="${item.discount}" min="0" step="0.01" placeholder="Indirim"
                       onchange="updateDiscount(${item.index}, this.value)"
                       class="w-20 px-2 py-1 border border-gray-200 rounded text-xs focus:ring-1 focus:ring-gray-900 outline-none">
                <span class="ml-auto text-sm font-semibold text-gray-900">${item.total.toLocaleString('tr-TR', {minimumFractionDigits: 2})} TL</span>
            </div>
        </div>
    `).join('');

    updateTotals();
}

function updateTotals() {
    const subtotal = cart.reduce((s, i) => s + (i.price * i.quantity), 0);
    const discount = cart.reduce((s, i) => s + i.discount, 0);
    const total = subtotal - discount;

    document.getElementById('subtotalDisplay').textContent = subtotal.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' TL';
    document.getElementById('discountDisplay').textContent = discount.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' TL';
    document.getElementById('totalDisplay').textContent = total.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' TL';
}

function submitSale() {
    if (cart.length === 0) {
        alert('Sepet bos! Lutfen urun veya hizmet ekleyin.');
        return;
    }

    const container = document.getElementById('itemsContainer');
    container.innerHTML = '';

    cart.forEach((item, i) => {
        container.innerHTML += `
            <input type="hidden" name="items[${i}][item_type]" value="${item.type}">
            <input type="hidden" name="items[${i}][item_id]" value="${item.id}">
            <input type="hidden" name="items[${i}][quantity]" value="${item.quantity}">
            <input type="hidden" name="items[${i}][unit_price]" value="${item.price}">
            <input type="hidden" name="items[${i}][discount]" value="${item.discount}">
        `;
    });

    document.getElementById('saleForm').submit();
}

function filterItems() {
    const q = document.getElementById('itemSearch').value.toLowerCase();
    document.querySelectorAll('.item-card').forEach(card => {
        card.style.display = card.dataset.name.includes(q) ? 'block' : 'none';
    });
}
</script>
@endsection
