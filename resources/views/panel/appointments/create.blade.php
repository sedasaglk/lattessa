@extends('layouts.panel')

@section('title', 'Yeni Randevu')

@section('content')

<div class="mb-4 md:mb-6 flex items-center gap-3">
    <a href="{{ route('panel.appointments.index', ['tenant_slug' => $tenant->slug]) }}"
       class="text-gray-400 hover:text-gray-900 text-sm">← Geri</a>
    <h1 class="text-xl md:text-2xl font-semibold text-gray-900">Yeni Randevu</h1>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-4 md:p-6 w-full md:max-w-2xl">

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('panel.appointments.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-4">
        @csrf

        {{-- Şube --}}
        @if($userBranchId)
            <input type="hidden" name="branch_id" value="{{ old('branch_id', $userBranchId) }}">
            <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
                🏢 {{ $branches->firstWhere('id', $userBranchId)?->name ?? 'Şube' }}
            </div>
        @else
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Şube</label>
                <select name="branch_id" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm bg-white">
                    <option value="">Şube seçin</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Grup Randevusu --}}
        <div class="border border-indigo-100 rounded-xl p-4 bg-indigo-50/40">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_group" value="1" id="isGroup"
                       class="w-4 h-4 rounded border-gray-300 accent-indigo-600">
                <span class="text-sm font-medium text-gray-700">👥 Grup Randevusu</span>
            </label>
        </div>

        {{-- Tek hidden input, hem mobil hem masaüstü bu alanı günceller --}}
        <input type="hidden" name="customer_id" id="customer_id_input" value="{{ old('customer_id') }}">

        {{-- TEKİL MÜŞTERİ --}}
        <div id="singleCustomerSection">
            <label class="block text-sm font-medium text-gray-700 mb-1">Müşteri</label>
            <input type="text"
                   id="custSearch"
                   autocomplete="off"
                   inputmode="search"
                   placeholder="İsim veya son 4 hane telefon..."
                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm bg-white"
                   style="font-size:16px;">
            {{-- font-size:16px iOS'ta zoom'u önler --}}
        </div>

        {{-- GRUP MÜŞTERİ --}}
        <div id="groupSection" class="hidden space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Grup Kapasitesi</label>
                <input type="number" name="group_capacity" min="1" max="500" value="10"
                       class="w-32 px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Katılımcılar</label>
                <div id="groupCustomerList" class="space-y-2 mb-2"></div>
                <input type="text"
                       id="groupCustSearch"
                       autocomplete="off"
                       inputmode="search"
                       placeholder="Müşteri ekle..."
                       class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm bg-white"
                       style="font-size:16px;">
                <p class="text-xs text-gray-400 mt-1">Arama yaparak müşteri ekleyin.</p>
            </div>
        </div>

        {{-- Hizmet --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hizmet</label>
            <select name="service_id" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm bg-white">
                <option value="">Hizmet seçin</option>
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
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm bg-white">
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
            <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
                👤 {{ $authUser->name }}
            </div>
        @endif

        {{-- Tarih ve Saat --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tarih ve Saat</label>
            <input type="datetime-local" name="start_time"
                   value="{{ old('start_time', request('date')) }}" required
                   min="{{ now()->format('Y-m-d\TH:i') }}"
                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm bg-white"
                   style="font-size:16px;">
        </div>

        {{-- Notlar --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notlar (opsiyonel)</label>
            <textarea name="notes" rows="3"
                      class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm resize-none"
                      style="font-size:16px;"
                      placeholder="Randevuya ait notlar...">{{ old('notes') }}</textarea>
        </div>

        {{-- Tekrarlayan --}}
        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
            <label class="flex items-center gap-3 cursor-pointer mb-3">
                <input type="checkbox" name="is_recurring" value="1" id="isRecurring"
                       class="w-4 h-4 rounded border-gray-300">
                <span class="text-sm font-medium text-gray-700">Tekrarlayan Randevu</span>
            </label>
            <div id="recurringOptions" class="hidden space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tekrar Sıklığı</label>
                        <select name="recurrence_rule"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none bg-white">
                            <option value="weekly">Haftalık</option>
                            <option value="biweekly">2 Haftada Bir</option>
                            <option value="monthly">Aylık</option>
                            <option value="daily">Günlük</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Kaç Kez</label>
                        <input type="number" name="recurrence_count" min="2" max="52" value="4"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                </div>
                <p class="text-xs text-gray-400">Çakışan saatler otomatik atlanır.</p>
            </div>
        </div>

        {{-- Butonlar --}}
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 md:flex-none bg-gray-900 text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-gray-800 transition text-center">
                Randevu Oluştur
            </button>
            <a href="{{ route('panel.appointments.index', ['tenant_slug' => $tenant->slug]) }}"
               class="flex-1 md:flex-none px-6 py-3 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition text-center">
                İptal
            </a>
        </div>
    </form>
</div>

{{-- Dropdown div'leri script tarafından body'ye eklenir --}}

<script>
// Müşteri verisi — global, debug edilebilir
window._AC = [];
try {
    window._AC = @json($customers->map(fn($c) => ['id'=>(int)$c->id, 'name'=> (string)$c->name, 'phone'=> (string)($c->phone ?? '')]));
} catch(e) {
    console.error('Müşteri veri hatası:', e);
}

document.addEventListener('DOMContentLoaded', function () {
    var _AC = window._AC;
    var _GC = [];

    // Dropdown'ları doğrudan body'ye ekle — layout'un hiçbir overflow/transform'u clip edemez
    var _ddStyle = [
        'display:none',
        'position:fixed',
        'z-index:2147483647',   /* maksimum z-index */
        'background:#fff',
        'border:1px solid #E5E7EB',
        'border-radius:12px',
        'box-shadow:0 8px 24px rgba(0,0,0,0.14)',
        'overflow-y:auto',
        '-webkit-overflow-scrolling:touch',
    ].join(';');

    var cDD = document.createElement('div');
    cDD.id = '_custDD';
    cDD.setAttribute('style', _ddStyle);
    document.body.appendChild(cDD);

    var gDD = document.createElement('div');
    gDD.id = '_grpDD';
    gDD.setAttribute('style', _ddStyle);
    document.body.appendChild(gDD);

    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function filter(q) {
        q = (q || '').trim().toLowerCase();
        if (!q) return _AC.slice(0, 60);
        return _AC.filter(function (c) {
            var p = c.phone ? String(c.phone) : '';
            return c.name.toLowerCase().includes(q) || p.includes(q) || p.slice(-4) === q;
        }).slice(0, 60);
    }

    function last4(phone) {
        return phone ? String(phone).slice(-4) : '';
    }

    // ── Dropdown konumlama ────────────────────────────────────────────────────
    function positionDD(inp, dd) {
        var rect = inp.getBoundingClientRect();
        var vw   = window.innerWidth;
        var vh   = window.innerHeight;
        var spaceBelow = vh - rect.bottom - 8;
        var spaceAbove = rect.top - 8;
        var maxH = Math.min(280, Math.max(spaceBelow, spaceAbove) - 8);

        dd.style.left  = rect.left + 'px';
        dd.style.width = rect.width + 'px';
        dd.style.maxHeight = maxH + 'px';

        if (spaceBelow >= 120 || spaceBelow >= spaceAbove) {
            dd.style.top    = (rect.bottom + 4) + 'px';
            dd.style.bottom = 'auto';
        } else {
            dd.style.bottom = (vh - rect.top + 4) + 'px';
            dd.style.top    = 'auto';
        }
    }

    function showDD(inp, dd) {
        positionDD(inp, dd);
        dd.style.display = 'block';
    }

    function hideDD(dd) {
        dd.style.display = 'none';
    }

    // ── Dropdown içeriği render ───────────────────────────────────────────────
    function renderDD(inp, dd, list, onSelect) {
        dd.innerHTML = '';

        if (!list.length) {
            dd.innerHTML = '<div style="padding:14px 16px;color:#9CA3AF;font-size:14px;text-align:center;">Müşteri bulunamadı</div>';
            showDD(inp, dd);
            return;
        }

        list.forEach(function (c) {
            var row = document.createElement('div');
            // Minimum 48px yükseklik — parmakla rahat tıklanır
            row.style.cssText = [
                'padding:14px 16px',
                'border-bottom:1px solid #F3F4F6',
                'display:flex',
                'justify-content:space-between',
                'align-items:center',
                'min-height:48px',
                'cursor:pointer',
                '-webkit-tap-highlight-color:rgba(0,0,0,0.04)',
                'user-select:none',
            ].join(';');

            var l4 = last4(c.phone);
            row.innerHTML =
                '<span style="font-size:15px;font-weight:500;color:#111;">' + esc(c.name) + '</span>' +
                (l4 ? '<span style="font-size:12px;color:#9CA3AF;background:#F9FAFB;padding:2px 8px;border-radius:6px;flex-shrink:0;">···' + l4 + '</span>' : '');

            // Hover (masaüstü)
            row.addEventListener('mouseenter', function () { this.style.background = '#F9FAFB'; });
            row.addEventListener('mouseleave', function () { this.style.background = ''; });

            // Seçim — mobil + masaüstü
            var moved = false;
            row.addEventListener('touchstart', function () { moved = false; }, { passive: true });
            row.addEventListener('touchmove',  function () { moved = true;  }, { passive: true });
            row.addEventListener('touchend', function (e) {
                if (moved) return;
                e.preventDefault();
                onSelect(c);
            });
            row.addEventListener('mousedown', function (e) {
                e.preventDefault();
                onSelect(c);
            });

            dd.appendChild(row);
        });

        showDD(inp, dd);
    }

    // ── Dropdown'ı input'a bağla ──────────────────────────────────────────────
    function bindDD(inp, dd, onSelect) {
        if (!inp || !dd) return;

        var open = function () {
            renderDD(inp, dd, filter(inp.value), onSelect);
        };

        inp.addEventListener('focus', open);
        inp.addEventListener('input', open);
        inp.addEventListener('click', open);

        // iOS: focus gecikebilir, touchstart ile de tetikle
        inp.addEventListener('touchstart', function () {
            setTimeout(open, 80);
        }, { passive: true });

        // Scroll/resize → dropdown'ı yeniden konumlandır
        var repos = function () {
            if (dd.style.display !== 'none') positionDD(inp, dd);
        };
        window.addEventListener('scroll', repos, true);
        window.addEventListener('resize', repos);

        // Dışarı tıklayınca kapat
        document.addEventListener('mousedown', function (e) {
            if (!inp.contains(e.target) && !dd.contains(e.target)) hideDD(dd);
        });
        document.addEventListener('touchstart', function (e) {
            if (!inp.contains(e.target) && !dd.contains(e.target)) hideDD(dd);
        }, { passive: true });
    }

    // ── Tekil müşteri ─────────────────────────────────────────────────────────
    // Layout içeriği 2x render edilir (desktop hidden + mobile). getElementById
    // her zaman display:none olan desktop versiyonunu döndürür → BRC={0,0,0,0}.
    // Görünür (offsetWidth>0) olanı seç:
    var cInp = (function() {
        var all = document.querySelectorAll('#custSearch');
        for (var i = 0; i < all.length; i++) { if (all[i].offsetWidth > 0) return all[i]; }
        return all[0] || null;
    })();
    var cVal = (function() {
        var all = document.querySelectorAll('#customer_id_input');
        for (var i = 0; i < all.length; i++) { if (all[i].closest('form')) return all[i]; }
        return all[0] || null;
    })();

    bindDD(cInp, cDD, function (c) {
        var l4 = last4(c.phone);
        cInp.value  = c.name + (l4 ? ' (···' + l4 + ')' : '');
        cVal.value  = c.id;
        hideDD(cDD);
        cInp.blur(); // klavyeyi kapat
    });

    // Input değişince customer_id'yi sıfırla
    if (cInp) {
        cInp.addEventListener('input', function () { cVal.value = ''; });
    }

    // ── Grup müşteri ──────────────────────────────────────────────────────────
    var gInp = document.getElementById('groupCustSearch');
    // gDD yukarıda body'ye append edildi

    bindDD(gInp, gDD, function (c) {
        addGC(c.id, c.name, c.phone);
        gInp.value = '';
        hideDD(gDD);
        gInp.blur();
    });

    // ── Grup listesi ──────────────────────────────────────────────────────────
    function renderGC() {
        var el = document.getElementById('groupCustomerList');
        if (!el) return;
        if (!_GC.length) {
            el.innerHTML = '<p style="font-size:12px;color:#9CA3AF;font-style:italic;">Henüz katılımcı eklenmedi.</p>';
            return;
        }
        el.innerHTML = _GC.map(function (c) {
            var l4 = last4(c.phone);
            return '<div style="display:flex;justify-content:space-between;align-items:center;' +
                'padding:10px 12px;background:#fff;border:1px solid #E5E7EB;border-radius:8px;">' +
                '<span style="font-size:14px;"><strong>' + esc(c.name) + '</strong>' +
                (l4 ? ' <span style="color:#9CA3AF;font-size:12px;">···' + l4 + '</span>' : '') + '</span>' +
                '<input type="hidden" name="customer_ids[]" value="' + c.id + '">' +
                '<button type="button" onclick="window._removeGC(' + c.id + ')" ' +
                'style="color:#EF4444;font-size:12px;margin-left:12px;flex-shrink:0;' +
                'padding:4px 8px;border:1px solid #FEE2E2;border-radius:6px;background:#FEF2F2;">Kaldır</button>' +
                '</div>';
        }).join('');
    }

    function addGC(id, name, phone) {
        id = parseInt(id);
        if (_GC.find(function (c) { return c.id === id; })) return;
        _GC.push({ id: id, name: name, phone: phone || '' });
        renderGC();
    }

    window._removeGC = function (id) {
        _GC = _GC.filter(function (c) { return c.id !== parseInt(id); });
        renderGC();
    };

    window._addGC = addGC;
    window.removeGroupCustomer = window._removeGC;
    renderGC();

    // ── Toggle'lar ────────────────────────────────────────────────────────────
    var gChk = document.getElementById('isGroup');
    if (gChk) gChk.addEventListener('change', function () {
        var on = this.checked;
        document.getElementById('singleCustomerSection').classList.toggle('hidden', on);
        document.getElementById('groupSection').classList.toggle('hidden', !on);
        var r = document.getElementById('isRecurring');
        if (r) { r.checked = false; r.disabled = on; }
        var ro = document.getElementById('recurringOptions');
        if (ro) ro.classList.add('hidden');
    });

    var rChk = document.getElementById('isRecurring');
    if (rChk) rChk.addEventListener('change', function () {
        document.getElementById('recurringOptions').classList.toggle('hidden', !this.checked);
    });

    console.log('Lattessa: Müşteri arama hazır,', _AC.length, 'müşteri yüklendi.');
}); // DOMContentLoaded sonu
</script>
@endsection
