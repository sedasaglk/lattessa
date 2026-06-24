@extends('layouts.panel')
@section('title', 'Randevular')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Randevular</h1>
    <div class="flex items-center gap-2">
        <a href="?view=list&date={{ $date }}"
           class="px-3 py-2 text-sm rounded-lg border {{ $view === 'list' ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Liste
        </a>
        <a href="?view=calendar"
           class="px-3 py-2 text-sm rounded-lg border {{ $view === 'calendar' ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Takvim
        </a>
        <a href="{{ route('panel.appointments.create', ['tenant_slug' => $tenant->slug]) }}"
           class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            + Yeni Randevu
        </a>
    </div>
</div>

@if($view === 'calendar')
{{-- TAKVIM GORUNUMU --}}
<div class="bg-white rounded-xl border border-gray-200 p-4">

    {{-- Personel Filtresi --}}
    <div class="mb-4 flex items-center gap-3">
        <label class="text-sm text-gray-600">Personel:</label>
        <select id="staffFilter" onchange="filterByStaff()"
                class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
            <option value="">Tumu</option>
            @foreach(\Illuminate\Support\Facades\DB::table('users')->where('tenant_id', $tenant->id)->whereNull('deleted_at')->whereIn('role', ['firma_sahibi', 'sube_muduru', 'personel'])->get() as $member)
                <option value="{{ $member->id }}">{{ $member->name }}</option>
            @endforeach
        </select>

        {{-- Legend --}}
        <div class="flex items-center gap-3 ml-4">
            <span class="flex items-center gap-1 text-xs text-gray-500">
                <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span> Bekliyor
            </span>
            <span class="flex items-center gap-1 text-xs text-gray-500">
                <span class="w-3 h-3 rounded-full bg-green-600 inline-block"></span> Onaylandi
            </span>
            <span class="flex items-center gap-1 text-xs text-gray-500">
                <span class="w-3 h-3 rounded-full bg-blue-600 inline-block"></span> Tamamlandi
            </span>
            <span class="flex items-center gap-1 text-xs text-gray-500">
                <span class="w-3 h-3 rounded-full bg-red-600 inline-block"></span> Iptal
            </span>
        </div>
    </div>

    <div id="calendar"></div>
</div>

{{-- Randevu Detay Modal --}}
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Randevu Detay</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-900">✕</button>
        </div>
        <div id="modalContent" class="space-y-2 text-sm"></div>
        <div class="mt-4 flex gap-2">
            <a id="modalDetailLink" href="#"
               class="flex-1 text-center bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Detaya Git
            </a>
            <button onclick="closeModal()"
                    class="flex-1 border border-gray-200 text-gray-600 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                Kapat
            </button>
        </div>
    </div>
</div>

@else
{{-- LİSTE GORUNUMU --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
    <form method="GET" class="flex items-center gap-3">
        <input type="hidden" name="view" value="list">
        <input type="date" name="date" value="{{ $date }}"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
        <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            Filtrele
        </button>
        <a href="?view=list&date={{ today()->format('Y-m-d') }}" class="text-sm text-gray-500 hover:text-gray-900">Bugun</a>
        <a href="?view=list&date={{ today()->addDay()->format('Y-m-d') }}" class="text-sm text-gray-500 hover:text-gray-900">Yarin</a>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200">
    @if($appointments->isEmpty())
        <div class="p-12 text-center">
            <p class="text-gray-400">Bu tarihe ait randevu bulunmuyor.</p>
            <a href="{{ route('panel.appointments.create', ['tenant_slug' => $tenant->slug]) }}"
               class="inline-block mt-4 text-sm text-gray-900 underline">Yeni randevu ekle</a>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($appointments as $appointment)
            <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                <div class="flex items-center gap-4">
                    <div class="text-center w-14">
                        <p class="text-lg font-semibold text-gray-900">{{ $appointment->start_time->format('H:i') }}</p>
                        <p class="text-xs text-gray-400">{{ $appointment->end_time->format('H:i') }}</p>
                    </div>
                    <div class="w-1 h-10 rounded-full
                        {{ $appointment->status === 'confirmed' ? 'bg-green-400' : '' }}
                        {{ $appointment->status === 'pending' ? 'bg-amber-400' : '' }}
                        {{ $appointment->status === 'completed' ? 'bg-blue-400' : '' }}
                        {{ $appointment->status === 'cancelled' ? 'bg-red-400' : '' }}
                        {{ $appointment->status === 'no_show' ? 'bg-gray-400' : '' }}
                    "></div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $appointment->customer->name }}</p>
                        <p class="text-sm text-gray-500">{{ $appointment->service->name }} &bull; {{ $appointment->staff->name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-900">{{ number_format($appointment->price, 0, ',', '.') }} TL</span>
                    <span class="text-xs px-2 py-1 rounded-full
                        {{ $appointment->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $appointment->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $appointment->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $appointment->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $appointment->status === 'no_show' ? 'bg-gray-100 text-gray-700' : '' }}
                    ">
                        {{ match($appointment->status) {
                            'pending' => 'Bekliyor',
                            'confirmed' => 'Onaylandi',
                            'completed' => 'Tamamlandi',
                            'cancelled' => 'Iptal',
                            'no_show' => 'Gelmedi',
                            default => $appointment->status
                        } }}
                    </span>
                    <a href="{{ route('panel.appointments.show', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}"
                       class="text-sm text-gray-500 hover:text-gray-900">Detay</a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endif

{{-- FullCalendar --}}
@if($view === 'calendar')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
const tenantSlug = '{{ $tenant->slug }}';
let calendar;
let currentStaffId = '';

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'tr',
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Bugun',
            month: 'Ay',
            week: 'Hafta',
            day: 'Gun',
        },
        slotMinTime: '07:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        height: 'auto',
        events: function(info, successCallback, failureCallback) {
            fetch(`/${tenantSlug}/randevular/events?start=${info.startStr}&end=${info.endStr}&staff_id=${currentStaffId}`)
                .then(r => r.json())
                .then(events => successCallback(events))
                .catch(err => failureCallback(err));
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            showModal(info.event);
        },
        dateClick: function(info) {
            const dateTime = info.dateStr;
            window.location.href = `/${tenantSlug}/randevular/yeni?date=${dateTime}`;
        },
        eventDidMount: function(info) {
            const props = info.event.extendedProps;
            info.el.title = `${props.customer}\n${props.service}\n${props.staff}`;
        }
    });

    calendar.render();
});

function filterByStaff() {
    currentStaffId = document.getElementById('staffFilter').value;
    calendar.refetchEvents();
}

function showModal(event) {
    const props = event.extendedProps;
    const statusMap = {
        'pending': 'Bekliyor',
        'confirmed': 'Onaylandi',
        'completed': 'Tamamlandi',
        'cancelled': 'Iptal',
        'no_show': 'Gelmedi',
    };

    const start = new Date(event.start);
    const end = new Date(event.end);
    const timeStr = start.toLocaleTimeString('tr-TR', {hour: '2-digit', minute: '2-digit'})
        + ' - ' + end.toLocaleTimeString('tr-TR', {hour: '2-digit', minute: '2-digit'});
    const dateStr = start.toLocaleDateString('tr-TR', {day: '2-digit', month: '2-digit', year: 'numeric'});

    document.getElementById('modalContent').innerHTML = `
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Musteri</span>
            <span class="font-medium text-gray-900">${props.customer}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Hizmet</span>
            <span class="font-medium text-gray-900">${props.service}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Personel</span>
            <span class="font-medium text-gray-900">${props.staff}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Tarih</span>
            <span class="font-medium text-gray-900">${dateStr}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Saat</span>
            <span class="font-medium text-gray-900">${timeStr}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Durum</span>
            <span class="font-medium text-gray-900">${statusMap[props.status] || props.status}</span>
        </div>
        <div class="flex justify-between py-1">
            <span class="text-gray-500">Ucret</span>
            <span class="font-medium text-gray-900">${parseFloat(props.price).toLocaleString('tr-TR')} TL</span>
        </div>
    `;

    document.getElementById('modalDetailLink').href = event.url;
    document.getElementById('appointmentModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('appointmentModal').classList.add('hidden');
}

document.getElementById('appointmentModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endif
@endsection
