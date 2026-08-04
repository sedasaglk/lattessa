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
        <div id="singleCustomerSection" class="relative z-20">
            <label class="block text-sm font-medium text-gray-700 mb-1">Müşteri</label>
            <div class="relative">
                <input type="text"
                       id="custSearch"
                       autocomplete="off"
                       placeholder="İsim veya son 4 hane telefon..."
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm relative z-10 bg-white"
                       style="-webkit-user-select: text; user-select: text;">
                <input type="hidden" name="customer_id" id="customer_id_input" value="{{ old('customer_id') }}">

                {{-- Dropdown Container --}}
                <div id="_custDD" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto"></div>
            </div>
        </div>
        {{-- Grup Müşteri --}}
        <div id="groupSection" class="hidden space-y-3 relative z-20">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Grup Kapasitesi</label>
                <input type="number" name="group_capacity" min="1" max="500" value="10"
                       class="w-32 px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
            </div>
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Katılımcılar</label>
                <div id="groupCustomerList" class="space-y-2 mb-2"></div>
                <input type="text"
                       id="groupCustSearch"
                       autocomplete="off"
                       placeholder="Müşteri ekle..."
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm relative z-10 bg-white"
                       style="-webkit-user-select: text; user-select: text;">

                {{-- Dropdown Container --}}
                <div id="_grpDD" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto"></div>
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
document.addEventListener('DOMContentLoaded', function () {
    var _AC = @json($customers->map(fn($c) => ['id'=>$c->id,'name'=>$c->name,'phone'=>$c->phone??'']));
    var _GC = [];
    function _esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function _filter(q){
        q = (q||'').trim().toLowerCase();
        if(!q) return _AC.slice(0,50);
        return _AC.filter(function(c){
            var p = c.phone ? c.phone.toString() : '';
            return c.name.toLowerCase().includes(q) || p.includes(q) || p.slice(-4) === q;
        }).slice(0,50);
    }
    function _renderList(container, list, onSelect) {
        container.innerHTML = '';
        if(!list.length){
            container.innerHTML = '<div class="p-3 text-center text-gray-400 text-sm">Müşteri bulunamadı</div>';
            container.classList.remove('hidden');
            return;
        }
        list.forEach(function(c){
            var row = document.createElement('div');
            row.className = 'px-4 py-3 border-b border-gray-100 text-sm text-gray-900 cursor-pointer active:bg-gray-100 hover:bg-gray-50 flex justify-between items-center';
            var last4 = c.phone ? c.phone.toString().slice(-4) : '';
            row.innerHTML = '<span><strong>'+_esc(c.name)+'</strong></span>' + (last4 ? '<span class="text-xs text-gray-400">···'+last4+'</span>' : '');

            // Event Listener bağlama (Mobil uyumlu)
            row.addEventListener('pointerdown', function(e){
                e.preventDefault();
                onSelect(c);
            });
            container.appendChild(row);
        });
        container.classList.remove('hidden');
    }
    // Tekil Müşteri Elemanları
    var cInp = document.getElementById('custSearch');
    var cDD = document.getElementById('_custDD');
    var cVal = document.getElementById('customer_id_input');
    if(cInp){
        ['focus', 'input', 'click', 'touchstart'].forEach(function(evtName){
            cInp.addEventListener(evtName, function(e){
                cVal.value = '';
                var res = _filter(cInp.value);
                _renderList(cDD, res, function(selected){
                    var last4 = selected.phone ? selected.phone.toString().slice(-4) : '';
                    cInp.value = selected.name + (last4 ? ' (···'+last4+')' : '');
                    cVal.value = selected.id;
                    cDD.classList.add('hidden');
                });
            });
        });
    }
    // Grup Müşteri Elemanları
    var gInp = document.getElementById('groupCustSearch');
    var gDD = document.getElementById('_grpDD');
    if(gInp){
        ['focus', 'input', 'click', 'touchstart'].forEach(function(evtName){
            gInp.addEventListener(evtName, function(e){
                var res = _filter(gInp.value);
                _renderList(gDD, res, function(selected){
                    _addGC(parseInt(selected.id), selected.name, String(selected.phone||''));
                    gInp.value = '';
                    gDD.classList.add('hidden');
                });
            });
        });
    }
    // Dışarı tıklayınca kapatma
    document.addEventListener('pointerdown', function(e){
        if(cDD && !cInp.contains(e.target) && !cDD.contains(e.target)) cDD.classList.add('hidden');
        if(gDD && !gInp.contains(e.target) && !gDD.contains(e.target)) gDD.classList.add('hidden');
    });

    // Grup müşteri fonksiyonları
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
    window._addGC = function(id, name, phone){
        if(_GC.find(function(c){ return c.id==id; })) return;
        _GC.push({id:id,name:name,phone:phone});
        _renderGC();
    };
    window.removeGroupCustomer = function(id){
        _GC = _GC.filter(function(c){ return c.id!=id; });
        _renderGC();
    };
    _renderGC();

    // Toggle'lar
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
});
</script>
@endsection
