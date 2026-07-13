<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Randevu Alındı — {{ $tenant->company_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #F8F8F8; min-height: 100vh; display: flex; flex-direction: column; }
        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.1); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        .bounce-in { animation: bounceIn 0.6s ease; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up { animation: fadeUp 0.4s ease 0.3s both; }
    </style>
</head>
<body>

<div style="background:#111; padding: 20px 16px; padding-top: calc(20px + env(safe-area-inset-top));">
    <div style="max-width:480px; margin:0 auto; display:flex; align-items:center; gap:12px;">
        <div style="width:40px;height:40px;border-radius:10px;background:#6366F1;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:16px;">
            {{ strtoupper(substr($tenant->company_name, 0, 1)) }}
        </div>
        <div>
            <p style="color:#fff;font-weight:600;font-size:15px;margin:0;">{{ $tenant->company_name }}</p>
            <p style="color:#9CA3AF;font-size:12px;margin:0;">Online Randevu</p>
        </div>
    </div>
</div>

<div style="max-width:480px; margin:0 auto; padding:40px 16px; flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center;">

    {{-- Başarı İkonu --}}
    <div class="bounce-in" style="width:80px;height:80px;border-radius:50%;background:#DCFCE7;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
        <span style="font-size:40px;">✓</span>
    </div>

    <div class="fade-up" style="text-align:center; width:100%;">
        <h1 style="font-size:24px;font-weight:700;color:#111;margin:0 0 8px;">Randevunuz Alındı!</h1>
        <p style="font-size:14px;color:#6B7280;margin:0 0 28px;">Randevunuz onaylandığında WhatsApp veya SMS ile bilgilendirileceksiniz.</p>

        @if(session('booking_success'))
        @php $b = session('booking_success'); @endphp
        <div style="background:linear-gradient(135deg,#6366F1,#8B5CF6);border-radius:20px;padding:20px;color:#fff;margin-bottom:24px;text-align:left;">
            <p style="font-size:11px;opacity:0.8;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">Randevu Detayları</p>
            <p style="font-size:20px;font-weight:700;margin:0 0 6px;">{{ $b['service_name'] }}</p>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <p style="font-size:13px;opacity:0.9;margin:0;">👤 {{ $b['customer_name'] }}</p>
                <p style="font-size:13px;opacity:0.9;margin:0;">📅 {{ \Carbon\Carbon::parse($b['date'])->format('d F Y, l') }}</p>
                <p style="font-size:13px;opacity:0.9;margin:0;">🕐 {{ $b['time'] }}</p>
            </div>
        </div>
        @endif

        <div style="background:#fff;border:1px solid #E5E7EB;border-radius:16px;padding:16px;margin-bottom:16px;text-align:left;">
            <p style="font-size:13px;color:#374151;margin:0;line-height:1.6;">
                📱 Randevunuzu iptal etmek veya değiştirmek için lütfen bizi arayın.
            </p>
        </div>

        <a href="{{ route('booking.show', ['tenant_slug' => $tenant->slug]) }}"
           style="display:block;width:100%;padding:14px;background:#111;color:#fff;border-radius:14px;font-weight:600;font-size:15px;text-decoration:none;text-align:center;margin-bottom:10px;">
            + Yeni Randevu Al
        </a>

        <a href="/"
           style="display:block;width:100%;padding:14px;background:transparent;color:#374151;border:2px solid #E5E7EB;border-radius:14px;font-weight:500;font-size:14px;text-decoration:none;text-align:center;">
            Ana Sayfaya Dön
        </a>
    </div>
</div>

</body>
</html>
