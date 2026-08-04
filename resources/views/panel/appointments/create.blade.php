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
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
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
                <select name="branch_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                    <option value="">Şube seçin</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="border border-indigo-100 rounded-xl p-4 bg-indigo-50/40">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_group" value="1" id="isGroup" class="rounded border-gray-300 accent-indigo-600">
                <span class="text-sm font-medium text-gray-700">👥 Grup Randevusu</span>
            </label>
        </div>

        {{-- Tekil Müşteri --}}
        <div id="singleCustomerSection">
            <label class="block text-sm font-medium text-gray-700 mb-1">Müşteri</label>
            <input type="text" id="custSearch" autocomplete="off"
                   placeholder="İsim veya son 4 hane telefon..."
                   onfocus="custOpen()"
                   oninput="custOpen()"
                   onblur="setTimeout(custClose,200)"
                   style="width:100%;padding:10px 16px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;color:#111;background:#fff;box-sizing:border-box;outline:none;font-family:inherit;">
            <input type="hidden" name="customer_id" id="customer_id_input" value="{{ old('customer_id') }}">
        </div>

        {{-- Grup Müşteri --}}
        <div id="groupSection" class="hidden space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Grup Kapasitesi</label>
                <input type="number" name="group_capacity" min="1" max="500" value="10"
                       class="w-32 px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Katılımcılar</label>
                <div id="groupCustomerList" class="space-y-2 mb-2"></div>
                <input type="text" id="groupCustSearch" autocomplete="off"
                       placeholder="Müşteri ekle..."
                       onfocus="grpOpen()"
                       oninput="grpOpen()"
                       onblur="setTimeout(grpClose,200)"
                       style="width:100%;padding:10px 16px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;color:#111;background:#fff;box-sizing:border-box;outline:none;font-family:inherit;">
                <p class="text-xs text-gray-400 mt-1">Arama yaparak müşteri ekleyin.</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hizmet</label>
            <select name="service_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                <option value="">Hizmet seçin</option>
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
                <select name="staff_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                    <option value="">Personel seçin</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}" {{ old('staff_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
        @else
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
            <textarea name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                      placeholder="Randevuya ait notlar...">{{ old('notes') }}</textarea>
        </div>

        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
            <label class="flex items-center gap-2 cursor-pointer mb-3">
                <input type="checkbox" name="is_recurring" value="1" id="isRecurring" class="rounded border-gray-300">
                <span class="text-sm font-medium text-gray-700">Tekrarlayan Randevu</span>
            </label>
            <div id="recurringOptions" class="hidden space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tekrar Sıklığı</label>
                        <select name="recurrence_rule" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                            <option value="weekly">Haftalık</option>
                            <option value="biweekly">2 Haftada Bir</option>
                            <option value="monthly">Aylık</option>
                            <option value="daily">Günlük</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Kaç Kez</label>
                        <input type="number" name="recurrence_count" min="2" max="52" value="4"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                </div>
                <p class="text-xs text-gray-400">Çakışan saatler otomatik atlanır.</p>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Randevu Oluştur
            </button>
            <a href="{{ route('panel.appointments.index', ['tenant_slug' => $tenant->slug]) }}"
               class="px-6 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                İptal
            </a>
        </div>
    </form>
</div>

<script>
/* ── Müşteri verisi ── */
var _AC = @json($customers->map(fn($c) => ['id'=>$c->id,'name'=>$c->name,'phone'=>$c->phone??'']));

function _esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function _filter(q){
    q = (q||'').trim().toLowerCase();
    if(!q) return _AC.slice(0,50);
    return _AC.filter(function(c){
        var p = c.phone ? c.phone.toString() : '';
        return c.name.toLowerCase().includes(q) || p.includes(q) || p.slice(-4) === q;
    }).slice(0,50);
}

/* ── Dropdown yardımcı ── */
function _getDD(id){
    var d = document.getElementById(id);
    if(!d){
        d = document.createElement('div');
        d.id = id;
        /* Tüm stiller inline — CSS bağımlılığı yok */
        d.style.position   = 'fixed';
        d.style.zIndex     = '2147483647';
        d.style.background = '#fff';
        d.style.border     = '1px solid #e5e7eb';
        d.style.borderRadius = '10px';
        d.style.boxShadow  = '0 8px 24px rgba(0,0,0,.14)';
        d.style.overflowY  = 'auto';
        d.style.webkitOverflowScrolling = 'touch';
        d.style.display    = 'none';
        document.body.appendChild(d);
    }
    return d;
}

function _placeDD(dd, anchor){
    var r  = anchor.getBoundingClientRect();
    var vh = window.innerHeight;
    dd.style.left  = r.left + 'px';
    dd.style.width = r.width + 'px';
    if(vh - r.bottom >= 120){
        dd.style.top       = (r.bottom + 2) + 'px';
        dd.style.bottom    = 'auto';
        dd.style.maxHeight = Math.min(260, vh - r.bottom - 8) + 'px';
    } else {
        dd.style.top       = 'auto';
        dd.style.bottom    = (vh - r.top + 2) + 'px';
        dd.style.maxHeight = Math.min(260, r.top - 8) + 'px';
    }
}

function _fillDD(dd, list, onPick){
    dd.innerHTML = '';
    if(!list.length){
        var empty = document.createElement('div');
        empty.style.cssText = 'padding:16px;text-align:center;color:#9ca3af;font-size:14px;';
        empty.textContent = 'Müşteri bulunamadı';
        dd.appendChild(empty);
        return;
    }
    list.forEach(function(c){
        var row = document.createElement('div');
        row.style.cssText = 'padding:13px 16px;border-bottom:1px solid #f3f4f6;font-size:14px;color:#111;cursor:pointer;background:#fff;-webkit-tap-highlight-color:rgba(0,0,0,.06);';
        var last4 = c.phone ? c.phone.toString().slice(-4) : '';
        row.innerHTML = '<strong>'+_esc(c.name)+'</strong>'+(last4?' <span style="color:#9ca3af;font-size:12px;">···'+last4+'</span>':'');
        /* onclick: onblur'dan sonra tetiklenir → 200ms timeout yeterli */
        row.onclick = function(){ onPick(c); };
        dd.appendChild(row);
    });
}

/* ── TEKİL MÜŞTERİ — global fonksiyonlar (onfocus/oninput için) ── */
function custOpen(){
    var inp = document.getElementById('custSearch');
    var dd  = _getDD('_custDD');
    document.getElementById('customer_id_input').value = '';
    _fillDD(dd, _filter(inp.value), function(c){
        var last4 = c.phone ? c.phone.toString().slice(-4) : '';
        inp.value = c.name + (last4 ? ' (···'+last4+')' : '');
        document.getElementById('customer_id_input').value = c.id;
        custClose();
    });
    _placeDD(dd, inp);
    dd.style.display = 'block';
}

function custClose(){
    var dd = document.getElementById('_custDD');
    if(dd) dd.style.display = 'none';
}

/* ── GRUP MÜŞTERİ ── */
var _GC = [];

function grpOpen(){
    var inp = document.getElementById('groupCustSearch');
    var dd  = _getDD('_grpDD');
    _fillDD(dd, _filter(inp.value), function(c){
        _addGC(parseInt(c.id), c.name, String(c.phone||''));
        inp.value = '';
        grpClose();
    });
    _placeDD(dd, inp);
    dd.style.display = 'block';
}

function grpClose(){
    var dd = document.getElementById('_grpDD');
    if(dd) dd.style.display = 'none';
}

function _addGC(id, name, phone){
    if(_GC.find(function(c){ return c.id==id; })) return;
    _GC.push({id:id,name:name,phone:phone});
    _renderGC();
}

function removeGroupCustomer(id){
    _GC = _GC.filter(function(c){ return c.id!=id; });
    _renderGC();
}

function _renderGC(){
    var el = document.getElementById('groupCustomerList');
    if(!el) return;
    if(!_GC.length){ el.innerHTML='<p class="text-xs text-gray-400 italic">Henüz katılımcı eklenmedi.</p>'; return; }
    el.innerHTML = _GC.map(function(c){
        var last4 = c.phone ? c.phone.toString().slice(-4) : '';
        return '<div class="flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm">'
            +'<span><strong>'+_esc(c.name)+'</strong>'+(last4?' <span class="text-gray-400 text-xs">···'+last4+'</span>':'')+'</span>'
            +'<input type="hidden" name="customer_ids[]" value="'+c.id+'">'
            +'<button type="button" onclick="removeGroupCustomer('+c.id+')" class="text-red-400 hover:text-red-600 text-xs ml-2">✕</button>'
            +'</div>';
    }).join('');
}

/* ── Toggle'lar ── */
(function(){
    _renderGC();
    var g = document.getElementById('isGroup');
    if(g) g.addEventListener('change', function(){
        var on = this.checked;
        document.getElementById('singleCustomerSection').classList.toggle('hidden', on);
        document.getElementById('groupSection').classList.toggle('hidden', !on);
        var r = document.getElementById('isRecurring');
        if(r){ r.checked=false; r.disabled=on; }
        var ro = document.getElementById('recurringOptions');
        if(ro) ro.classList.add('hidden');
    });
    var r = document.getElementById('isRecurring');
    if(r) r.addEventListener('change', function(){
        document.getElementById('recurringOptions').classList.toggle('hidden', !this.checked);
    });
})();
</script>
@endsection
