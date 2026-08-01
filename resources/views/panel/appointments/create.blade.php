@extends('layouts.panel')

@section('title', 'Yeni Randevu')

@section('content')
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
        {{-- Şubesi belli olan personel: gizli input --}}
        <input type="hidden" name="branch_id" value="{{ old('branch_id', $userBranchId) }}">
        <div class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
            🏢 {{ $branches->firstWhere('id', $userBranchId)?->name ?? 'Şube' }}
        </div>
        @else
        {{-- Firma sahibi: şube seçebilir --}}
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
                       onchange="toggleGroupMode()"
                       class="rounded border-gray-300 accent-indigo-600">
                <span class="text-sm font-medium text-gray-700">👥 Grup Randevusu</span>
            </label>
        </div>

        {{-- Tekil Müşteri (grup değilken görünür) --}}
        <div id="singleCustomerSection">
            <label class="block text-sm font-medium text-gray-700 mb-1">Müşteri</label>
            <input type="text" id="customerSearch" readonly autocomplete="off"
                   placeholder="İsim veya son 4 hane telefon..."
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                   style="cursor:pointer;"
                   onclick="openCustModal('single')" ontouchend="openCustModal('single')">
            <input type="hidden" name="customer_id" id="customer_id_input" value="{{ old('customer_id') }}">
        </div>

        {{-- Grup Müşteri Listesi (grup modunda görünür) --}}
        <div id="groupSection" class="hidden space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Grup Kapasitesi (max katılımcı)</label>
                <input type="number" name="group_capacity" min="1" max="500" value="10"
                       class="w-32 px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Katılımcılar</label>
                <div id="groupCustomerList" class="space-y-2 mb-2"></div>
                {{-- Müşteri arama (grup) --}}
                <input type="text" id="groupSearch" readonly autocomplete="off"
                       placeholder="Müşteri ekle..."
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm"
                       style="cursor:pointer;"
                       onclick="openCustModal('group')" ontouchend="openCustModal('group')">
                <p class="text-xs text-gray-400 mt-1">Arama yaparak müşteri ekleyin. Her müşteri listeye eklenir.</p>
            </div>
        </div>

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
        {{-- Diğer personel: sadece kendisi --}}
        <input type="hidden" name="staff_id" value="{{ $authUser->id }}">
        <div class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
            👤 {{ $authUser->name }}
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tarih ve Saat</label>
            <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required
                   min="{{ now()->format('Y-m-d\TH:i') }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
        </div>

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
                       onchange="toggleRecurring()"
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

{{-- Müşteri Arama Modalı --}}
<div id="custSearchModal" style="display:none;position:fixed;inset:0;z-index:99999;">
    {{-- Arka plan overlay --}}
    <div onclick="closeCustModal()" style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
    {{-- Bottom sheet --}}
    <div style="position:absolute;bottom:0;left:0;right:0;background:#fff;border-radius:16px 16px 0 0;max-height:80vh;display:flex;flex-direction:column;">
        {{-- Handle --}}
        <div style="display:flex;justify-content:center;padding:10px 0 4px;">
            <div style="width:40px;height:4px;background:#d1d5db;border-radius:2px;"></div>
        </div>
        {{-- Search bar --}}
        <div style="display:flex;align-items:center;gap:10px;padding:8px 14px 12px;border-bottom:1px solid #f3f4f6;">
            <input id="custModalInput" type="text" autocomplete="off"
                   placeholder="İsim veya son 4 hane telefon..."
                   style="flex:1;padding:10px 14px;border:1px solid #d1d5db;border-radius:10px;font-size:16px;outline:none;font-family:inherit;">
            <button type="button" onclick="closeCustModal()" style="color:#6b7280;background:none;border:none;font-size:20px;padding:4px 6px;cursor:pointer;">✕</button>
        </div>
        {{-- Sonuçlar --}}
        <div id="custModalResults" style="overflow-y:auto;-webkit-overflow-scrolling:touch;flex:1;"></div>
    </div>
</div>


<script>
function toggleRecurring() {
    const cb = document.getElementById('isRecurring');
    const opts = document.getElementById('recurringOptions');
    opts.classList.toggle('hidden', !cb.checked);
}

// ---- GRUP RANDEVUSU ----
var groupCustomers = [];

function toggleGroupMode() {
    const isGroup = document.getElementById('isGroup').checked;
    document.getElementById('singleCustomerSection').classList.toggle('hidden', isGroup);
    document.getElementById('groupSection').classList.toggle('hidden', !isGroup);

    var singleInput = document.getElementById('customer_id_input');
    if (singleInput) singleInput.required = !isGroup;

    if (isGroup) {
        document.getElementById('isRecurring').checked = false;
        document.getElementById('recurringOptions').classList.add('hidden');
        document.getElementById('isRecurring').disabled = true;
    } else {
        document.getElementById('isRecurring').disabled = false;
    }
}

function addGroupCustomer(id, name, phone) {
    if (groupCustomers.find(function(c){ return c.id == id; })) return;
    groupCustomers.push({id: id, name: name, phone: phone});
    renderGroupCustomers();
}

function removeGroupCustomer(id) {
    groupCustomers = groupCustomers.filter(function(c){ return c.id != id; });
    renderGroupCustomers();
}

function renderGroupCustomers() {
    var list = document.getElementById('groupCustomerList');
    if (groupCustomers.length === 0) {
        list.innerHTML = '<p class="text-xs text-gray-400 italic">Henüz katılımcı eklenmedi.</p>';
    } else {
        list.innerHTML = groupCustomers.map(function(c) {
            var last4 = c.phone ? c.phone.toString().slice(-4) : '';
            return '<div class="flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm">' +
                '<span><strong>' + escHtml(c.name) + '</strong> <span class="text-gray-400 text-xs">···' + last4 + '</span></span>' +
                '<input type="hidden" name="customer_ids[]" value="' + c.id + '">' +
                '<button type="button" onclick="removeGroupCustomer(' + c.id + ')" class="text-red-400 hover:text-red-600 text-xs ml-2">✕</button>' +
                '</div>';
        }).join('');
    }
}

// ── Müşteri Arama ──────────────────────────────────────────────────────────
var allCustomers = @json($customers->map(fn($c) => ['id'=>$c->id,'name'=>$c->name,'phone'=>$c->phone ?? '']));
var _custModalMode = 'single';

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function filterC(q) {
    q = (q||'').trim();
    if (!q) return allCustomers.slice(0,60);
    if (/^\d+$/.test(q)) return allCustomers.filter(function(c){ return c.phone && (c.phone.toString().includes(q)||c.phone.toString().slice(-4)===q.slice(-4)); });
    var ql = q.toLowerCase();
    return allCustomers.filter(function(c){ return c.name.toLowerCase().includes(ql)||(c.phone&&c.phone.toString().includes(q)); });
}

function openCustModal(mode) {
    _custModalMode = mode || 'single';
    document.getElementById('custSearchModal').style.display = 'block';
    var inp = document.getElementById('custModalInput');
    inp.value = '';
    renderCustModalResults('');
    setTimeout(function(){ inp.focus(); }, 150);
}

function closeCustModal() {
    document.getElementById('custSearchModal').style.display = 'none';
}

function renderCustModalResults(q) {
    var results = filterC(q);
    var html = '';
    if (!results.length) {
        html = '<div style="padding:20px;text-align:center;color:#9ca3af;font-size:14px;">Müşteri bulunamadı</div>';
    } else {
        html = results.slice(0,100).map(function(c){
            var last4 = c.phone ? c.phone.toString().slice(-4) : '';
            return '<div style="padding:16px 18px;border-bottom:1px solid #f3f4f6;font-size:15px;cursor:pointer;-webkit-tap-highlight-color:rgba(0,0,0,0.05);" onclick="pickCustModal('+c.id+',\''+c.name.replace(/'/g,"\\'")+'\',' +'\''+String(c.phone||'').replace(/'/g,"\\'")+'\''+')">'
                + '<strong style="color:#111;">'+escHtml(c.name)+'</strong> '
                + '<span style="color:#9ca3af;font-size:13px;">···'+last4+'</span>'
                + '</div>';
        }).join('');
    }
    document.getElementById('custModalResults').innerHTML = html;
}

function pickCustModal(id, name, phone) {
    if (_custModalMode === 'group') {
        addGroupCustomer(parseInt(id), name, phone);
        document.getElementById('custModalInput').value = '';
        renderCustModalResults('');
    } else {
        document.getElementById('customer_id_input').value = id;
        var last4 = phone ? phone.toString().slice(-4) : '';
        document.getElementById('customerSearch').value = name + (last4 ? ' (···'+last4+')' : '');
        closeCustModal();
    }
}

// Modal'ı body'ye taşı → iOS overflow:auto içinde position:fixed sorunu çözülür
document.body.appendChild(document.getElementById('custSearchModal'));

document.getElementById('custModalInput').addEventListener('input', function(){
    renderCustModalResults(this.value);
});

// Desktop dropdown
var isDesktop = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
if (isDesktop) {
    var _csInp = document.getElementById('customerSearch');
    var _gsInp = document.getElementById('groupSearch');

    // Desktop'ta readonly kaldır, yazılabilir yap
    [_csInp, _gsInp].forEach(function(el){ if(el){ el.readOnly=false; el.style.cursor=''; el.onclick=null; el.ontouchend=null; } });

    // Dropdown div
    var _dd = document.createElement('div');
    _dd.style.cssText = 'display:none;position:fixed;z-index:9999;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,0.12);max-height:300px;overflow-y:auto;min-width:200px;';
    document.body.appendChild(_dd);
    var _ddt = null;

    function _showDd(el, mode) {
        var results = filterC(el.value);
        var r = el.getBoundingClientRect();
        _dd.style.left=r.left+'px'; _dd.style.top=(r.bottom+4)+'px'; _dd.style.width=r.width+'px';
        _dd.innerHTML = !results.length
            ? '<div style="padding:12px 16px;color:#9ca3af;font-size:14px;">Müşteri bulunamadı</div>'
            : results.slice(0,60).map(function(c){
                var l4=c.phone?c.phone.toString().slice(-4):'';
                return '<div style="padding:10px 16px;border-bottom:1px solid #f3f4f6;font-size:14px;cursor:pointer;" '
                    +'onmousedown="_ddPick(\''+mode+'\','+c.id+',\''+c.name.replace(/'/g,"\\'")+'\',' +'\''+String(c.phone||'').replace(/'/g,"\\'")+'\''+')">'
                    +'<strong>'+escHtml(c.name)+'</strong> <span style="color:#9ca3af;font-size:12px;">···'+l4+'</span></div>';
            }).join('');
        _dd.style.display='block';
    }

    window._ddPick = function(mode, id, name, phone) {
        _dd.style.display='none';
        var l4=phone?phone.toString().slice(-4):'';
        if(mode==='group'){ addGroupCustomer(parseInt(id),name,phone); if(_gsInp)_gsInp.value=''; }
        else { document.getElementById('customer_id_input').value=id; if(_csInp)_csInp.value=name+(l4?' (···'+l4+')':''); }
    };

    function _setupDd(el, mode) {
        if(!el) return;
        el.addEventListener('focus', function(){ clearTimeout(_ddt); _showDd(el,mode); });
        el.addEventListener('input', function(){ clearTimeout(_ddt); if(mode==='single') document.getElementById('customer_id_input').value=''; _showDd(el,mode); });
        el.addEventListener('blur', function(){ _ddt=setTimeout(function(){ _dd.style.display='none'; },200); });
    }
    _setupDd(_csInp,'single');
    _setupDd(_gsInp,'group');
}

(function(){
    var oldId = {{ old('customer_id', 'null') }};
    if (oldId) {
        var c = allCustomers.find(function(x){ return x.id == oldId; });
        if (c) {
            document.getElementById('customer_id_input').value = c.id;
            var l4 = c.phone ? c.phone.slice(-4) : '';
            document.getElementById('customerSearch').value = c.name + (l4 ? ' (···'+l4+')' : '');
        }
    }
    renderGroupCustomers();
})();
</script>
@endsection
