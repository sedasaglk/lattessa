@extends('layouts.panel')

@section('title', 'Yeni Randevu')

@section('content')

{{-- Müşteri dropdown stilleri --}}
<style>
#custDropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 9999;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    max-height: 300px;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    margin-top: 4px;
    display: none;
}
#custDropdown .cust-row {
    padding: 14px 16px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    font-size: 14px;
    color: #111;
    background: #fff;
    -webkit-tap-highlight-color: rgba(0,0,0,0.06);
    touch-action: manipulation;
    user-select: none;
    -webkit-user-select: none;
}
#custDropdown .cust-row:last-child { border-bottom: none; }
#custDropdown .cust-row:active { background: #f9fafb; }
#custDropdown .cust-empty {
    padding: 16px;
    text-align: center;
    color: #9ca3af;
    font-size: 14px;
}
</style>

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.appointments.index', ['tenant_slug' => $tenant->slug]) }}"
       class="text-gray-400 hover:text-gray-900">← Geri</a>
    <h1 class="text-2xl font-semibold text-gray-900">Yeni Randevu</h1>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('panel.appointments.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-4">
        @csrf

        @if($userBranchId)
        <input type="hidden" name="branch_id" value="{{ old('branch_id', $userBranchId) }}">
        <div class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
            🏢 {{ $branches->firstWhere('id', $userBranchId)?->name ?? 'Şube' }}
        </div>
        @else
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Şube</label>
            <select name="branch_id" required
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                <option value="">Şube seçin</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Grup Randevusu Toggle --}}
        <div class="border border-indigo-100 rounded-xl p-4 bg-indigo-50/40">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_group" value="1" id="isGroup"
                       class="rounded border-gray-300 accent-indigo-600">
                <span class="text-sm font-medium text-gray-700">👥 Grup Randevusu</span>
            </label>
        </div>

        {{-- Tekil Müşteri --}}
        <div id="singleCustomerSection">
            <label class="block text-sm font-medium text-gray-700 mb-1">Müşteri</label>
            <div style="position:relative;">
                <input type="text"
                       id="custSearch"
                       autocomplete="off"
                       placeholder="İsim veya son 4 hane telefon..."
                       style="width:100%;padding:10px 16px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;color:#111;background:#fff;box-sizing:border-box;outline:none;font-family:inherit;"
                       onfocus="custOnFocus()"
                       oninput="custOnInput(this.value)">
                <div id="custDropdown"></div>
            </div>
            <input type="hidden" name="customer_id" id="customer_id_input" value="{{ old('customer_id') }}">
        </div>

        {{-- Grup Müşteri --}}
        <div id="groupSection" class="hidden space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Grup Kapasitesi (max katılımcı)</label>
                <input type="number" name="group_capacity" min="1" max="500" value="10"
                       class="w-32 px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Katılımcılar</label>
                <div id="groupCustomerList" class="space-y-2 mb-2"></div>
                <div style="position:relative;">
                    <input type="text"
                           id="groupCustSearch"
                           autocomplete="off"
                           placeholder="Müşteri ekle..."
                           style="width:100%;padding:10px 16px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;color:#111;background:#fff;box-sizing:border-box;outline:none;font-family:inherit;"
                           oninput="groupCustOnInput(this.value)"
                           onfocus="groupCustOnFocus()">
                    <div id="groupCustDropdown" style="position:absolute;top:100%;left:0;right:0;z-index:9999;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);max-height:250px;overflow-y:auto;-webkit-overflow-scrolling:touch;margin-top:4px;display:none;"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Arama yaparak müşteri ekleyin.</p>
            </div>
        </div>

        {{-- Hizmet --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hizmet</label>
            <select name="service_id" required
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                <option value="">Hizmet secin</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                        {{ $service->name }} ({{ $service->duration_minutes }} dk - {{ number_format($service->price, 0) }} TL)
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Personel --}}
        @if($authUser->role === 'firma_sahibi')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Personel</label>
            <select name="staff_id" required
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                <option value="">Personel seçin</option>
                @foreach($staff as $member)
                    <option value="{{ $member->id }}" {{ old('staff_id') == $member->id ? 'selected' : '' }}>
                        {{ $member->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @else
        <input type="hidden" name="staff_id" value="{{ $authUser->id }}">
        <div class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
            👤 {{ $authUser->name }}
        </div>
        @endif

        {{-- Tarih ve Saat --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tarih ve Saat</label>
            <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required
                   min="{{ now()->format('Y-m-d\TH:i') }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
        </div>

        {{-- Notlar --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notlar (opsiyonel)</label>
            <textarea name="notes" rows="3"
                      class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                      placeholder="Randevuya ait notlar...">{{ old('notes') }}</textarea>
        </div>

        {{-- Tekrarlayan Randevu --}}
        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
            <label class="flex items-center gap-2 cursor-pointer mb-3">
                <input type="checkbox" name="is_recurring" value="1" id="isRecurring"
                       class="rounded border-gray-300">
                <span class="text-sm font-medium text-gray-700">Tekrarlayan Randevu</span>
            </label>
            <div id="recurringOptions" class="hidden space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tekrar Siklikli</label>
                        <select name="recurrence_rule"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                            <option value="weekly">Haftalik</option>
                            <option value="biweekly">2 Haftada Bir</option>
                            <option value="monthly">Aylik</option>
                            <option value="daily">Gunluk</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Kac Kez Tekrarlansin</label>
                        <input type="number" name="recurrence_count" min="2" max="52" value="4"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"
                               placeholder="ornek: 8">
                    </div>
                </div>
                <p class="text-xs text-gray-400">Cakisan saatler otomatik atlanir.</p>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Randevu Olustur
            </button>
            <a href="{{ route('panel.appointments.index', ['tenant_slug' => $tenant->slug]) }}"
               class="px-6 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                Iptal
            </a>
        </div>
    </form>
</div>

<script>
/* ============================================================
   Müşteri verisi
   ============================================================ */
var allCustomers = @json($customers->map(fn($c) => [
    'id'    => $c->id,
    'name'  => $c->name,
    'phone' => $c->phone ?? ''
]));

/* ============================================================
   Yardımcı fonksiyonlar
   ============================================================ */
function escHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function filterCust(q, limit) {
    q = (q || '').trim().toLowerCase();
    limit = limit || 50;
    if (!q) return allCustomers.slice(0, limit);
    return allCustomers.filter(function(c) {
        var phone = c.phone ? c.phone.toString() : '';
        return c.name.toLowerCase().includes(q) ||
               phone.includes(q) ||
               phone.slice(-4) === q;
    }).slice(0, limit);
}

/* ============================================================
   TEKİL MÜŞTERİ — dropdown
   ============================================================ */
var custSelected = false;

function custOnFocus() {
    custSelected = false;
    showCustDropdown(document.getElementById('custSearch').value);
}

function custOnInput(val) {
    custSelected = false;
    document.getElementById('customer_id_input').value = '';
    showCustDropdown(val);
}

function showCustDropdown(q) {
    var dd   = document.getElementById('custDropdown');
    var list = filterCust(q);
    dd.innerHTML = '';

    if (!list.length) {
        dd.innerHTML = '<div class="cust-empty">Müşteri bulunamadı</div>';
        dd.style.display = 'block';
        return;
    }

    list.forEach(function(c) {
        var last4 = c.phone ? c.phone.toString().slice(-4) : '';
        var row   = document.createElement('div');
        row.className = 'cust-row';
        row.innerHTML  = '<strong>' + escHtml(c.name) + '</strong>' +
            (last4 ? ' <span style="color:#9ca3af;font-size:12px;">···' + last4 + '</span>' : '');

        /* mousedown yerine pointerdown: blur'dan önce tetiklenir */
        row.addEventListener('pointerdown', function(e) {
            e.preventDefault(); /* input blur'u engelle */
            selectCust(c);
        });
        /* Fallback: touch cihazlarda pointerdown desteklenmiyorsa */
        row.addEventListener('touchend', function(e) {
            e.preventDefault();
            selectCust(c);
        }, { passive: false });

        dd.appendChild(row);
    });

    dd.style.display = 'block';
}

function selectCust(c) {
    custSelected = true;
    var last4    = c.phone ? c.phone.toString().slice(-4) : '';
    var dispText = c.name + (last4 ? ' (···' + last4 + ')' : '');

    document.getElementById('custSearch').value        = dispText;
    document.getElementById('customer_id_input').value = c.id;
    hideCustDropdown();
}

function hideCustDropdown() {
    var dd = document.getElementById('custDropdown');
    if (dd) dd.style.display = 'none';
}

/* Dışarı tıklanınca kapat */
document.addEventListener('pointerdown', function(e) {
    var search = document.getElementById('custSearch');
    var dd     = document.getElementById('custDropdown');
    if (search && dd && !search.contains(e.target) && !dd.contains(e.target)) {
        hideCustDropdown();
    }
});

/* ============================================================
   GRUP MÜŞTERİ — dropdown
   ============================================================ */
var groupCustomers = [];

function groupCustOnFocus() {
    showGroupDropdown(document.getElementById('groupCustSearch').value);
}

function groupCustOnInput(val) {
    showGroupDropdown(val);
}

function showGroupDropdown(q) {
    var dd   = document.getElementById('groupCustDropdown');
    var list = filterCust(q);
    dd.innerHTML = '';

    if (!list.length) {
        dd.innerHTML = '<div style="padding:16px;text-align:center;color:#9ca3af;font-size:14px;">Müşteri bulunamadı</div>';
        dd.style.display = 'block';
        return;
    }

    list.forEach(function(c) {
        var last4 = c.phone ? c.phone.toString().slice(-4) : '';
        var row   = document.createElement('div');
        row.className = 'cust-row';
        row.innerHTML  = '<strong>' + escHtml(c.name) + '</strong>' +
            (last4 ? ' <span style="color:#9ca3af;font-size:12px;">···' + last4 + '</span>' : '');

        row.addEventListener('pointerdown', function(e) {
            e.preventDefault();
            addGroupCustomer(parseInt(c.id), c.name, String(c.phone || ''));
            document.getElementById('groupCustSearch').value = '';
            dd.style.display = 'none';
        });
        row.addEventListener('touchend', function(e) {
            e.preventDefault();
            addGroupCustomer(parseInt(c.id), c.name, String(c.phone || ''));
            document.getElementById('groupCustSearch').value = '';
            dd.style.display = 'none';
        }, { passive: false });

        dd.appendChild(row);
    });

    dd.style.display = 'block';
}

document.addEventListener('pointerdown', function(e) {
    var search = document.getElementById('groupCustSearch');
    var dd     = document.getElementById('groupCustDropdown');
    if (search && dd && !search.contains(e.target) && !dd.contains(e.target)) {
        dd.style.display = 'none';
    }
});

function addGroupCustomer(id, name, phone) {
    if (groupCustomers.find(function(c) { return c.id == id; })) return;
    groupCustomers.push({ id: id, name: name, phone: phone });
    renderGroupCustomers();
}

function removeGroupCustomer(id) {
    groupCustomers = groupCustomers.filter(function(c) { return c.id != id; });
    renderGroupCustomers();
}

function renderGroupCustomers() {
    var list = document.getElementById('groupCustomerList');
    if (!list) return;
    if (!groupCustomers.length) {
        list.innerHTML = '<p class="text-xs text-gray-400 italic">Henüz katılımcı eklenmedi.</p>';
        return;
    }
    list.innerHTML = groupCustomers.map(function(c) {
        var last4 = c.phone ? c.phone.toString().slice(-4) : '';
        return '<div class="flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm">'
            + '<span><strong>' + escHtml(c.name) + '</strong>'
            + (last4 ? ' <span class="text-gray-400 text-xs">···' + last4 + '</span>' : '')
            + '</span>'
            + '<input type="hidden" name="customer_ids[]" value="' + c.id + '">'
            + '<button type="button" onclick="removeGroupCustomer(' + c.id + ')" class="text-red-400 hover:text-red-600 text-xs ml-2">✕</button>'
            + '</div>';
    }).join('');
}

/* ============================================================
   Grup / Tekrarlayan toggle
   ============================================================ */
(function init() {
    renderGroupCustomers();

    var isGroupChk = document.getElementById('isGroup');
    if (isGroupChk) isGroupChk.addEventListener('change', toggleGroupMode);

    var isRecurringChk = document.getElementById('isRecurring');
    if (isRecurringChk) isRecurringChk.addEventListener('change', toggleRecurring);
})();

function toggleGroupMode() {
    var isGroup = document.getElementById('isGroup').checked;
    document.getElementById('singleCustomerSection').classList.toggle('hidden', isGroup);
    document.getElementById('groupSection').classList.toggle('hidden', !isGroup);
    if (isGroup) {
        var rec = document.getElementById('isRecurring');
        if (rec) { rec.checked = false; rec.disabled = true; }
        var ro = document.getElementById('recurringOptions');
        if (ro) ro.classList.add('hidden');
    } else {
        var rec2 = document.getElementById('isRecurring');
        if (rec2) rec2.disabled = false;
    }
}

function toggleRecurring() {
    var cb = document.getElementById('isRecurring');
    var ro = document.getElementById('recurringOptions');
    if (cb && ro) ro.classList.toggle('hidden', !cb.checked);
}
</script>
@endsection
