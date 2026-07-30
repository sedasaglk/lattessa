@extends('layouts.panel')

@section('title', 'Randevu Düzenle')

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.appointments.show', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}"
       class="text-gray-400 hover:text-gray-900">← Geri</a>
    <h1 class="text-2xl font-semibold text-gray-900">Randevu #{{ $appointment->id }} — Düzenle</h1>
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

    <form method="POST" action="{{ route('panel.appointments.update', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Şube --}}
        @if($userBranchId)
        <input type="hidden" name="branch_id" value="{{ $userBranchId }}">
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
                    <option value="{{ $branch->id }}" {{ old('branch_id', $appointment->branch_id) == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Müşteri --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Müşteri</label>
            <div class="relative cust-search-wrap">
                <input type="text" autocomplete="off"
                       placeholder="İsim veya son 4 hane telefon..."
                       class="cust-search-input w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                <input type="hidden" name="customer_id" class="cust-id-input" required value="{{ old('customer_id', $appointment->customer_id) }}">
                <div class="cust-dropdown absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-xl hidden"
                     style="max-height:220px; overflow-y:auto; top:calc(100% + 2px); left:0;"></div>
            </div>
        </div>

        {{-- Hizmet --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hizmet</label>
            <select name="service_id" required
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                <option value="">Hizmet seçin</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ old('service_id', $appointment->service_id) == $service->id ? 'selected' : '' }}>
                        {{ $service->name }} ({{ $service->duration_minutes }} dk — {{ number_format($service->price, 0) }} TL)
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
                    <option value="{{ $member->id }}" {{ old('staff_id', $appointment->staff_id) == $member->id ? 'selected' : '' }}>
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
            <input type="datetime-local" name="start_time"
                   value="{{ old('start_time', $appointment->start_time->format('Y-m-d\TH:i')) }}"
                   required
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
        </div>

        {{-- Notlar --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notlar (opsiyonel)</label>
            <textarea name="notes" rows="3"
                      class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                      placeholder="Randevuya ait notlar...">{{ old('notes', $appointment->notes) }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Kaydet
            </button>
            <a href="{{ route('panel.appointments.show', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}"
               class="px-6 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                İptal
            </a>
        </div>
    </form>
</div>

<script>
var allCustomers = @json($customers->map(fn($c) => ['id'=>$c->id,'name'=>$c->name,'phone'=>$c->phone]));

function escHtml(s) { return String(s).replace(/'/g,"&#39;").replace(/"/g,'&quot;'); }

function initCustomerSearch(wrap) {
    var input = wrap.querySelector('.cust-search-input');
    var dd = wrap.querySelector('.cust-dropdown');
    var hidden = wrap.querySelector('.cust-id-input');

    // Mevcut müşteriyi göster
    var existingId = hidden.value;
    if (existingId) {
        var c = allCustomers.find(function(x){ return x.id == existingId; });
        if (c) { var l4 = c.phone ? c.phone.slice(-4) : ''; input.value = c.name + ' (···' + l4 + ')'; }
    }

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
                item.addEventListener('mousedown', function(e){
                    e.preventDefault();
                    hidden.value = item.dataset.id;
                    var last4 = item.dataset.phone ? item.dataset.phone.slice(-4) : '';
                    input.value = item.dataset.name + ' (···' + last4 + ')';
                    dd.classList.add('hidden');
                });
            });
        }
        dd.classList.remove('hidden');
    }

    input.addEventListener('input', function(){ doSearch(this.value); });
    input.addEventListener('focus', function(){ doSearch(this.value); });
    input.addEventListener('blur', function(){ setTimeout(function(){ dd.classList.add('hidden'); }, 150); });
}

window.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.cust-search-wrap').forEach(function(wrap){
        if (wrap.offsetParent !== null) initCustomerSearch(wrap);
    });
});
</script>
@endsection
