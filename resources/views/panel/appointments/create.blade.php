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
            {{-- position:relative → dropdown absolute olarak bu div'e göre konumlanır --}}
            <div style="position:relative;">
                <input type="text"
                       id="custSearch"
                       autocomplete="off"
                       inputmode="search"
                       placeholder="İsim veya son 4 hane telefon..."
                       class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm bg-white"
                       style="font-size:16px; position:relative; z-index:1;"
                       onfocus="window._openCust()"
                       onclick="window._openCust()"
                       oninput="window._openCust()">
                {{-- Dropdown burada — position:fixed değil, absolute → overflow/transform sorunlarından etkilenmez --}}
                <div id="_custDD"
                     style="display:none; position:absolute; top:calc(100% + 4px); left:0; right:0;
                            z-index:9999; background:#fff; border:1px solid #e5e7eb;
                            border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.14);
                            overflow-y:auto; -webkit-overflow-scrolling:touch; max-height:260px;"></div>
            </div>
            <button type="button" id="contactPickerBtn" onclick="window._pickContact()"
                    style="display:none; margin-top:8px; width:100%; padding:10px 14px;
                           border:1px dashed #d1d5db; border-radius:8px; background:#f9fafb;
                           color:#374151; font-size:13px; cursor:pointer; text-align:center;">
                📱 Rehberimden Ekle
            </button>
        </div>

        {{-- Contact picker: yeni müşteri modal --}}
        <div id="contactModal" style="display:none; position:fixed; inset:0; z-index:99999;
                                      background:rgba(0,0,0,.5); align-items:center; justify-content:center;">
            <div style="background:#fff; border-radius:16px; padding:24px; width:90%; max-width:360px; margin:auto;">
                <h3 style="font-size:16px; font-weight:600; margin:0 0 16px;">Yeni Müşteri Ekle</h3>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Ad Soyad</label>
                    <input id="contactModalName" type="text" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; outline:none; box-sizing:border-box;">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Telefon</label>
                    <input id="contactModalPhone" type="tel" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; outline:none; box-sizing:border-box;">
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="button" onclick="window._closeContactModal()"
                            style="flex:1; padding:10px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; background:#fff; cursor:pointer;">İptal</button>
                    <button type="button" id="contactSaveBtn" onclick="window._saveContact()"
                            style="flex:1; padding:10px; border:none; border-radius:8px; font-size:14px; background:#111; color:#fff; cursor:pointer; font-weight:500;">Kaydet</button>
                </div>
            </div>
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
                <div style="position:relative;">
                    <input type="text"
                           id="groupCustSearch"
                           autocomplete="off"
                           inputmode="search"
                           placeholder="Müşteri ekle..."
                           class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm bg-white"
                           style="font-size:16px; position:relative; z-index:1;">
                    <div id="_grpDD"
                         style="display:none; position:absolute; top:calc(100% + 4px); left:0; right:0;
                                z-index:9999; background:#fff; border:1px solid #e5e7eb;
                                border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.14);
                                overflow-y:auto; max-height:240px;"></div>
                </div>
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

<script>
// ── Müşteri verisi ────────────────────────────────────────────────────────────
window._AC = @json($customers->map(fn($c) => ['id'=>(int)$c->id,'name'=>(string)$c->name,'phone'=>(string)($c->phone??'')]));
window._GC = [];

// ── _posDD: position:absolute kullandığımız için konumlama gerekmez
function _posDD(inp, dd) { /* absolute, CSS tarafından konumlandırılıyor */ }

// ── Grup dropdown'ı oluştur (body'e ekle) — sadece grup için kullanılır
function _makeDD(id) {
    // custDD artık HTML'de tanımlandı; sadece grpDD için oluştur
    var existing = document.getElementById(id);
    if (existing) return existing;
    var d = document.createElement('div');
    d.id = id;
    d.style.cssText = [
        'display:none',
        'position:absolute',
        'top:calc(100% + 4px)',
        'left:0',
        'right:0',
        'z-index:9999',
        'background:#fff',
        'border:1px solid #e5e7eb',
        'border-radius:10px',
        'box-shadow:0 8px 24px rgba(0,0,0,.14)',
        'overflow-y:auto',
        '-webkit-overflow-scrolling:touch',
        'max-height:260px',
    ].join(';');
    return d;
}

// ── Müşteri listesi filtrele ──────────────────────────────────────────────────
function _filt(q) {
    q = (q || '').trim().toLowerCase();
    if (!q) return window._AC.slice(0, 60);
    return window._AC.filter(function(c) {
        var p = String(c.phone || '');
        return c.name.toLowerCase().indexOf(q) >= 0 || p.indexOf(q) >= 0 || p.slice(-4) === q;
    }).slice(0, 60);
}

// ── Dropdown içeriğini doldur ve göster ───────────────────────────────────────
function _fillDD(inp, dd, items, onPick) {
    dd.innerHTML = '';
    if (!items.length) {
        dd.innerHTML = '<div style="padding:14px;color:#9ca3af;font-size:14px;text-align:center;">Müşteri bulunamadı</div>';
    } else {
        items.forEach(function(c) {
            var row = document.createElement('div');
            var p4 = String(c.phone || '').slice(-4);
            row.style.cssText = 'padding:13px 16px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;min-height:48px;cursor:pointer;';
            row.innerHTML = '<span style="font-size:15px;font-weight:500;color:#111">' + c.name + '</span>' +
                (p4 ? '<span style="font-size:12px;color:#9ca3af;background:#f9fafb;padding:2px 8px;border-radius:6px">···' + p4 + '</span>' : '');

            // Seçim
            function pick(e) { e.preventDefault(); onPick(c); }
            row.addEventListener('mousedown', pick);
            row.addEventListener('touchend',  pick);

            row.addEventListener('mouseenter', function(){ this.style.background='#f9fafb'; });
            row.addEventListener('mouseleave', function(){ this.style.background=''; });
            dd.appendChild(row);
        });
    }
    _posDD(inp, dd);
    dd.style.display = 'block';
    console.log('DD gösterildi:', items.length, 'sonuç, top=' + dd.style.top);
}

// ── Tek müşteri arama ─────────────────────────────────────────────────────────

// ── Contact Picker ────────────────────────────────────────────────────────────
(function() {
    // Butonu her cihazda göster
    var _btn = document.getElementById('contactPickerBtn');
    var _hasContactPicker = ('contacts' in navigator && 'ContactsManager' in window);
    if (_btn) {
        _btn.style.display = 'block';
        _btn.textContent   = _hasContactPicker ? '📱 Rehberimden Ekle' : '➕ Yeni Müşteri Ekle';
    }

    window._pickContact = async function() {
        if (_hasContactPicker) {
            try {
                var list = await navigator.contacts.select(['name','tel'], {multiple:false});
                if (!list || !list.length) return;
                var c     = list[0];
                var name  = (c.name && c.name[0]) ? c.name[0].trim() : '';
                var phone = (c.tel  && c.tel[0])  ? c.tel[0].replace(/\s+/g,'').trim() : '';
                var p4    = phone.slice(-4);
                var found = p4 ? (window._AC||[]).find(function(ac){ return String(ac.phone||'').slice(-4)===p4; }) : null;
                if (found) {
                    var inp = document.querySelector('main.main-content #custSearch') || document.getElementById('custSearch');
                    if (inp) inp.value = found.name + ' (···' + String(found.phone).slice(-4) + ')';
                    document.querySelectorAll('#customer_id_input').forEach(function(el){ el.value = found.id; });
                    return;
                }
                document.getElementById('contactModalName').value  = name;
                document.getElementById('contactModalPhone').value = phone;
            } catch(e) { console.error('Contact picker:', e); }
        } else {
            document.getElementById('contactModalName').value  = '';
            document.getElementById('contactModalPhone').value = '';
        }
        document.getElementById('contactModal').style.display = 'flex';
    };

    window._closeContactModal = function() {
        document.getElementById('contactModal').style.display = 'none';
    };

    window._saveContact = async function() {
        var name  = document.getElementById('contactModalName').value.trim();
        var phone = document.getElementById('contactModalPhone').value.trim();
        if (!name || !phone) { alert('Ad ve telefon zorunludur.'); return; }
        var btn = document.getElementById('contactSaveBtn');
        if (btn) btn.textContent = 'Kaydediliyor...';
        try {
            var token = (document.querySelector('meta[name="csrf-token"]')||{}).content
                     || (document.querySelector('input[name="_token"]')||{}).value || '';
            var res  = await fetch('{{ route("panel.customers.quick-store", ["tenant_slug" => $tenant->slug]) }}', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
                body: JSON.stringify({name:name, phone:phone})
            });
            var data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Hata');
            window._AC = window._AC || [];
            window._AC.push({id:data.id, name:data.name, phone:data.phone||''});
            var inp = document.querySelector('main.main-content #custSearch') || document.getElementById('custSearch');
            var p4  = String(data.phone||'').slice(-4);
            if (inp) inp.value = data.name + (p4 ? ' (···'+p4+')' : '');
            document.querySelectorAll('#customer_id_input').forEach(function(el){ el.value = data.id; });
            window._closeContactModal();
        } catch(e) {
            alert('Hata: ' + e.message);
        } finally {
            if (btn) btn.textContent = 'Kaydet';
        }
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded: başlatılıyor...');

    // Dropdown'lar HTML'de tanımlı — sadece referans al
    var cDD  = document.getElementById('_custDD');
    var gDD  = document.getElementById('_grpDD');
    var cInp = document.getElementById('custSearch');
    var cVal = document.getElementById('customer_id_input');
    var gInp = document.getElementById('groupCustSearch');

    console.log('Elementler:', {cInp: !!cInp, cVal: !!cVal, gInp: !!gInp, cDD: !!cDD});

    // ── Tekil müşteri dropdown ─────────────────────────────────────────────────
    // ── Sabit dropdown (body'e eklenmiş, overflow clip edemez) ────────────────
    var _bodyDD = document.createElement('div');
    _bodyDD.id = '_bodyDD';
    _bodyDD.style.cssText = [
        'display:none',
        'position:fixed',
        'z-index:2147483647',
        'background:#fff',
        'border:1px solid #e5e7eb',
        'border-radius:12px',
        'box-shadow:0 8px 24px rgba(0,0,0,.16)',
        'overflow-y:auto',
        '-webkit-overflow-scrolling:touch',
        'font-family:inherit',
        'font-size:15px',
    ].join(';');
    document.body.appendChild(_bodyDD);

    // Panel layout içeriği iki kez render eder (desktop hidden + mobile visible).
    // getElementById her zaman ilk eşleşmeyi (display:none olan desktop) döndürür.
    // getBoundingClientRect() display:none eleman için {0,0,0,0} döner.
    // Bu yüzden görünür olanı seçiyoruz.
    function _visibleCustSearch() {
        var all = document.querySelectorAll('#custSearch');
        for (var i = 0; i < all.length; i++) {
            if (all[i].offsetWidth > 0) return all[i];
        }
        return all[0] || null;
    }

    // window._openCust — inline onfocus/onclick/oninput ile çağrılır
    window._openCust = function() {
        var inp = _visibleCustSearch();
        if (!inp) return;

        // Koordinatları şu an al (kullanıcı gerçekten input'a tıkladı)
        var r  = inp.getBoundingClientRect();
        var vh = window.innerHeight;
        var spaceBelow = vh - r.bottom - 8;
        var spaceAbove = r.top - 8;

        console.log('BRC: top=' + Math.round(r.top) + ' bottom=' + Math.round(r.bottom) + ' w=' + Math.round(r.width) + ' vh=' + vh);

        _bodyDD.style.left  = r.left + 'px';
        _bodyDD.style.width = r.width + 'px';

        if (spaceBelow >= 100 || spaceBelow >= spaceAbove) {
            _bodyDD.style.top    = (r.bottom + 4) + 'px';
            _bodyDD.style.bottom = 'auto';
            _bodyDD.style.maxHeight = Math.min(280, spaceBelow) + 'px';
        } else {
            _bodyDD.style.top    = 'auto';
            _bodyDD.style.bottom = (vh - r.top + 4) + 'px';
            _bodyDD.style.maxHeight = Math.min(280, spaceAbove) + 'px';
        }

        // Tüm customer_id_input alanlarını temizle
        document.querySelectorAll('#customer_id_input').forEach(function(el) { el.value = ''; });

        _fillDD(inp, _bodyDD, _filt(inp.value), function(c) {
            var p4 = String(c.phone || '').slice(-4);
            inp.value = c.name + (p4 ? ' (···' + p4 + ')' : '');
            // Tüm customer_id_input alanlarını güncelle (iki layout)
            document.querySelectorAll('#customer_id_input').forEach(function(el) { el.value = c.id; });
            _bodyDD.style.display = 'none';
        });
    };

    // Dışarı tıklayınca kapat
    document.addEventListener('mousedown', function(e) {
        var clickedAny = Array.from(document.querySelectorAll('#custSearch')).some(function(el) { return el.contains(e.target); });
        if (!clickedAny && !_bodyDD.contains(e.target)) {
            _bodyDD.style.display = 'none';
        }
    });
    document.addEventListener('touchstart', function(e) {
        var clickedAny = Array.from(document.querySelectorAll('#custSearch')).some(function(el) { return el.contains(e.target); });
        if (!clickedAny && !_bodyDD.contains(e.target)) {
            _bodyDD.style.display = 'none';
        }
    }, {passive:true});

    if (cInp) {
        console.log('_openCust hazır, bodyDD body\'e eklendi ✓');
    }

    // ── Grup müşteri dropdown ──────────────────────────────────────────────────
    var openG = function() {
        try {
            _fillDD(gInp, gDD, _filt(gInp.value), function(c) {
                window._addGC(c.id, c.name, c.phone);
                gInp.value = '';
                gDD.style.display = 'none';
            });
        } catch(e) {
            console.error('openG hata:', e);
        }
    };

    if (gInp) {
        gInp.addEventListener('focus',     openG);
        gInp.addEventListener('click',     openG);
        gInp.addEventListener('input',     openG);
        gInp.addEventListener('touchstart', function(){ setTimeout(openG, 50); }, {passive:true});
    }

    // Dışarı tıklayınca kapat
    document.addEventListener('mousedown', function(e) {
        if (cInp && !cInp.contains(e.target) && !cDD.contains(e.target)) cDD.style.display = 'none';
        if (gInp && !gInp.contains(e.target) && !gDD.contains(e.target)) gDD.style.display = 'none';
    });
    document.addEventListener('touchstart', function(e) {
        if (cInp && !cInp.contains(e.target) && !cDD.contains(e.target)) cDD.style.display = 'none';
        if (gInp && !gInp.contains(e.target) && !gDD.contains(e.target)) gDD.style.display = 'none';
    }, {passive:true});

    // ── Grup listesi ──────────────────────────────────────────────────────────
    function renderGC() {
        var el = document.getElementById('groupCustomerList');
        if (!el) return;
        if (!window._GC.length) {
            el.innerHTML = '<p style="font-size:12px;color:#9ca3af;font-style:italic">Henüz katılımcı eklenmedi.</p>';
            return;
        }
        el.innerHTML = window._GC.map(function(c) {
            var p4 = String(c.phone||'').slice(-4);
            return '<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#fff">' +
                '<span style="font-size:14px"><strong>' + c.name + '</strong>' + (p4?' <span style="color:#9ca3af;font-size:12px">···'+p4+'</span>':'') + '</span>' +
                '<input type="hidden" name="customer_ids[]" value="' + c.id + '">' +
                '<button type="button" onclick="window._rmGC(' + c.id + ')" style="color:#ef4444;font-size:12px;padding:2px 8px;border:1px solid #fee2e2;border-radius:6px;background:#fef2f2">Kaldır</button>' +
                '</div>';
        }).join('');
    }

    window._addGC = function(id, name, phone) {
        id = parseInt(id);
        if (window._GC.find(function(c){ return c.id===id; })) return;
        window._GC.push({id:id, name:String(name), phone:String(phone||'')});
        renderGC();
    };
    window._rmGC = function(id) {
        window._GC = window._GC.filter(function(c){ return c.id!==parseInt(id); });
        renderGC();
    };
    window.removeGroupCustomer = window._rmGC;
    renderGC();

    // ── Toggle'lar ────────────────────────────────────────────────────────────
    var gChk = document.getElementById('isGroup');
    if (gChk) gChk.addEventListener('change', function() {
        var on = this.checked;
        document.getElementById('singleCustomerSection').classList.toggle('hidden', on);
        document.getElementById('groupSection').classList.toggle('hidden', !on);
        var r = document.getElementById('isRecurring');
        if (r) { r.checked = false; r.disabled = on; }
        var ro = document.getElementById('recurringOptions');
        if (ro) ro.classList.add('hidden');
    });

    var rChk = document.getElementById('isRecurring');
    if (rChk) rChk.addEventListener('change', function() {
        document.getElementById('recurringOptions').classList.toggle('hidden', !this.checked);
    });

    console.log('Lattessa: hazır,', window._AC.length, 'müşteri');
});
</script>
@endsection
