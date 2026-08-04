@extends('layouts.panel')

@section('title', 'Yeni Randevu')

@section('content')

<style>
#_custDD, #_grpDD {
    position: fixed;
    z-index: 2147483647;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.14);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    display: none;
}
#_custDD .cr, #_grpDD .cr {
    padding: 13px 16px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 14px;
    color: #111;
    cursor: pointer;
    background: #fff;
    -webkit-tap-highlight-color: rgba(0,0,0,.06);
    touch-action: manipulation;
    user-select: none;
    -webkit-user-select: none;
}
#_custDD .cr:last-child, #_grpDD .cr:last-child { border-bottom: none; }
#_custDD .cr:active, #_grpDD .cr:active { background: #f9fafb; }
.cr-empty { padding: 16px; text-align: center; color: #9ca3af; font-size: 14px; }
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
                       style="width:100%;padding:10px 16px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;color:#111;background:#fff;box-sizing:border-box;outline:none;font-family:inherit;">
                <p class="text-xs text-gray-400 mt-1">Arama yaparak müşteri ekleyin.</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hizmet</label>
            <select name="service_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
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
/* ─── VERİ ─────────────────────────────────────────────── */
var allCustomers = @json($customers->map(fn($c) => ['id'=>$c->id,'name'=>$c->name,'phone'=>$c->phone??'']));

function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function filter(q,n){
    q=(q||'').trim().toLowerCase(); n=n||50;
    if(!q) return allCustomers.slice(0,n);
    return allCustomers.filter(function(c){
        var p=c.phone?c.phone.toString():'';
        return c.name.toLowerCase().includes(q)||p.includes(q)||p.slice(-4)===q;
    }).slice(0,n);
}

/* ─── DROPDOWN (position:fixed, body'de) ───────────────── */
function makeDD(id){
    var d=document.getElementById(id);
    if(!d){ d=document.createElement('div'); d.id=id; document.body.appendChild(d); }
    return d;
}

function placeDD(dd, anchor){
    var r   = anchor.getBoundingClientRect();
    var vh  = window.innerHeight;
    var bot = vh - r.bottom;   /* boşluk: input altı   */
    var top = r.top;           /* boşluk: input üstü   */
    var mh  = 260;

    dd.style.left  = r.left  + 'px';
    dd.style.width = r.width + 'px';

    if(bot >= 120 || bot >= top){          /* alta aç */
        dd.style.top    = (r.bottom + 2) + 'px';
        dd.style.bottom = 'auto';
        dd.style.maxHeight = Math.min(mh, bot - 8) + 'px';
    } else {                               /* üste aç */
        dd.style.top    = 'auto';
        dd.style.bottom = (vh - r.top + 2) + 'px';
        dd.style.maxHeight = Math.min(mh, top - 8) + 'px';
    }
}

function fillDD(dd, list, onPick){
    dd.innerHTML = '';
    if(!list.length){
        dd.innerHTML='<div class="cr-empty">Müşteri bulunamadı</div>';
        return;
    }
    list.forEach(function(c){
        var last4 = c.phone ? c.phone.toString().slice(-4) : '';
        var row   = document.createElement('div');
        row.className = 'cr';
        row.innerHTML = '<strong>'+esc(c.name)+'</strong>'+(last4?' <span style="color:#9ca3af;font-size:12px;">···'+last4+'</span>':'');
        row.addEventListener('pointerdown', function(e){ e.preventDefault(); onPick(c); });
        row.addEventListener('touchend',    function(e){ e.preventDefault(); onPick(c); },{passive:false});
        dd.appendChild(row);
    });
}

function showDD(dd, anchor, list, onPick){
    fillDD(dd, list, onPick);
    placeDD(dd, anchor);
    dd.style.display = 'block';
}

function hideDD(dd){ dd.style.display = 'none'; }

/* Herhangi bir dış tıklamada tüm dd'leri kapat */
function outsideClose(anchor, dd){
    function close(e){
        if(!anchor.contains(e.target) && !dd.contains(e.target)) hideDD(dd);
    }
    document.addEventListener('pointerdown', close);
    document.addEventListener('touchstart',  close, {passive:true});
}

/* ─── TEKİL MÜŞTERİ ────────────────────────────────────── */
(function(){
    var inp    = document.getElementById('custSearch');
    var hidden = document.getElementById('customer_id_input');
    if(!inp) return;
    var dd = makeDD('_custDD');

    function open(q){
        hidden.value = '';
        showDD(dd, inp, filter(q), function(c){
            var last4=c.phone?c.phone.toString().slice(-4):'';
            inp.value    = c.name+(last4?' (···'+last4+')':'');
            hidden.value = c.id;
            hideDD(dd);
            inp.blur();
        });
    }

    inp.addEventListener('focus', function(){ open(inp.value); });
    inp.addEventListener('click', function(){ open(inp.value); }); /* ekstra güvenlik */
    inp.addEventListener('input', function(){ open(inp.value); });
    outsideClose(inp, dd);
})();

/* ─── GRUP MÜŞTERİ ─────────────────────────────────────── */
var groupCustomers = [];

(function(){
    var inp = document.getElementById('groupCustSearch');
    if(!inp) return;
    var dd  = makeDD('_grpDD');

    function open(q){
        showDD(dd, inp, filter(q), function(c){
            addGC(parseInt(c.id), c.name, String(c.phone||''));
            inp.value = '';
            hideDD(dd);
        });
    }

    inp.addEventListener('focus', function(){ open(inp.value); });
    inp.addEventListener('click', function(){ open(inp.value); });
    inp.addEventListener('input', function(){ open(inp.value); });
    outsideClose(inp, dd);
})();

function addGC(id, name, phone){
    if(groupCustomers.find(function(c){ return c.id==id; })) return;
    groupCustomers.push({id:id,name:name,phone:phone});
    renderGC();
}
function removeGroupCustomer(id){
    groupCustomers=groupCustomers.filter(function(c){ return c.id!=id; });
    renderGC();
}
function renderGC(){
    var el=document.getElementById('groupCustomerList'); if(!el) return;
    if(!groupCustomers.length){ el.innerHTML='<p class="text-xs text-gray-400 italic">Henüz katılımcı eklenmedi.</p>'; return; }
    el.innerHTML=groupCustomers.map(function(c){
        var last4=c.phone?c.phone.toString().slice(-4):'';
        return '<div class="flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm">'
            +'<span><strong>'+esc(c.name)+'</strong>'+(last4?' <span class="text-gray-400 text-xs">···'+last4+'</span>':'')+'</span>'
            +'<input type="hidden" name="customer_ids[]" value="'+c.id+'">'
            +'<button type="button" onclick="removeGroupCustomer('+c.id+')" class="text-red-400 hover:text-red-600 text-xs ml-2">✕</button>'
            +'</div>';
    }).join('');
}

/* ─── TOGGLE'LAR ────────────────────────────────────────── */
(function(){
    renderGC();
    var g=document.getElementById('isGroup');
    if(g) g.addEventListener('change',function(){
        var on=this.checked;
        document.getElementById('singleCustomerSection').classList.toggle('hidden',on);
        document.getElementById('groupSection').classList.toggle('hidden',!on);
        var r=document.getElementById('isRecurring');
        if(r){ r.checked=false; r.disabled=on; }
        var ro=document.getElementById('recurringOptions');
        if(ro) ro.classList.add('hidden');
    });
    var r=document.getElementById('isRecurring');
    if(r) r.addEventListener('change',function(){
        document.getElementById('recurringOptions').classList.toggle('hidden',!this.checked);
    });
})();
</script>
@endsection
