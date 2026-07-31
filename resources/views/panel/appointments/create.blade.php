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
            <div class="relative cust-search-wrap">
                <input type="text" autocomplete="off"
                       placeholder="İsim veya son 4 hane telefon..."
                       class="cust-search-input w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                <input type="hidden" name="customer_id" class="cust-id-input" value="{{ old('customer_id') }}">
                <div class="cust-dropdown absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-xl hidden"
                     style="max-height:220px; overflow-y:auto; top:calc(100% + 2px); left:0;">
                </div>
            </div>
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
                <div class="relative cust-search-wrap group-cust-wrap">
                    <input type="text" autocomplete="off"
                           placeholder="Müşteri ekle..."
                           class="cust-search-input w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                    <div class="cust-dropdown absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-xl hidden"
                         style="max-height:220px; overflow-y:auto; top:calc(100% + 2px); left:0;">
                    </div>
                </div>
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

<script>
function toggleRecurring() {
    const cb = document.getElementById('isRecurring');
    const opts = document.getElementById('recurringOptions');
    opts.classList.toggle('hidden', !cb.checked);
}

// ---- GRUP RANDEVUSU ----
var groupCustomers = []; // {id, name, phone}

function toggleGroupMode() {
    const isGroup = document.getElementById('isGroup').checked;
    document.getElementById('singleCustomerSection').classList.toggle('hidden', isGroup);
    document.getElementById('groupSection').classList.toggle('hidden', !isGroup);

    // customer_id zorunluluğunu değiştir
    var singleInput = document.querySelector('#singleCustomerSection .cust-id-input');
    singleInput.required = !isGroup;

    // Tekrarlayan ile birlikte kullanılamaz
    if (isGroup) {
        document.getElementById('isRecurring').checked = false;
        document.getElementById('recurringOptions').classList.add('hidden');
        document.getElementById('isRecurring').disabled = true;
    } else {
        document.getElementById('isRecurring').disabled = false;
    }
}

function addGroupCustomer(id, name, phone) {
    if (groupCustomers.find(function(c){ return c.id == id; })) return; // zaten ekli
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

// Müşteri arama - her wrap bağımsız çalışır (double-render fix)
var allCustomers = @json($customers->map(fn($c) => ['id'=>$c->id,'name'=>$c->name,'phone'=>$c->phone]));

function escHtml(s) { return String(s).replace(/'/g,"&#39;").replace(/"/g,'&quot;'); }

function initCustomerSearch(wrap) {
    var input = wrap.querySelector('.cust-search-input');
    var dd = wrap.querySelector('.cust-dropdown');
    var hidden = wrap.querySelector('.cust-id-input');

    function doSearch(q) {
        q = (q || '').trim();
        var results;
        if (q.length === 0) {
            results = allCustomers.slice(0, 50);
        } else if (/^\d{3,4}$/.test(q)) {
            results = allCustomers.filter(function(c){ return c.phone && c.phone.toString().slice(-4) === q.slice(-4); });
        } else {
            var ql = q.toLowerCase();
            results = allCustomers.filter(function(c){ return c.name.toLowerCase().includes(ql) || (c.phone && c.phone.toString().includes(q)); });
        }
        if (results.length === 0) {
            dd.innerHTML = '<div style="padding:12px 16px;font-size:13px;color:#9ca3af;">Müşteri bulunamadı</div>';
        } else {
            dd.innerHTML = results.slice(0, 80).map(function(c){
                var last4 = c.phone ? c.phone.toString().slice(-4) : '';
                return '<div style="padding:10px 16px;font-size:13px;cursor:pointer;border-bottom:1px solid #f3f4f6;" class="cust-item" data-id="'+c.id+'" data-name="'+escHtml(c.name)+'" data-phone="'+escHtml(c.phone||'')+'">' +
                    '<span style="font-weight:600;color:#111;">'+escHtml(c.name)+'</span> ' +
                    '<span style="color:#9ca3af;font-size:11px;">···'+last4+'</span>' +
                    '</div>';
            }).join('');
            dd.querySelectorAll('.cust-item').forEach(function(item){
                function selectItem(e) {
                    e.preventDefault();
                    hidden.value = item.dataset.id;
                    var last4 = item.dataset.phone ? item.dataset.phone.slice(-4) : '';
                    input.value = item.dataset.name + ' (···' + last4 + ')';
                    dd.classList.add('hidden');
                    input.blur();
                }
                item.addEventListener('mousedown', selectItem);
                item.addEventListener('touchstart', selectItem);
            });
        }
        dd.classList.remove('hidden');
    }

    input.addEventListener('input', function(){ doSearch(this.value); });
    input.addEventListener('focus', function(){ doSearch(this.value); });
    input.addEventListener('blur', function(){ setTimeout(function(){ dd.classList.add('hidden'); }, 200); });

    // old() ile seçili müşteri
    var oldId = {{ old('customer_id', 'null') }};
    if (oldId) {
        var c = allCustomers.find(function(x){ return x.id == oldId; });
        if (c) { hidden.value = c.id; var l4 = c.phone?c.phone.slice(-4):''; input.value = c.name+' (···'+l4+')'; }
    }
}

window.addEventListener('DOMContentLoaded', function(){
    // Tekil müşteri arama
    var singleWrap = document.querySelector('#singleCustomerSection .cust-search-wrap');
    if (singleWrap) initCustomerSearch(singleWrap);

    // Grup müşteri arama (seçince listeye ekler, input'u temizler)
    var groupWrap = document.querySelector('.group-cust-wrap');
    if (groupWrap) {
        var input = groupWrap.querySelector('.cust-search-input');
        var dd = groupWrap.querySelector('.cust-dropdown');

        function doGroupSearch(q) {
            q = (q || '').trim();
            var results = q.length === 0 ? allCustomers.slice(0,50) :
                allCustomers.filter(function(c){
                    var ql = q.toLowerCase();
                    return c.name.toLowerCase().includes(ql) || (c.phone && c.phone.toString().includes(q));
                });
            if (results.length === 0) {
                dd.innerHTML = '<div style="padding:12px 16px;font-size:13px;color:#9ca3af;">Müşteri bulunamadı</div>';
            } else {
                dd.innerHTML = results.slice(0,80).map(function(c){
                    var last4 = c.phone ? c.phone.toString().slice(-4) : '';
                    return '<div style="padding:10px 16px;font-size:13px;cursor:pointer;border-bottom:1px solid #f3f4f6;" class="cust-item" data-id="'+c.id+'" data-name="'+escHtml(c.name)+'" data-phone="'+escHtml(c.phone||'')+'">'+
                        '<span style="font-weight:600;color:#111;">'+escHtml(c.name)+'</span> '+
                        '<span style="color:#9ca3af;font-size:11px;">···'+last4+'</span>'+
                        '</div>';
                }).join('');
                dd.querySelectorAll('.cust-item').forEach(function(item){
                    function selectGroupItem(e) {
                        e.preventDefault();
                        addGroupCustomer(parseInt(item.dataset.id), item.dataset.name, item.dataset.phone);
                        input.value = '';
                        dd.classList.add('hidden');
                        input.blur();
                    }
                    item.addEventListener('mousedown', selectGroupItem);
                    item.addEventListener('touchstart', selectGroupItem);
                });
            }
            dd.classList.remove('hidden');
        }

        input.addEventListener('input', function(){ doGroupSearch(this.value); });
        input.addEventListener('focus', function(){ doGroupSearch(this.value); });
        input.addEventListener('blur', function(){ setTimeout(function(){ dd.classList.add('hidden'); }, 200); });
    }

    renderGroupCustomers();
});
</script>
@endsection
