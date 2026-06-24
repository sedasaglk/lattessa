<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Randevu - {{ $tenant->company_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-lg mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-2xl font-semibold text-gray-900">{{ $tenant->company_name }}</h1>
        <p class="text-gray-500 text-sm mt-1">Online Randevu</p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('booking.store', ['tenant_slug' => $tenant->slug]) }}" id="bookingForm" class="space-y-4">
        @csrf

        {{-- Adim 1: Hizmet --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-medium text-gray-900 mb-3">1. Hizmet Secin</h2>
            @if($services->isEmpty())
                <p class="text-gray-400 text-sm">Henuz online rezervasyona acik hizmet bulunmuyor.</p>
            @else
                <div class="space-y-2">
                    @foreach($services as $service)
                    <label class="flex items-center justify-between p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-gray-900 transition has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="service_id" value="{{ $service->id }}"
                                   {{ old('service_id') == $service->id ? 'checked' : '' }}
                                   class="text-gray-900" onchange="loadStaff({{ $service->id }})">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $service->name }}</p>
                                <p class="text-xs text-gray-500">{{ $service->duration_minutes }} dakika</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($service->price, 0, ',', '.') }} TL</span>
                    </label>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Adim 2: Personel --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5" id="staffSection" style="display:none">
            <h2 class="font-medium text-gray-900 mb-3">2. Personel Secin</h2>
            <div id="staffList" class="space-y-2"></div>
        </div>

        {{-- Adim 3: Tarih --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5" id="dateSection" style="display:none">
            <h2 class="font-medium text-gray-900 mb-3">3. Tarih Secin</h2>
            <input type="date" name="date" id="dateInput"
                   min="{{ date('Y-m-d') }}"
                   value="{{ old('date') }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                   onchange="loadSlots()">
        </div>

        {{-- Adim 4: Saat --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5" id="slotsSection" style="display:none">
            <h2 class="font-medium text-gray-900 mb-3">4. Saat Secin</h2>
            <div id="slotsList" class="flex flex-wrap gap-2"></div>
            <input type="hidden" name="time" id="timeInput">
        </div>

        {{-- Adim 5: Bilgiler --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5" id="infoSection" style="display:none">
            <h2 class="font-medium text-gray-900 mb-3">5. Bilgileriniz</h2>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                           placeholder="Adiniz Soyadiniz">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                           placeholder="05XX XXX XX XX">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Not (opsiyonel)</label>
                    <textarea name="customer_notes" rows="2"
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                              placeholder="Eklemek istediginiz notlar...">{{ old('customer_notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Gonder --}}
        <div id="submitSection" style="display:none">
            <button type="submit"
                    class="w-full bg-gray-900 text-white py-3.5 rounded-xl font-medium text-sm hover:bg-gray-800 transition">
                Randevu Olustur
            </button>
        </div>

    </form>
</div>

<script>
const tenantSlug = '{{ $tenant->slug }}';
let selectedStaffId = null;
let selectedServiceId = null;

function loadStaff(serviceId) {
    selectedServiceId = serviceId;
    selectedStaffId = null;

    fetch(`/${tenantSlug}/randevu/personel?service_id=${serviceId}`)
        .then(r => r.json())
        .then(staff => {
            const list = document.getElementById('staffList');
            list.innerHTML = '';

            staff.forEach(member => {
                list.innerHTML += `
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-gray-900 transition">
                        <input type="radio" name="staff_id" value="${member.id}"
                               class="text-gray-900" onchange="selectStaff(${member.id})">
                        <span class="text-sm font-medium text-gray-900">${member.name}</span>
                    </label>`;
            });

            document.getElementById('staffSection').style.display = 'block';
            document.getElementById('dateSection').style.display = 'none';
            document.getElementById('slotsSection').style.display = 'none';
            document.getElementById('infoSection').style.display = 'none';
            document.getElementById('submitSection').style.display = 'none';
        });
}

function selectStaff(staffId) {
    selectedStaffId = staffId;
    document.getElementById('dateSection').style.display = 'block';
    document.getElementById('slotsSection').style.display = 'none';
    document.getElementById('infoSection').style.display = 'none';
    document.getElementById('submitSection').style.display = 'none';
}

function loadSlots() {
    const date = document.getElementById('dateInput').value;
    if (!date || !selectedStaffId || !selectedServiceId) return;

    document.getElementById('slotsList').innerHTML = '<p class="text-sm text-gray-400">Yukluyor...</p>';
    document.getElementById('slotsSection').style.display = 'block';

    fetch(`/${tenantSlug}/randevu/saatler?staff_id=${selectedStaffId}&service_id=${selectedServiceId}&date=${date}`)
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('slotsList');
            list.innerHTML = '';

            if (!data.slots || data.slots.length === 0) {
                list.innerHTML = '<p class="text-sm text-gray-400">Bu tarihte musait saat bulunmuyor.</p>';
                return;
            }

            data.slots.forEach(slot => {
                list.innerHTML += `
                    <button type="button" onclick="selectSlot('${slot}')"
                            id="slot-${slot.replace(':', '')}"
                            class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium hover:border-gray-900 hover:bg-gray-50 transition slot-btn">
                        ${slot}
                    </button>`;
            });

            document.getElementById('infoSection').style.display = 'none';
            document.getElementById('submitSection').style.display = 'none';
        });
}

function selectSlot(time) {
    document.querySelectorAll('.slot-btn').forEach(btn => {
        btn.classList.remove('bg-gray-900', 'text-white', 'border-gray-900');
    });

    const btn = document.getElementById('slot-' + time.replace(':', ''));
    if (btn) {
        btn.classList.add('bg-gray-900', 'text-white', 'border-gray-900');
    }

    document.getElementById('timeInput').value = time;
    document.getElementById('infoSection').style.display = 'block';
    document.getElementById('submitSection').style.display = 'block';
}
</script>

</body>
</html>
