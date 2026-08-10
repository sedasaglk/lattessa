<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Online Randevu — {{ $tenant->company_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #F8F8F8; }

        .step-indicator { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 32px; }
        .step { display: flex; flex-direction: column; align-items: center; gap: 6px; flex: 1; position: relative; }
        .step:not(:last-child)::after { content: ''; position: absolute; top: 16px; left: 60%; width: 80%; height: 2px; background: #E5E7EB; z-index: 0; }
        .step:not(:last-child).done::after { background: #6366F1; }
        .step-circle { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; border: 2px solid #E5E7EB; background: #fff; color: #9CA3AF; z-index: 1; transition: all 0.2s; }
        .step.active .step-circle { border-color: #6366F1; background: #6366F1; color: #fff; }
        .step.done .step-circle { border-color: #6366F1; background: #6366F1; color: #fff; }
        .step-label { font-size: 10px; color: #9CA3AF; font-weight: 500; white-space: nowrap; }
        .step.active .step-label { color: #6366F1; font-weight: 600; }
        .step.done .step-label { color: #6366F1; }

        .service-card { border: 2px solid #E5E7EB; border-radius: 14px; padding: 14px 16px; cursor: pointer; transition: all 0.15s; background: #fff; }
        .service-card:hover { border-color: #6366F1; background: #F5F5FF; }
        .service-card.selected { border-color: #6366F1; background: #EEF2FF; }

        .staff-card { border: 2px solid #E5E7EB; border-radius: 14px; padding: 12px 16px; cursor: pointer; transition: all 0.15s; background: #fff; display: flex; align-items: center; gap: 12px; }
        .staff-card:hover { border-color: #6366F1; }
        .staff-card.selected { border-color: #6366F1; background: #EEF2FF; }

        .slot-btn { padding: 8px 14px; border: 2px solid #E5E7EB; border-radius: 10px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.15s; background: #fff; color: #374151; }
        .slot-btn:hover { border-color: #6366F1; color: #6366F1; }
        .slot-btn.selected { border-color: #6366F1; background: #6366F1; color: #fff; }

        .section-card { background: #fff; border-radius: 20px; padding: 20px; border: 1px solid #E5E7EB; margin-bottom: 12px; }
        .section-title { font-size: 15px; font-weight: 600; color: #111; margin-bottom: 14px; display: flex; align-items: center; gap-8px; }

        input[type="text"], input[type="tel"], input[type="date"], textarea {
            width: 100%; padding: 12px 16px; border: 2px solid #E5E7EB; border-radius: 12px;
            font-size: 14px; font-family: 'Inter', sans-serif; outline: none; transition: border-color 0.15s;
            background: #fff;
        }
        input:focus, textarea:focus { border-color: #6366F1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }

        .btn-primary { width: 100%; padding: 14px; background: #111; color: #fff; border-radius: 14px; font-weight: 600; font-size: 15px; border: none; cursor: pointer; transition: all 0.15s; }
        .btn-primary:hover { background: #333; }
        .btn-primary:disabled { background: #9CA3AF; cursor: not-allowed; }

        .summary-card { background: linear-gradient(135deg, #6366F1, #8B5CF6); border-radius: 16px; padding: 16px; color: #fff; margin-bottom: 16px; }

        .alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 12px; padding: 12px 16px; font-size: 14px; margin-bottom: 16px; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.2s ease; }

        .cal-day { aspect-ratio:1; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:500; cursor:pointer; transition:all 0.15s; border:2px solid transparent; }
        .cal-day:hover:not(.cal-disabled):not(.cal-selected) { border-color:#6366F1; color:#6366F1; background:#EEF2FF; }
        .cal-day.cal-today:not(.cal-selected) { background:#F3F4F6; color:#111; font-weight:700; }
        .cal-day.cal-selected { background:#6366F1; color:#fff; border-color:#6366F1; font-weight:700; }
        .cal-day.cal-disabled { color:#D1D5DB; cursor:not-allowed; }
        .cal-day.cal-empty { cursor:default; }

        .avatar { width: 38px; height: 38px; border-radius: 50%; background: #6366F1; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 14px; flex-shrink: 0; }
    </style>
</head>
<body>

{{-- Header --}}
@auth
<div style="background:#4F46E5;padding:10px 16px;text-align:center;">
    <a href="{{ route('panel.appointments.index', ['tenant_slug' => $tenant->slug]) }}" style="color:#fff;font-size:13px;text-decoration:none;">← Panele Dön</a>
</div>
@endauth
<div style="background:#111; padding: 20px 16px 20px; padding-top: calc(20px + env(safe-area-inset-top));">
    <div style="max-width:480px; margin:0 auto; display:flex; align-items:center; gap:12px;">
        <div style="width:40px;height:40px;border-radius:10px;background:#6366F1;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:16px;flex-shrink:0;">
            {{ strtoupper(substr($tenant->company_name, 0, 1)) }}
        </div>
        <div>
            <p style="color:#fff;font-weight:600;font-size:15px;margin:0;">{{ $tenant->company_name }}</p>
            <p style="color:#9CA3AF;font-size:12px;margin:0;">Online Randevu</p>
        </div>
    </div>
</div>


{{-- Salon Fotoğrafları --}}
@if($photos->count())
<div style="max-width:480px;margin:0 auto;overflow:hidden;">
    <div style="display:flex;overflow-x:auto;scroll-snap-type:x mandatory;gap:0;scrollbar-width:none;" id="photoSlider">
        @foreach($photos as $photo)
        <div style="min-width:100%;scroll-snap-align:start;">
            <img src="{{ '/' . $photo->path }}" style="width:100%;height:220px;object-fit:cover;" alt="Salon">
        </div>
        @endforeach
    </div>
    @if($photos->count() > 1)
    <div style="display:flex;justify-content:center;gap:6px;padding:8px 0;background:#111;">
        @foreach($photos as $i => $photo)
        <div style="width:6px;height:6px;border-radius:50%;background:{{ $loop->first ? '#fff' : '#555' }};transition:.3s;" class="dot"></div>
        @endforeach
    </div>
    @endif
</div>
@endif

{{-- Ortalama Puan --}}
@if($avgRating)
<div style="max-width:480px;margin:0 auto;padding:12px 16px;background:#111;border-bottom:1px solid #222;">
    <div style="display:flex;align-items:center;gap:8px;">
        <span style="color:#FBBF24;font-size:18px;">★</span>
        <span style="color:#fff;font-weight:600;font-size:15px;">{{ $avgRating }}</span>
        <span style="color:#9CA3AF;font-size:13px;">{{ $reviews->count() }} değerlendirme</span>
    </div>
</div>
@endif

<div style="max-width:480px; margin:0 auto; padding:20px 16px 40px;">

    {{-- Hatalar --}}
    @if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="alert-error">
        @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
    </div>
    @endif

    {{-- Adim Göstergesi: Hizmet → Şube → Personel → Tarih → Saat → Bilgi --}}
    <div class="step-indicator" id="stepIndicator">
        <div class="step active" id="stepEl1">
            <div class="step-circle">1</div>
            <span class="step-label">Hizmet</span>
        </div>
        @if($branches->count() > 1)
        <div class="step" id="stepEl2">
            <div class="step-circle">2</div>
            <span class="step-label">Şube</span>
        </div>
        @endif
        <div class="step" id="stepEl3">
            <div class="step-circle">{{ $branches->count() > 1 ? '3' : '2' }}</div>
            <span class="step-label">Personel</span>
        </div>
        <div class="step" id="stepEl4">
            <div class="step-circle">{{ $branches->count() > 1 ? '4' : '3' }}</div>
            <span class="step-label">Tarih</span>
        </div>
        <div class="step" id="stepEl5">
            <div class="step-circle">{{ $branches->count() > 1 ? '5' : '4' }}</div>
            <span class="step-label">Saat</span>
        </div>
        <div class="step" id="stepEl6">
            <div class="step-circle">{{ $branches->count() > 1 ? '6' : '5' }}</div>
            <span class="step-label">Bilgi</span>
        </div>
    </div>

    <form method="POST" action="{{ route('booking.store', ['tenant_slug' => $tenant->slug]) }}" id="bookingForm">
        @csrf
        <input type="hidden" name="branch_id" id="branchIdInput" value="{{ $branches->count() === 1 ? $branches->first()->id : '' }}">
        <input type="hidden" name="service_id" id="serviceIdInput">
        <input type="hidden" name="staff_id" id="staffIdInput">
        <input type="hidden" name="date" id="dateInput">
        <input type="hidden" name="time" id="timeInput">

        {{-- ADIM 1: HİZMET --}}
        <div id="step1" class="fade-in">
            <div class="section-card">
                <p class="section-title">Hangi hizmeti istiyorsunuz?</p>
                @if($services->isEmpty())
                    <p style="color:#9CA3AF;font-size:14px;">Henüz online rezervasyona açık hizmet bulunmuyor.</p>
                @else
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($services as $service)
                        <div class="service-card" onclick="selectService({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->duration_minutes }}, {{ $service->price }})">
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <div>
                                    <p style="font-weight:600;font-size:14px;color:#111;margin:0;">{{ $service->name }}</p>
                                    <p style="font-size:12px;color:#9CA3AF;margin:4px 0 0;">⏱ {{ $service->duration_minutes }} dakika</p>
                                </div>
                                @if($tenant->show_price_online)
                                <div style="text-align:right;">
                                    <p style="font-weight:700;font-size:16px;color:#6366F1;margin:0;">{{ number_format($service->price, 0, ',', '.') }} ₺</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ADIM 2: ŞUBE (sadece birden fazla şube varsa) --}}
        @if($branches->count() > 1)
        <div id="step2" style="display:none;" class="fade-in">
            <div class="section-card">
                <p class="section-title">Şube seçin</p>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach($branches as $branch)
                    <div class="service-card branch-card" data-id="{{ $branch->id }}" data-name="{{ addslashes($branch->name) }}"
                         onclick="selectBranch({{ $branch->id }}, '{{ addslashes($branch->name) }}', this)">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:36px;height:36px;border-radius:8px;background:#6366F1;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0;">
                                {{ strtoupper(substr($branch->name, 0, 1)) }}
                            </div>
                            <div>
                                <p style="font-weight:600;font-size:14px;color:#111;margin:0;">{{ $branch->name }}</p>
                                @if($branch->address)<p style="font-size:12px;color:#9CA3AF;margin:2px 0 0;">📍 {{ $branch->address }}</p>@endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <button type="button" onclick="goStep(1)" style="width:100%;padding:12px;background:transparent;border:2px solid #E5E7EB;border-radius:12px;font-size:14px;font-weight:500;color:#374151;cursor:pointer;margin-top:4px;">
                ← Geri
            </button>
        </div>
        @endif

        {{-- ADIM 3: PERSONEL --}}
        <div id="step3" style="display:none;" class="fade-in">
            <div class="section-card">
                <p class="section-title">Personel seçin</p>
                <div id="staffList" style="display:flex;flex-direction:column;gap:8px;"></div>
            </div>
            <button type="button" onclick="goStep(hasBranches ? 2 : 1)" style="width:100%;padding:12px;background:transparent;border:2px solid #E5E7EB;border-radius:12px;font-size:14px;font-weight:500;color:#374151;cursor:pointer;margin-top:4px;">
                ← Geri
            </button>
        </div>

        {{-- ADIM 4: TARİH --}}
        <div id="step4" style="display:none;" class="fade-in">
            <div class="section-card">
                <p class="section-title">Tarih seçin</p>

                {{-- Modern Takvim --}}
                <div id="calendarWidget" style="user-select:none;">
                    <!-- Nav -->
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                        <button type="button" onclick="calPrev()" style="width:36px;height:36px;border-radius:50%;border:1px solid #E5E7EB;background:#fff;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;color:#374151;">‹</button>
                        <span id="calMonthLabel" style="font-size:15px;font-weight:600;color:#111;"></span>
                        <button type="button" onclick="calNext()" style="width:36px;height:36px;border-radius:50%;border:1px solid #E5E7EB;background:#fff;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;color:#374151;">›</button>
                    </div>
                    <!-- Gün başlıkları -->
                    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:6px;">
                        @foreach(['Pt','Sa','Ça','Pe','Cu','Ct','Pz'] as $d)
                        <div style="text-align:center;font-size:11px;font-weight:600;color:#9CA3AF;padding:4px 0;">{{ $d }}</div>
                        @endforeach
                    </div>
                    <!-- Günler -->
                    <div id="calDays" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;"></div>
                </div>

                <input type="hidden" id="datePickerInput">
            </div>
            <button type="button" onclick="goStep(3)" style="width:100%;padding:12px;background:transparent;border:2px solid #E5E7EB;border-radius:12px;font-size:14px;font-weight:500;color:#374151;cursor:pointer;margin-top:4px;">
                ← Geri
            </button>
        </div>

        {{-- ADIM 5: SAAT --}}
        <div id="step5" style="display:none;" class="fade-in">
            <div class="section-card">
                <p class="section-title">Saat seçin</p>
                <div id="slotsList" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
            </div>
            <button type="button" onclick="goStep(4)" style="width:100%;padding:12px;background:transparent;border:2px solid #E5E7EB;border-radius:12px;font-size:14px;font-weight:500;color:#374151;cursor:pointer;margin-top:4px;">
                ← Geri
            </button>
        </div>

        {{-- ADIM 6: BİLGİLER --}}
        <div id="step6" style="display:none;" class="fade-in">
            {{-- Özet --}}
            <div class="summary-card" id="summaryCard"></div>

            <div class="section-card">
                <p class="section-title">Bilgileriniz</p>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Ad Soyad *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Adınız Soyadınız" required>
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Telefon *</label>
                        <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="05XX XXX XX XX" required>
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">E-posta (opsiyonel)</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" placeholder="ornek@mail.com">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Not (opsiyonel)</label>
                        <textarea name="customer_notes" rows="2" placeholder="Eklemek istediğiniz notlar...">{{ old('customer_notes') }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                ✓ Randevu Oluştur
            </button>

            <button type="button" onclick="goStep(5)" style="width:100%;padding:12px;background:transparent;border:2px solid #E5E7EB;border-radius:12px;font-size:14px;font-weight:500;color:#374151;cursor:pointer;margin-top:8px;">
                ← Geri
            </button>
        </div>

    </form>
</div>

<script>
const tenantSlug = '{{ $tenant->slug }}';
const hasBranches = {{ $branches->count() > 1 ? 'true' : 'false' }};
const showPriceOnline = {{ $tenant->show_price_online ? 'true' : 'false' }};
// URL'den gelen şube veya tek şube otomatik seçilir
@isset($selectedBranch)
const _preselectedBranch = { id: {{ $selectedBranch->id }}, name: '{{ addslashes($selectedBranch->name) }}' };
@else
const _preselectedBranch = null;
@endisset
let sel = {
    branchId: _preselectedBranch ? _preselectedBranch.id : {{ $branches->count() === 1 ? $branches->first()->id : 'null' }},
    branchName: _preselectedBranch ? _preselectedBranch.name : '{{ $branches->count() === 1 ? addslashes($branches->first()->name) : '' }}',
    serviceId: null, serviceName: '', serviceDuration: 0, servicePrice: 0,
    staffId: null, staffName: '', date: '', time: ''
};
let currentStep = 1;

// Adım göstergesi: 1=Hizmet, 2=Şube(opt), 3=Personel, 4=Tarih, 5=Saat, 6=Bilgi
function updateStepIndicator(active) {
    [1,2,3,4,5,6].forEach(i => {
        const el = document.getElementById('stepEl' + i);
        if (!el) return;
        el.classList.remove('active', 'done');
        const circle = el.querySelector('.step-circle');
        if (i < active) { el.classList.add('done'); circle.innerHTML = '✓'; }
        else if (i === active) { el.classList.add('active'); circle.innerHTML = circle.dataset.num || circle.innerHTML; }
    });
}

function showStep(n) {
    [1,2,3,4,5,6].forEach(i => {
        const el = document.getElementById('step' + i);
        if (el) el.style.display = 'none';
    });
    const target = document.getElementById('step' + n);
    if (target) { target.style.display = 'block'; target.classList.add('fade-in'); }
    currentStep = n;
    updateStepIndicator(n);
    window.scrollTo({ top: 0, behavior: 'smooth' });
    if (n === 4) initCalendar();
}

function goStep(n) { showStep(n); }

function selectService(id, name, duration, price) {
    sel.serviceId = id; sel.serviceName = name; sel.serviceDuration = duration; sel.servicePrice = price;
    document.getElementById('serviceIdInput').value = id;
    document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    // Şube URL'den geliyorsa veya tek şube varsa direkt personele geç
    const skipBranch = !hasBranches || !!_preselectedBranch;
    if (_preselectedBranch) {
        document.getElementById('branchIdInput').value = _preselectedBranch.id;
    }
    setTimeout(() => skipBranch ? loadStaff() : showStep(2), 200);
}

// ── Takvim ──────────────────────────────────────────────────────────────
var calYear, calMonth;
var today = new Date(); today.setHours(0,0,0,0);

var MONTH_TR = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];

function initCalendar() {
    var d = new Date();
    calYear = d.getFullYear();
    calMonth = d.getMonth();
    renderCalendar();
}

function renderCalendar() {
    document.getElementById('calMonthLabel').textContent = MONTH_TR[calMonth] + ' ' + calYear;
    var grid = document.getElementById('calDays');
    grid.innerHTML = '';

    var first = new Date(calYear, calMonth, 1);
    var lastDay = new Date(calYear, calMonth + 1, 0).getDate();
    // Pazartesi=0 başlangıç: getDay() 0=Pazar→6, 1=Pzt→0, ...
    var startDow = (first.getDay() + 6) % 7;

    // Boş hücreler
    for (var i = 0; i < startDow; i++) {
        var empty = document.createElement('div');
        empty.className = 'cal-day cal-empty';
        grid.appendChild(empty);
    }

    var selDate = document.getElementById('datePickerInput').value;
    var todayStr = formatDate(today);

    for (var d2 = 1; d2 <= lastDay; d2++) {
        var dt = new Date(calYear, calMonth, d2);
        dt.setHours(0,0,0,0);
        var dtStr = formatDate(dt);
        var isPast = dt < today;
        var isToday = dtStr === todayStr;
        var isSel = dtStr === selDate;

        var cell = document.createElement('div');
        cell.className = 'cal-day' + (isPast ? ' cal-disabled' : '') + (isToday ? ' cal-today' : '') + (isSel ? ' cal-selected' : '');
        cell.textContent = d2;

        if (!isPast) {
            (function(s) {
                cell.onclick = function() { selectDate(s); };
            })(dtStr);
        }
        grid.appendChild(cell);
    }
}

function formatDate(d) {
    var m = String(d.getMonth()+1).padStart(2,'0');
    var day = String(d.getDate()).padStart(2,'0');
    return d.getFullYear() + '-' + m + '-' + day;
}

function calPrev() {
    calMonth--;
    if (calMonth < 0) { calMonth = 11; calYear--; }
    // Geçmiş aya gitmeyi engelle
    var now = new Date();
    if (calYear < now.getFullYear() || (calYear === now.getFullYear() && calMonth < now.getMonth())) {
        calMonth++; if (calMonth > 11) { calMonth = 0; calYear++; }
        return;
    }
    renderCalendar();
}

function calNext() {
    calMonth++;
    if (calMonth > 11) { calMonth = 0; calYear++; }
    // Max 3 ay ileriye
    var maxDate = new Date(); maxDate.setMonth(maxDate.getMonth() + 3);
    if (new Date(calYear, calMonth, 1) > maxDate) {
        calMonth--; if (calMonth < 0) { calMonth = 11; calYear--; }
        return;
    }
    renderCalendar();
}
// ────────────────────────────────────────────────────────────────────────

function selectDate(date) {
    if (!date) return;
    sel.date = date;
    document.getElementById('dateInput').value = date;
    document.getElementById('datePickerInput').value = date;
    renderCalendar(); // seçili günü highlight et
    // Personel zaten seçili, direkt slotları yükle
    setTimeout(function() { loadSlots(); }, 300);
}

function selectBranch(id, name, el) {
    sel.branchId = id; sel.branchName = name;
    document.getElementById('branchIdInput').value = id;
    document.querySelectorAll('.branch-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    setTimeout(() => loadStaff(), 200);
}

function loadStaff() {
    document.getElementById('staffList').innerHTML = '<p style="color:#9CA3AF;font-size:13px;">Yükleniyor...</p>';
    showStep(3);

    fetch(`/${tenantSlug}/randevu/personel?service_id=${sel.serviceId}&branch_id=${sel.branchId || ''}`)
        .then(r => r.json())
        .then(staff => {
            const list = document.getElementById('staffList');
            list.innerHTML = '';
            if (!staff || staff.length === 0) {
                list.innerHTML = '<p style="color:#9CA3AF;font-size:14px;">Uygun personel bulunamadı.</p>';
                return;
            }
            staff.forEach(m => {
                const card = document.createElement('div');
                card.className = 'staff-card';
                card.innerHTML = `<div class="avatar">${m.name.charAt(0).toUpperCase()}</div><div><p style="font-weight:600;font-size:14px;color:#111;margin:0;">${m.name}</p></div>`;
                card.onclick = () => selectStaff(m.id, m.name, card);
                list.appendChild(card);
            });
        });
}

function selectStaff(id, name, el) {
    sel.staffId = id; sel.staffName = name;
    document.getElementById('staffIdInput').value = id;
    document.querySelectorAll('.staff-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    // Takvimi göster
    showStep(4);
}

function loadSlots() {
    document.getElementById('slotsList').innerHTML = '<p style="color:#9CA3AF;font-size:13px;">Uygun saatler yükleniyor...</p>';
    showStep(5);

    fetch(`/${tenantSlug}/randevu/saatler?staff_id=${sel.staffId}&service_id=${sel.serviceId}&date=${sel.date}`)
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('slotsList');
            list.innerHTML = '';
            if (!data.slots || data.slots.length === 0) {
                list.innerHTML = '<p style="color:#9CA3AF;font-size:14px;width:100%;">Bu tarihte müsait saat bulunmuyor. Geri dönüp farklı bir tarih seçin.</p>';
                return;
            }
            data.slots.forEach(slot => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'slot-btn';
                btn.textContent = slot;
                btn.onclick = () => selectSlot(slot, btn);
                list.appendChild(btn);
            });
        });
}

function selectSlot(time, btn) {
    sel.time = time;
    document.getElementById('timeInput').value = time;
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');

    const dateObj = new Date(sel.date + 'T00:00:00');
    const dateStr = dateObj.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric', weekday: 'long' });
    document.getElementById('summaryCard').innerHTML = `
        <p style="font-size:11px;opacity:0.8;margin:0 0 8px;text-transform:uppercase;letter-spacing:0.05em;">Randevu Özeti</p>
        <p style="font-size:18px;font-weight:700;margin:0 0 4px;">${sel.serviceName}</p>
        <p style="font-size:13px;opacity:0.9;margin:0 0 2px;">📅 ${dateStr}</p>
        <p style="font-size:13px;opacity:0.9;margin:0 0 2px;">🕐 ${time} • ⏱ ${sel.serviceDuration} dk</p>
        <p style="font-size:13px;opacity:0.9;margin:0;">👤 ${sel.staffName}${hasBranches ? ' • 🏢 ' + sel.branchName : ''}${showPriceOnline ? ' • 💰 ' + sel.servicePrice.toLocaleString('tr-TR') + ' ₺' : ''}</p>
    `;

    setTimeout(() => showStep(6), 200);
}
</script>


{{-- Müşteri Yorumları --}}
@if($reviews->count())
<div style="max-width:480px;margin:16px auto 0;padding:0 16px;">
    <h3 style="font-size:15px;font-weight:600;color:#111;margin-bottom:12px;">Müşteri Yorumları</h3>
    <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($reviews as $review)
        <div style="background:#fff;border-radius:12px;padding:14px;border:1px solid #E5E7EB;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <span style="font-weight:500;font-size:13px;color:#111;">{{ $review->customer_name }}</span>
                <span style="color:#FBBF24;font-size:13px;">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
            </div>
            @if($review->comment)
            <p style="font-size:13px;color:#6B7280;margin:0;">{{ $review->comment }}</p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

</body>
</html>
