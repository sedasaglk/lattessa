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

        .avatar { width: 38px; height: 38px; border-radius: 50%; background: #6366F1; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 14px; flex-shrink: 0; }
    </style>
</head>
<body>

{{-- Header --}}
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

    {{-- Adim Göstergesi --}}
    <div class="step-indicator" id="stepIndicator">
        @if($branches->count() > 1)
        <div class="step active" id="stepEl0">
            <div class="step-circle">1</div>
            <span class="step-label">Şube</span>
        </div>
        @endif
        <div class="step {{ $branches->count() <= 1 ? 'active' : '' }}" id="stepEl1">
            <div class="step-circle">{{ $branches->count() > 1 ? '2' : '1' }}</div>
            <span class="step-label">Hizmet</span>
        </div>
        <div class="step" id="stepEl2">
            <div class="step-circle">{{ $branches->count() > 1 ? '3' : '2' }}</div>
            <span class="step-label">Personel</span>
        </div>
        <div class="step" id="stepEl3">
            <div class="step-circle">{{ $branches->count() > 1 ? '4' : '3' }}</div>
            <span class="step-label">Tarih</span>
        </div>
        <div class="step" id="stepEl4">
            <div class="step-circle">{{ $branches->count() > 1 ? '5' : '4' }}</div>
            <span class="step-label">Saat</span>
        </div>
        <div class="step" id="stepEl5">
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

        {{-- ADIM 0: ŞUBE (sadece birden fazla şube varsa) --}}
        @if($branches->count() > 1)
        <div id="step0" class="fade-in">
            <div class="section-card">
                <p class="section-title">Şube seçin</p>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach($branches as $branch)
                    <div class="service-card" onclick="selectBranch({{ $branch->id }}, '{{ addslashes($branch->name) }}')">
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
        </div>
        @endif

                {{-- ADIM 1: HİZMET --}}
        <div id="step1" @if($branches->count() > 1) style="display:none;" @endif class="fade-in">
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
        @if($branches->count() > 1)
        <button type="button" onclick="goStep(0)" style="width:100%;padding:12px;background:transparent;border:2px solid #E5E7EB;border-radius:12px;font-size:14px;font-weight:500;color:#374151;cursor:pointer;margin-top:4px;">
            ← Geri
        </button>
        @endif
        </div>

        {{-- ADIM 2: PERSONEL --}}
        <div id="step2" style="display:none;" class="fade-in">
            <div class="section-card">
                <p class="section-title">Personel seçin</p>
                <div id="staffList" style="display:flex;flex-direction:column;gap:8px;"></div>
            </div>
            <button type="button" onclick="goStep(hasBranches ? 1 : 1)" style="width:100%;padding:12px;background:transparent;border:2px solid #E5E7EB;border-radius:12px;font-size:14px;font-weight:500;color:#374151;cursor:pointer;margin-top:4px;">
                ← Geri
            </button>
        </div>

        {{-- ADIM 3: TARİH --}}
        <div id="step3" style="display:none;" class="fade-in">
            <div class="section-card">
                <p class="section-title">Tarih seçin</p>
                <input type="date" id="datePickerInput" min="{{ date('Y-m-d') }}" onchange="selectDate(this.value)"
                       style="font-size:16px;">
            </div>
            <button type="button" onclick="goStep(2)" style="width:100%;padding:12px;background:transparent;border:2px solid #E5E7EB;border-radius:12px;font-size:14px;font-weight:500;color:#374151;cursor:pointer;margin-top:4px;">
                ← Geri
            </button>
        </div>

        {{-- ADIM 4: SAAT --}}
        <div id="step4" style="display:none;" class="fade-in">
            <div class="section-card">
                <p class="section-title">Saat seçin</p>
                <div id="slotsList" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
            </div>
            <button type="button" onclick="goStep(3)" style="width:100%;padding:12px;background:transparent;border:2px solid #E5E7EB;border-radius:12px;font-size:14px;font-weight:500;color:#374151;cursor:pointer;margin-top:4px;">
                ← Geri
            </button>
        </div>

        {{-- ADIM 5: BİLGİLER --}}
        <div id="step5" style="display:none;" class="fade-in">
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

            <button type="button" onclick="goStep(4)" style="width:100%;padding:12px;background:transparent;border:2px solid #E5E7EB;border-radius:12px;font-size:14px;font-weight:500;color:#374151;cursor:pointer;margin-top:8px;">
                ← Geri
            </button>
        </div>

    </form>
</div>

<script>
const tenantSlug = '{{ $tenant->slug }}';
const hasBranches = {{ $branches->count() > 1 ? 'true' : 'false' }};
let sel = { branchId: {{ $branches->count() === 1 ? $branches->first()->id : 'null' }}, branchName: '{{ $branches->count() === 1 ? addslashes($branches->first()->name) : '' }}', serviceId: null, serviceName: '', serviceDuration: 0, servicePrice: 0, staffId: null, staffName: '', date: '', time: '' };
let currentStep = hasBranches ? 0 : 1;

function updateStepIndicator(active) {
    const start = hasBranches ? 0 : 1;
    const end = hasBranches ? 5 : 5;
    for (let i = start; i <= end; i++) {
        const el = document.getElementById('stepEl' + i);
        if (!el) continue;
        el.classList.remove('active', 'done');
        if (i < active) el.classList.add('done');
        if (i === active) el.classList.add('active');
        const circle = el.querySelector('.step-circle');
        if (i < active) circle.innerHTML = '✓';
        else circle.innerHTML = hasBranches ? (i + 1) : i;
    }
}

function showStep(n) {
    for (let i = 0; i <= 5; i++) {
        const el = document.getElementById('step' + i);
        if (el) el.style.display = 'none';
    }
    const target = document.getElementById('step' + n);
    if (target) { target.style.display = 'block'; target.classList.add('fade-in'); }
    currentStep = n;
    updateStepIndicator(n);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function goStep(n) { showStep(n); }

function selectBranch(id, name) {
    sel.branchId = id; sel.branchName = name;
    document.getElementById('branchIdInput').value = id;
    document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    setTimeout(() => showStep(1), 200);
}

function selectService(id, name, duration, price) {
    sel.serviceId = id; sel.serviceName = name; sel.serviceDuration = duration; sel.servicePrice = price;
    document.getElementById('serviceIdInput').value = id;
    document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
    event.currentTarget.classList.add('selected');

    // Personel yükle
    document.getElementById('staffList').innerHTML = '<p style="color:#9CA3AF;font-size:13px;">Yükleniyor...</p>';
    showStep(2);

    fetch(`/${tenantSlug}/randevu/personel?service_id=${id}&branch_id=${sel.branchId || ''}`)
        .then(r => r.json())
        .then(staff => {
            const list = document.getElementById('staffList');
            list.innerHTML = '';
            // "Farketmez" seçeneği ekle
            list.innerHTML += `<div class="staff-card" onclick="selectStaff(0, 'Farketmez')">
                <div class="avatar" style="background:#9CA3AF;">?</div>
                <div><p style="font-weight:600;font-size:14px;color:#111;margin:0;">Farketmez</p><p style="font-size:12px;color:#9CA3AF;margin:2px 0 0;">Uygun personeli seç</p></div>
            </div>`;
            staff.forEach(m => {
                list.innerHTML += `<div class="staff-card" onclick="selectStaff(${m.id}, '${m.name.replace(/'/g, "\\'")}')">
                    <div class="avatar">${m.name.charAt(0).toUpperCase()}</div>
                    <div><p style="font-weight:600;font-size:14px;color:#111;margin:0;">${m.name}</p></div>
                </div>`;
            });
        });
}

function selectStaff(id, name) {
    sel.staffId = id; sel.staffName = name;
    document.getElementById('staffIdInput').value = id;
    document.querySelectorAll('.staff-card').forEach(c => c.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    setTimeout(() => showStep(3), 200);
}

function selectDate(date) {
    sel.date = date;
    document.getElementById('dateInput').value = date;
    if (!date) return;

    document.getElementById('slotsList').innerHTML = '<p style="color:#9CA3AF;font-size:13px;">Uygun saatler yükleniyor...</p>';
    showStep(4);

    fetch(`/${tenantSlug}/randevu/saatler?staff_id=${sel.staffId}&service_id=${sel.serviceId}&date=${date}`)
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('slotsList');
            list.innerHTML = '';
            if (!data.slots || data.slots.length === 0) {
                list.innerHTML = '<p style="color:#9CA3AF;font-size:14px;width:100%;">Bu tarihte müsait saat bulunmuyor. Farklı bir tarih seçin.</p>';
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

    // Özet güncelle
    const dateObj = new Date(sel.date + 'T00:00:00');
    const dateStr = dateObj.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric', weekday: 'long' });
    document.getElementById('summaryCard').innerHTML = `
        <p style="font-size:11px;opacity:0.8;margin:0 0 8px;text-transform:uppercase;letter-spacing:0.05em;">Randevu Özeti</p>
        <p style="font-size:18px;font-weight:700;margin:0 0 4px;">${sel.serviceName}</p>
        <p style="font-size:13px;opacity:0.9;margin:0 0 2px;">📅 ${dateStr}</p>
        <p style="font-size:13px;opacity:0.9;margin:0 0 2px;">🕐 ${time} • ⏱ ${sel.serviceDuration} dk</p>
        <p style="font-size:13px;opacity:0.9;margin:0;">👤 ${sel.staffName || 'Personel'}{{ $tenant->show_price_online ? ' • 💰 ${sel.servicePrice.toLocaleString('tr-TR')} ₺' : '' }}</p>
    `;

    setTimeout(() => showStep(5), 200);
}
</script>

</body>
</html>
