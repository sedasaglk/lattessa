<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #F3F4F6; color: #111; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #111; padding: 28px 32px; display: flex; align-items: center; gap: 12px; }
        .logo { background: #6366F1; width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; font-size: 18px; }
        .brand { color: #fff; font-size: 18px; font-weight: 700; }
        .content { padding: 36px 32px; }
        .footer { background: #F9FAFB; border-top: 1px solid #E5E7EB; padding: 20px 32px; text-align: center; }
        .footer p { color: #9CA3AF; font-size: 12px; line-height: 1.6; }
        .footer a { color: #6366F1; text-decoration: none; }
        h1 { font-size: 22px; font-weight: 700; color: #111; margin-bottom: 12px; }
        p { font-size: 15px; color: #374151; line-height: 1.7; margin-bottom: 12px; }
        .btn { display: inline-block; background: #6366F1; color: #fff !important; padding: 14px 28px; border-radius: 10px; font-weight: 600; font-size: 15px; text-decoration: none; margin: 16px 0; }
        .btn:hover { background: #4F46E5; }
        .btn-dark { background: #111; }
        .info-card { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #E5E7EB; font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #6B7280; }
        .info-value { font-weight: 600; color: #111; }
        .highlight { background: linear-gradient(135deg, #6366F1, #8B5CF6); color: #fff; border-radius: 12px; padding: 20px; margin: 20px 0; text-align: center; }
        .highlight p { color: rgba(255,255,255,0.9); margin: 0; }
        .highlight h2 { color: #fff; font-size: 20px; margin-bottom: 6px; }
        .divider { border: none; border-top: 1px solid #E5E7EB; margin: 24px 0; }
        .small { font-size: 13px; color: #9CA3AF; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <span class="logo">L</span>
        <span class="brand">Lattessa</span>
    </div>
    <div class="content">
        @yield('content')
    </div>
    <div class="footer">
        <p>
            Bu mail <a href="https://lattessa.com">Lattessa</a> tarafından otomatik gönderilmiştir.<br>
            Salon ve Klinik Yönetim Platformu · <a href="https://lattessa.com/gizlilik">Gizlilik Politikası</a>
        </p>
    </div>
</div>
</body>
</html>
