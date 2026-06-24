<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f9fafb; margin: 0; padding: 20px; }
        .container { max-width: 560px; margin: 0 auto; background: white; border-radius: 12px; padding: 40px; border: 1px solid #e5e7eb; }
        .logo { font-size: 22px; font-weight: 700; color: #111827; margin-bottom: 32px; }
        h1 { font-size: 20px; color: #111827; margin-bottom: 16px; }
        p { color: #6b7280; font-size: 15px; line-height: 1.6; margin-bottom: 16px; }
        .highlight { background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 16px; margin: 24px 0; }
        .highlight p { color: #92400e; margin: 0; font-weight: 600; }
        .btn { display: inline-block; background: #111827; color: white; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px; }
        .footer { margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 13px; }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">Lattessa</div>
    <h1>Merhaba {{ $ownerName }},</h1>
    <p>{{ $companyName }} isletmenizin Lattessa ucretsiz deneme suresi <strong>{{ $daysLeft }} gun</strong> sonra sona erecek.</p>
    <div class="highlight">
        <p>Deneme sureniz {{ $daysLeft }} gun sonra bitiyor!</p>
    </div>
    <p>Verilerinizi kaybetmemek ve kesintisiz kullanmaya devam etmek icin bir paket secin.</p>
    <a href="{{ config('app.url') }}/{{ $tenantSlug }}/giris" class="btn">Panele Git</a>
    <p style="margin-top: 24px;">Sorulariniz icin <a href="mailto:destek@lattessa.com">destek@lattessa.com</a></p>
    <div class="footer">
        <p>Lattessa - Salon ve Klinik Yonetim Yazilimi</p>
    </div>
</div>
</body>
</html>
