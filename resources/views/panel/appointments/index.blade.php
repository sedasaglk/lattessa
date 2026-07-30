@extends('layouts.panel')
@section('title', 'Randevular')

@section('content')

<div class="mb-4 flex items-center justify-between flex-wrap gap-2">
    <h1 class="text-xl font-semibold text-gray-900">Randevular</h1>
    <div class="flex items-center gap-2">
        <a href="?view=list&date={{ $date }}"
           class="px-3 py-2 text-sm rounded-lg border {{ $view === 'list' ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Liste
        </a>
        <a href="?view=grid&date={{ $date }}"
           class="px-3 py-2 text-sm rounded-lg border {{ $view === 'grid' ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Tablo
        </a>
        <a href="?view=calendar"
           class="px-3 py-2 text-sm rounded-lg border {{ $view === 'calendar' ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Takvim
        </a>
        <a href="{{ route('panel.appointments.create', ['tenant_slug' => $tenant->slug]) }}"
           class="btn-primary text-sm px-4 py-2">
            + Yeni
        </a>
    </div>
</div>

@if($view === 'grid')
@php
$gridDate = \Carbon\Carbon::parse($date);
$gridPrev = $gridDate->copy()->subDay()->format('Y-m-d');
$gridNext = $gridDate->copy()->addDay()->format('Y-m-d');
$gridStaffColors = ['#FACC15','#6366F1','#10B981','#EC4899','#3B82F6','#8B5CF6','#EF4444','#F97316','#14B8A6','#84CC16'];
$gridBranchCtx = app(\App\Services\BranchContext::class);
$gridBranchCtx->setFromUser();
$gridStaffQuery = \Illuminate\Support\Facades\DB::table('users')
    ->where('tenant_id', $tenant->id)
    ->whereNull('deleted_at')
    ->whereIn('role', ['firma_sahibi', 'sube_muduru', 'personel', 'sekreter'])
    ->orderBy('name');
if ($gridBranchCtx->getBranchId()) {
    $gridStaffQuery->where(function($q) use ($gridBranchCtx) {
        $q->where('branch_id', $gridBranchCtx->getBranchId())->orWhere('role', 'firma_sahibi');
    });
}
$gridStaff = $gridStaffQuery->get(['id', 'name']);
$gridTurkishDays = ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'];
$gridDayName = $gridTurkishDays[$gridDate->dayOfWeek];
$gridStartHour = 8;
$gridEndHour = 22;
$gridTotalSlots = ($gridEndHour - $gridStartHour) * 2;
$gridSlotH = 44;
$gridTotalH = $gridTotalSlots * $gridSlotH;
@endphp

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    {{-- Date nav --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
        <a href="?view=grid&date={{ $gridPrev }}" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-600 text-lg">‹</a>
        <span class="font-semibold text-gray-900 text-sm">{{ $gridDate->format('d') }} {{ ['Oca','Şub','Mar','Nis','May','Haz','Tem','Ağu','Eyl','Eki','Kas','Ara'][$gridDate->month-1] }} {{ $gridDate->year }} {{ $gridDayName }}</span>
        <a href="?view=grid&date={{ $gridNext }}" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-600 text-lg">›</a>
    </div>

    {{-- Grid --}}
    <div class="grid-scroll-container" style="overflow-x:auto; overflow-y:auto; max-height:75vh;">
        <div style="display:flex; flex-direction:column; min-width:{{ 52 + count((array)$gridStaff) * 90 }}px;">

            {{-- Sticky header: staff names --}}
            <div style="display:flex; position:sticky; top:0; z-index:20; background:#fff; border-bottom:2px solid #e5e7eb;">
                <div style="width:52px; min-width:52px; flex-shrink:0;"></div>
                @foreach($gridStaff as $gi => $gm)
                @php $gc = $gridStaffColors[$gi % count($gridStaffColors)]; $gtc = in_array($gc,['#FACC15','#84CC16']) ? '#000' : '#fff'; @endphp
                <div style="flex:1; min-width:90px; text-align:center; padding:8px 4px; font-weight:700; font-size:11px; background:{{ $gc }}; color:{{ $gtc }}; border-right:1px solid rgba(0,0,0,0.08);">
                    {{ $gm->name }}
                </div>
                @endforeach
            </div>

            {{-- Body: time col + staff columns side by side --}}
            <div style="display:flex; position:relative;">

                {{-- Time column --}}
                <div style="width:52px; min-width:52px; flex-shrink:0; position:sticky; left:0; z-index:10; background:#fff; border-right:1px solid #e5e7eb;">
                    @for($h = $gridStartHour; $h < $gridEndHour; $h++)
                        @foreach([0,30] as $m)
                        <div style="height:{{ $gridSlotH }}px; display:flex; align-items:flex-start; justify-content:flex-end; padding:3px 6px 0 0; border-bottom:1px solid {{ $m===0 ? '#e5e7eb' : '#f3f4f6' }};">
                            @if($m===0)
                            <span style="font-size:11px; color:#9ca3af; font-weight:500;">{{ sprintf('%02d:%02d',$h,$m) }}</span>
                            @endif
                        </div>
                        @endforeach
                    @endfor
                </div>

                {{-- Staff columns --}}
                @foreach($gridStaff as $gi => $gm)
                @php $gc = $gridStaffColors[$gi % count($gridStaffColors)]; $gtc = in_array($gc,['#FACC15','#84CC16']) ? '#000' : '#fff'; @endphp
                <div class="staff-col-wrap" data-staff-id="{{ $gm->id }}"
                     style="flex:1; min-width:90px; position:relative; border-right:1px solid #e5e7eb;">
                    {{-- Background rows --}}
                    @for($h = $gridStartHour; $h < $gridEndHour; $h++)
                        @foreach([0,30] as $m)
                        <div style="height:{{ $gridSlotH }}px; border-bottom:1px solid {{ $m===0 ? '#e5e7eb' : '#f3f4f6' }}; cursor:pointer; box-sizing:border-box;"
                             onclick="newAppt('{{ $gridDate->format('Y-m-d') }}T{{ sprintf('%02d:%02d',$h,$m) }}')">
                        </div>
                        @endforeach
                    @endfor
                    {{-- Appointments rendered here by JS --}}
                </div>
                @endforeach

            </div>
        </div>
    </div>
</div>

{{-- Appointment Detail Modal --}}
<div class="grid-modal fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="grid-modal-dot w-3 h-3 rounded-full"></div>
                <h3 class="font-semibold text-gray-900">Randevu Detay</h3>
            </div>
            <button onclick="closeGridModal()" class="text-gray-400 hover:text-gray-900 text-lg">✕</button>
        </div>
        <div class="grid-modal-content space-y-1 text-sm"></div>
        <div class="mt-4 flex gap-2">
            <a class="grid-modal-link" href="#" class="flex-1 text-center bg-gray-900 text-white py-2.5 rounded-xl text-sm font-medium">Detaya Git</a>
            <button onclick="closeGridModal()" class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm">Kapat</button>
        </div>
    </div>
</div>

<script>
var tenantSlug = '{{ $tenant->slug }}';
var gridDate = '{{ $date }}';
var SLOT_H = {{ $gridSlotH }};
var START_HOUR = {{ $gridStartHour }};
var staffColors = {
    @foreach($gridStaff as $gi => $gm)
    {{ $gm->id }}: '{{ $gridStaffColors[$gi % count($gridStaffColors)] }}',
    @endforeach
};

document.addEventListener('DOMContentLoaded', function() {
    loadGridEvents();
    // Scroll to 9:00
    var scrollEl = getVisibleEl('.grid-scroll-container');
    if (scrollEl) scrollEl.scrollTop = (9 - START_HOUR) * 2 * SLOT_H;
});

function getVisibleEl(selector) {
    var els = document.querySelectorAll(selector);
    for (var i = 0; i < els.length; i++) {
        if (els[i].offsetParent !== null) return els[i];
    }
    return els[0] || null;
}

function loadGridEvents() {
    fetch('/' + tenantSlug + '/randevular/events?start=' + gridDate + 'T00:00:00&end=' + gridDate + 'T23:59:59')
        .then(r => r.json())
        .then(renderGridEvents)
        .catch(console.error);
}

function renderGridEvents(events) {
    document.querySelectorAll('.appt-block').forEach(el => el.remove());
    events.forEach(function(ev) {
        var staffId = ev.extendedProps && ev.extendedProps.staff_id;
        if (!staffId) return;
        var col = getVisibleEl('.staff-col-wrap[data-staff-id="' + staffId + '"]');
        if (!col) return;

        var startStr = ev.start.slice(11,16);
        var endStr = ev.end ? ev.end.slice(11,16) : '';
        var startMin = timeToMin(startStr) - START_HOUR*60;
        var endMin = endStr ? (timeToMin(endStr) - START_HOUR*60) : startMin+30;
        var topPx = (startMin/30)*SLOT_H;
        var heightPx = Math.max(((endMin-startMin)/30)*SLOT_H - 2, 22);

        var color = staffColors[staffId] || ev.color || '#6366F1';
        var tc = (color==='#FACC15'||color==='#84CC16') ? '#000' : '#fff';

        var b = document.createElement('div');
        b.className = 'appt-block';
        b.style.cssText = 'position:absolute;left:2px;right:2px;top:'+topPx+'px;height:'+heightPx+'px;background:'+color+';border-radius:6px;padding:2px 4px;cursor:pointer;overflow:hidden;z-index:10;';
        b.innerHTML =
            '<div style="font-size:9px;font-weight:700;color:'+tc+';line-height:1.2;">'+startStr+(endStr?' - '+endStr:'')+'</div>'+
            '<div style="font-size:11px;font-weight:700;color:'+tc+';line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+(ev.title||'')+'</div>'+
            (ev.extendedProps&&ev.extendedProps.service?'<div style="font-size:9px;color:'+tc+';opacity:0.85;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+ev.extendedProps.service+'</div>':'');
        b.onclick = function(e){ e.stopPropagation(); showGridModal(ev,color); };
        col.appendChild(b);
    });
}

function timeToMin(t){ var p=t.split(':'); return parseInt(p[0])*60+parseInt(p[1]); }
function newAppt(dt){ window.location.href='/'+tenantSlug+'/randevular/yeni?date='+dt; }

function timeToMin(t) {
    var parts = t.split(':');
    return parseInt(parts[0]) * 60 + parseInt(parts[1]);
}

function newAppt(dateTimeStr) {
    window.location.href = '/' + tenantSlug + '/randevular/yeni?date=' + dateTimeStr;
}

function getModal() { return getVisibleEl('.grid-modal'); }

function showGridModal(ev, color) {
    var modal = getModal();
    if (!modal) return;
    var props = ev.extendedProps || {};
    var statusMap = {'pending':'Bekliyor','confirmed':'Onaylı','completed':'Tamamlandı','cancelled':'İptal','no_show':'Gelmedi'};
    var startStr = ev.start ? ev.start.slice(11,16) : '';
    var endStr = ev.end ? ev.end.slice(11,16) : '';
    modal.querySelector('.grid-modal-dot').style.background = color;
    modal.querySelector('.grid-modal-content').innerHTML =
        '<div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500">Müşteri</span><span class="font-medium">' + (ev.title||'') + '</span></div>' +
        '<div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500">Hizmet</span><span class="font-medium">' + (props.service||'-') + '</span></div>' +
        '<div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500">Personel</span><span class="font-medium" style="color:' + color + '">' + (props.staff||'-') + '</span></div>' +
        '<div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500">Saat</span><span class="font-medium">' + startStr + (endStr?' - '+endStr:'') + '</span></div>' +
        '<div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500">Durum</span><span class="font-medium">' + (statusMap[props.status]||props.status||'-') + '</span></div>' +
        '<div class="flex justify-between py-2"><span class="text-gray-500">Ücret</span><span class="font-medium">' + (props.price ? parseFloat(props.price).toLocaleString('tr-TR') + ' TL' : '-') + '</span></div>';
    var link = modal.querySelector('.grid-modal-link');
    if (link) { link.href = ev.url || '#'; link.className = 'grid-modal-link flex-1 text-center bg-gray-900 text-white py-2.5 rounded-xl text-sm font-medium'; }
    modal.classList.remove('hidden');
}

function closeGridModal() {
    document.querySelectorAll('.grid-modal').forEach(function(m){ m.classList.add('hidden'); });
}
document.querySelectorAll('.grid-modal').forEach(function(m) {
    m.addEventListener('click', function(e) { if (e.target === this) closeGridModal(); });
});
</script>
<style>
.appt-block { transition: opacity 0.1s; }
.appt-block:hover { opacity: 0.88; }
</style>

@elseif($view === 'calendar')

@php
$staffColors = ['#6366F1','#EC4899','#F59E0B','#10B981','#3B82F6','#8B5CF6','#EF4444','#14B8A6','#F97316','#84CC16'];
$branchCtxBlade = app(\App\Services\BranchContext::class);
$branchCtxBlade->setFromUser();
$staffQuery2 = \Illuminate\Support\Facades\DB::table('users')
    ->where('tenant_id', $tenant->id)
    ->whereNull('deleted_at')
    ->whereIn('role', ['firma_sahibi', 'sube_muduru', 'personel', 'sekreter'])
    ->orderBy('name');
if ($branchCtxBlade->getBranchId()) {
    $staffQuery2->where(function($q) use ($branchCtxBlade) {
        $q->where('branch_id', $branchCtxBlade->getBranchId())->orWhere('role', 'firma_sahibi');
    });
}
$staffMembers = $staffQuery2->get(['id', 'name']);
$staffColorMap = [];
foreach ($staffMembers as $i => $member) {
    $staffColorMap[$member->id] = $staffColors[$i % count($staffColors)];
}
@endphp

<div class="bg-white rounded-xl border border-gray-200 p-4">

    {{-- Personel Legend / Filtre --}}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <button onclick="toggleAllStaff()" id="allStaffBtn"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border-2 border-indigo-500 text-indigo-600 hover:bg-indigo-50 transition">
            Tümü
        </button>
        @foreach($staffMembers as $i => $member)
        <button onclick="toggleStaff({{ $member->id }})" id="staffBtn_{{ $member->id }}"
                data-staff-id="{{ $member->id }}"
                data-color="{{ $staffColorMap[$member->id] }}"
                class="staff-filter-btn flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border-2 transition active-staff"
                style="border-color: {{ $staffColorMap[$member->id] }}; color: {{ $staffColorMap[$member->id] }}; background: {{ $staffColorMap[$member->id] }}18;">
            <span class="w-2 h-2 rounded-full inline-block" style="background: {{ $staffColorMap[$member->id] }};"></span>
            {{ $member->name }}
        </button>
        @endforeach

        {{-- Durum legend --}}
        <div class="ml-auto flex items-center gap-3">
            <span class="flex items-center gap-1 text-xs text-gray-400">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-400 inline-block opacity-40"></span> İptal/Gelmedi
            </span>
        </div>
    </div>

    <div id="calendar" style="height:600px;min-height:600px;"></div>
</div>

{{-- Randevu Detay Modal --}}
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div id="modalStaffColor" class="w-3 h-3 rounded-full"></div>
                <h3 class="font-semibold text-gray-900">Randevu Detay</h3>
            </div>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-900 text-lg">✕</button>
        </div>
        <div id="modalContent" class="space-y-2 text-sm"></div>
        <div class="mt-4 flex gap-2">
            <a id="modalDetailLink" href="#"
               class="flex-1 text-center bg-gray-900 text-white py-2.5 rounded-xl text-sm font-medium">
                Detaya Git
            </a>
            <button onclick="closeModal()"
                    class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm">
                Kapat
            </button>
        </div>
    </div>
</div>

@else
{{-- LİSTE GORUNUMU --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="hidden" name="view" value="list">
        <input type="date" name="date" value="{{ $date }}"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="btn-primary text-sm px-4 py-2">Filtrele</button>
        <a href="?view=list&date={{ today()->format('Y-m-d') }}" class="text-sm text-gray-500 hover:text-gray-900">Bugün</a>
        <a href="?view=list&date={{ today()->addDay()->format('Y-m-d') }}" class="text-sm text-gray-500 hover:text-gray-900">Yarın</a>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200">
    @if($appointments->isEmpty())
        <div class="p-12 text-center">
            <p class="text-gray-400 text-sm">Bu tarihe ait randevu bulunmuyor.</p>
            <a href="{{ route('panel.appointments.create', ['tenant_slug' => $tenant->slug]) }}"
               class="inline-block mt-4 text-sm text-indigo-600 underline">Yeni randevu ekle</a>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @php
            $staffColors2 = ['#6366F1','#EC4899','#F59E0B','#10B981','#3B82F6','#8B5CF6','#EF4444','#14B8A6','#F97316','#84CC16'];
            $allStaff2 = \Illuminate\Support\Facades\DB::table('users')->where('tenant_id', $tenant->id)->whereNull('deleted_at')->orderBy('name')->pluck('id')->toArray();
            $colorMap2 = [];
            foreach ($allStaff2 as $i2 => $sid2) { $colorMap2[$sid2] = $staffColors2[$i2 % count($staffColors2)]; }
            @endphp
            @foreach($appointments as $appointment)
            @php $staffColor = $colorMap2[$appointment->staff_id] ?? '#6366F1'; @endphp
            <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="text-center w-12 flex-shrink-0">
                        <p class="text-base font-semibold text-gray-900">{{ $appointment->start_time->format('H:i') }}</p>
                        <p class="text-xs text-gray-400">{{ $appointment->end_time->format('H:i') }}</p>
                    </div>
                    <div class="w-1 self-stretch rounded-full flex-shrink-0" style="background: {{ $staffColor }};"></div>
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 text-sm truncate">{{ $appointment->customer->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $appointment->service->name }} · {{ $appointment->staff->name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-sm font-medium text-gray-900 hidden sm:block">{{ number_format($appointment->price, 0, ',', '.') }} TL</span>
                    <span class="text-xs px-2 py-1 rounded-full
                        {{ $appointment->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $appointment->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $appointment->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $appointment->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $appointment->status === 'no_show' ? 'bg-gray-100 text-gray-700' : '' }}">
                        {{ match($appointment->status) { 'pending'=>'Bekliyor','confirmed'=>'Onaylı','completed'=>'Tamamlandı','cancelled'=>'İptal','no_show'=>'Gelmedi',default=>$appointment->status } }}
                    </span>
                    <a href="{{ route('panel.appointments.show', ['tenant_slug' => $tenant->slug, 'id' => $appointment->id]) }}"
                       class="text-xs text-indigo-600 hover:underline hidden sm:block">Detay</a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endif

@if($view === 'calendar')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
var tenantSlug = '{{ $tenant->slug }}';
var calendar;
var activeStaffIds = new Set([...document.querySelectorAll('.staff-filter-btn')].map(b => parseInt(b.dataset.staffId)));

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = Array.from(document.querySelectorAll('[id="calendar"]')).find(el => el.offsetParent !== null) || document.getElementById('calendar');
    const isMobile = window.innerWidth < 768;

    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'tr',
        initialView: isMobile ? 'timeGridDay' : 'timeGridWeek',
        headerToolbar: isMobile ? {
            left: 'prev,next',
            center: 'title',
            right: 'today'
        } : {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: { today: 'Bugün', month: 'Ay', week: 'Hafta', day: 'Gün' },
        slotMinTime: '07:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        height: isMobile ? 600 : 700,
        slotDuration: '00:30:00',
        nowIndicator: true,
        events: fetchEvents,
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            showModal(info.event);
        },
        dateClick: function(info) {
            window.location.href = `/${tenantSlug}/randevular/yeni?date=${info.dateStr}`;
        },
        eventContent: function(arg) {
            const props = arg.event.extendedProps;
            return {
                html: `<div style="padding:2px 4px; overflow:hidden; height:100%;">
                    <div style="font-weight:600; font-size:11px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${arg.event.title}</div>
                    <div style="font-size:10px; opacity:0.85; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${props.service || ''}</div>
                    <div style="font-size:10px; opacity:0.75; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${props.staff || ''}</div>
                </div>`
            };
        }
    });

    calendar.render();
});

function fetchEvents(info, successCallback, failureCallback) {
    const staffIds = [...activeStaffIds].join(',');
    fetch(`/${tenantSlug}/randevular/events?start=${info.startStr}&end=${info.endStr}&staff_ids=${staffIds}`)
        .then(r => r.json())
        .then(events => {
            const filtered = events.filter(e => activeStaffIds.has(e.extendedProps.staff_id));
            successCallback(filtered);
        })
        .catch(err => failureCallback(err));
}

function toggleStaff(staffId) {
    const btn = document.getElementById(`staffBtn_${staffId}`);
    if (activeStaffIds.has(staffId)) {
        activeStaffIds.delete(staffId);
        btn.classList.remove('active-staff');
        btn.style.background = 'transparent';
    } else {
        activeStaffIds.add(staffId);
        btn.classList.add('active-staff');
        btn.style.background = btn.dataset.color + '18';
    }
    calendar.refetchEvents();
}

function toggleAllStaff() {
    const allBtns = document.querySelectorAll('.staff-filter-btn');
    const allActive = activeStaffIds.size === allBtns.length;
    if (allActive) {
        activeStaffIds.clear();
        allBtns.forEach(btn => {
            btn.classList.remove('active-staff');
            btn.style.background = 'transparent';
        });
    } else {
        allBtns.forEach(btn => {
            const sid = parseInt(btn.dataset.staffId);
            activeStaffIds.add(sid);
            btn.classList.add('active-staff');
            btn.style.background = btn.dataset.color + '18';
        });
    }
    calendar.refetchEvents();
}

function getVisibleModal() {
    const modals = document.querySelectorAll('#appointmentModal');
    // Desktop layout DOM'da önce gelir, mobil layout sonra
    return window.innerWidth < 768 ? modals[modals.length - 1] : modals[0];
}

function showModal(event) {
    const props = event.extendedProps;
    const statusMap = { 'pending':'Bekliyor','confirmed':'Onaylı','completed':'Tamamlandı','cancelled':'İptal','no_show':'Gelmedi' };
    const start = new Date(event.start);
    const end = new Date(event.end);
    const timeStr = start.toLocaleTimeString('tr-TR', {hour:'2-digit',minute:'2-digit'}) + ' - ' + end.toLocaleTimeString('tr-TR', {hour:'2-digit',minute:'2-digit'});
    const dateStr = start.toLocaleDateString('tr-TR', {day:'2-digit',month:'long',year:'numeric'});

    const modal = getVisibleModal();
    modal.querySelector('[id="modalStaffColor"]').style.background = props.staff_color || '#6366F1';
    modal.querySelector('[id="modalContent"]').innerHTML = `
        <div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500">Müşteri</span><span class="font-medium text-gray-900">${props.customer}</span></div>
        <div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500">Hizmet</span><span class="font-medium text-gray-900">${props.service}</span></div>
        <div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500">Personel</span><span class="font-medium" style="color:${props.staff_color}">${props.staff}</span></div>
        <div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500">Tarih</span><span class="font-medium text-gray-900">${dateStr}</span></div>
        <div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500">Saat</span><span class="font-medium text-gray-900">${timeStr}</span></div>
        <div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500">Durum</span><span class="font-medium text-gray-900">${statusMap[props.status] || props.status}</span></div>
        <div class="flex justify-between py-2"><span class="text-gray-500">Ücret</span><span class="font-medium text-gray-900">${parseFloat(props.price).toLocaleString('tr-TR')} TL</span></div>
    `;
    modal.querySelector('[id="modalDetailLink"]').href = event.url;
    getVisibleModal().classList.remove('hidden');
}

function closeModal() {
    getVisibleModal().classList.add('hidden');
}
document.querySelectorAll('#appointmentModal').forEach(function(modal) { modal.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
}); });
</script>
<style>
.fc .fc-timegrid-event { border-radius: 6px !important; border-width: 0 !important; }
.fc .fc-daygrid-event { border-radius: 4px !important; border-width: 0 !important; }
.fc .fc-toolbar-title { font-size: 1rem !important; font-weight: 600 !important; }
.fc .fc-button { font-size: 12px !important; padding: 5px 10px !important; border-radius: 8px !important; }
.fc .fc-button-primary { background: #111 !important; border-color: #111 !important; }
.fc .fc-button-primary:hover { background: #333 !important; }
.fc .fc-button-active { background: #6366F1 !important; border-color: #6366F1 !important; }
.fc .fc-now-indicator-line { border-color: #EF4444 !important; border-width: 2px !important; }
@media (max-width: 768px) {
    .fc .fc-toolbar { flex-wrap: wrap; gap: 8px; }
    .fc .fc-toolbar-title { font-size: 0.9rem !important; }
}
</style>
@endif
@endsection
