@extends('layouts.panel')

@section('title', 'Yeni Randevu')

@section('content')

<style>
.cust-results {
    display: none;
    border: 1px solid #e5e7eb;
    border-top: none;
    border-radius: 0 0 10px 10px;
    background: #fff;
    max-height: 240px;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.cust-results .cust-row {
    display: block;
    width: 100%;
    padding: 13px 16px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 14px;
    color: #111;
    background: #fff;
    cursor: pointer;
    text-align: left;
    -webkit-tap-highlight-color: rgba(0,0,0,0.06);
    touch-action: manipulation;
    user-select: none;
    -webkit-user-select: none;
    box-sizing: border-box;
}
.cust-results .cust-row:last-child { border-bottom: none; }
.cust-results .cust-row:active { background: #f9fafb; }
.cust-results .cust-empty {
    padding: 16px;
    text-align: center;
    color: #9ca3af;
    font-size: 14px;
}
/* input açıkken alt köşe düzleşsin */
.cust-search-open {
    border-radius: 10px 10px 0 0 !important;
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
            <input type="text"
                   id="custSearch"
                   autocomplete="off"
                   placeholder="İsim veya son 4 hane telefon..."
                   style="width:100%;padding:10px 16px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;color:#111;background:#fff;box-sizing:border-box;outline:none;font-family:inherit;display:block;">
            <div id="custResults" class="cust-results"></div>
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
                <input type="text"
                       id="groupCustSearch"
                       autocomplete="off"
                       placeholder="Müşteri ekle..."
                       style="width:100%;padding:10px 16px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;color:#111;background:#fff;box-sizing:border-box;outline:none;font-family:inherit;display:block;">
                <div id="groupCustResults" class="cust-results"></div>
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
/* ── Müşteri verisi ────────────────────────────────────── */
var allCustomers = @json($customers->map(fn($c) => [
    'id'    => $c->id,
    'name'  => $c->name,
    'phone' => $c->phone ?? ''
]));

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function filterCust(q, limit) {
    q = (q || '').trim().toLowerCase();
    limit = limit || 50;
    if (!q) return allCustomers.slice(0, limit);
    return allCustomers.filter(function(c) {
        var p = c.phone ? c.phone.toString() : '';
        return c.name.toLowerCase().includes(q) || p.includes(q) || p.slice(-4) === q;
    }).slice(0, limit);
}

/* ── Sonuç listesi render ──────────────────────────────── */
function renderResults(resultsEl, inputEl, list, onSelect) {
    resultsEl.innerHTML = '';
    if (!list.length) {
        resultsEl.innerHTML = '<div class="cust-empty">Müşteri bulunamadı</div>';
        resultsEl.style.display = 'block';
        inputEl.style.borderRadius = '10px 10px 0 0';
        return;
    }
    list.forEach(function(c) {
        var last4 = c.phone ? c.phone.toString().slice(-4) : '';
        var row   = document.createElement('div');
        row.className = 'cust-row';
        row.innerHTML = '<strong>' + escHtml(c.name) + '</strong>' +
            (last4 ? ' <span style="color:#9ca3af;font-size:12px;">···' + last4 + '</span>' : '');

        /* pointerdown: blur'dan önce tetiklenir, selection kaybı olmaz */
        row.addEventListener('pointerdown', function(e) {
            e.preventDefault();
            onSelect(c);
        });
        /* iOS WKWebView fallback */
        row.addEventListener('touchend', function(e) {
            e.preventDefault();
            onSelect(c);
        }, { passive: false });

        resultsEl.appendChild(row);
    });
    resultsEl.style.display = 'block';
    inputEl.style.borderRadius = '10px 10px 0 0';
}

function hideResults(resultsEl, inputEl) {
    resultsEl.style.display = 'none';
    inputEl.style.borderRadius = '10px';
}

/* ── Tekil müşteri ────────────────────────────────────── */
(function() {
    var inp     = document.getElementById('custSearch');
    var results = document.getElementById('custResults');
    var hidden  = document.getElementById('customer_id_input');
    if (!inp || !results) return;

    function show(q) {
        renderResults(results, inp, filterCust(q), function(c) {
            var last4 = c.phone ? c.phone.toString().slice(-4) : '';
            inp.value    = c.name + (last4 ? ' (···' + last4 + ')' : '');
            hidden.value = c.id;
            hideResults(results, inp);
            inp.blur();
        });
    }

    inp.addEventListener('focus', function() {
        hidden.value = '';
        show(inp.value);
        /* Results görünsün: input'u viewport başına kaydır */
        setTimeout(function() {
            inp.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    });
    inp.addEventListener('input', function() {
        hidden.value = '';
        show(inp.value);
    });
    /* Dışarı tıklanınca kapat */
    document.addEventListener('pointerdown', function(e) {
        if (!inp.contains(e.target) && !results.contains(e.target)) {
            hideResults(results, inp);
        }
    });
    document.addEventListener('touchstart', function(e) {
        if (!inp.contains(e.target) && !results.contains(e.target)) {
            hideResults(results, inp);
        }
    }, { passive: true });
})();

/* ── Grup müşteri ─────────────────────────────────────── */
var groupCustomers = [];

(function() {
    var inp     = document.getElementById('groupCustSearch');
    var results = document.getElementById('groupCustResults');
    if (!inp || !results) return;

    function show(q) {
        renderResults(results, inp, filterCust(q), function(c) {
            addGroupCustomer(parseInt(c.id), c.name, String(c.phone || ''));
            inp.value = '';
            hideResults(results, inp);
        });
    }

    inp.addEventListener('focus', function() { show(inp.value); });
    inp.addEventListener('input', function() { show(inp.value); });
    document.addEventListener('pointerdown', function(e) {
        if (!inp.contains(e.target) && !results.contains(e.target)) {
            hideResults(results, inp);
        }
    });
    document.addEventListener('touchstart', function(e) {
        if (!inp.contains(e.target) && !results.contains(e.target)) {
            hideResults(results, inp);
        }
    }, { passive: true });
})();

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
            + (last4 ? ' <span class="text-gray-400 text-xs">···' + last4 + '</span>' : '') + '</span>'
            + '<input type="hidden" name="customer_ids[]" value="' + c.id + '">'
            + '<button type="button" onclick="removeGroupCustomer(' + c.id + ')" class="text-red-400 hover:text-red-600 text-xs ml-2">✕</button>'
            + '</div>';
    }).join('');
}

/* ── Toggle'lar ───────────────────────────────────────── */
(function init() {
    renderGroupCustomers();

    var isGroupChk = document.getElementById('isGroup');
    if (isGroupChk) isGroupChk.addEventListener('change', function() {
        var on = this.checked;
        document.getElementById('singleCustomerSection').classList.toggle('hidden', on);
        document.getElementById('groupSection').classList.toggle('hidden', !on);
        if (on) {
            var rec = document.getElementById('isRecurring');
            if (rec) { rec.checked = false; rec.disabled = true; }
            var ro = document.getElementById('recurringOptions');
            if (ro) ro.classList.add('hidden');
        } else {
            var rec2 = document.getElementById('isRecurring');
            if (rec2) rec2.disabled = false;
        }
    });

    var isRecurringChk = document.getElementById('isRecurring');
    if (isRecurringChk) isRecurringChk.addEventListener('change', function() {
        var ro = document.getElementById('recurringOptions');
        if (ro) ro.classList.toggle('hidden', !this.checked);
    });
})();
</script>
@endsection
