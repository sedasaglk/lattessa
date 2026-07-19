<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destek - Lattessa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #F8F8F8; color: #111; }
        .header { background: #111; padding: 20px 24px; }
        .header a { color: #fff; text-decoration: none; font-weight: 700; font-size: 18px; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 24px 60px; }
        h1 { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
        .subtitle { color: #6B7280; font-size: 16px; margin-bottom: 40px; }
        .card { background: #fff; border: 1px solid #E5E7EB; border-radius: 16px; padding: 24px; margin-bottom: 16px; }
        h2 { font-size: 17px; font-weight: 600; margin-bottom: 8px; }
        p { font-size: 15px; line-height: 1.7; color: #374151; }
        a { color: #6366F1; text-decoration: none; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px; }
        @media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }
        .contact-item { display: flex; align-items: center; gap: 12px; padding: 16px; background: #fff; border: 1px solid #E5E7EB; border-radius: 12px; }
        .contact-icon { font-size: 24px; }
        .contact-label { font-size: 12px; color: #9CA3AF; }
        .contact-value { font-size: 15px; font-weight: 600; color: #111; }
        .faq-item { border-bottom: 1px solid #E5E7EB; padding: 16px 0; }
        .faq-item:last-child { border-bottom: none; }
        .faq-q { font-weight: 600; margin-bottom: 6px; }
        .faq-a { color: #6B7280; font-size: 14px; line-height: 1.6; }
    </style>
</head>
<body>
<div class="header">
    <a href="https://lattessa.com">Lattessa</a>
</div>
<div class="container">
    <h1>Destek Merkezi</h1>
    <p class="subtitle">Size nasıl yardımcı olabiliriz?</p>

    <div class="grid">
        <div class="contact-item">
            <span class="contact-icon">📧</span>
            <div>
                <div class="contact-label">E-posta</div>
                <div class="contact-value"><a href="mailto:info@lattessa.com">info@lattessa.com</a></div>
            </div>
        </div>
        <div class="contact-item">
            <span class="contact-icon">🌐</span>
            <div>
                <div class="contact-label">Web</div>
                <div class="contact-value"><a href="https://lattessa.com">lattessa.com</a></div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Sık Sorulan Sorular</h2>
        <div class="faq-item">
            <p class="faq-q">Lattessa nedir?</p>
            <p class="faq-a">Lattessa, kuaför, berber, güzellik merkezi, spa, klinik ve sağlık profesyonelleri için geliştirilmiş kapsamlı bir salon ve klinik yönetim platformudur.</p>
        </div>
        <div class="faq-item">
            <p class="faq-q">Ücretsiz deneme nasıl başlarım?</p>
            <p class="faq-a">lattessa.com/kayit adresinden kaydolarak 14 günlük ücretsiz deneme sürecini başlatabilirsiniz. Kredi kartı gerekmez.</p>
        </div>
        <div class="faq-item">
            <p class="faq-q">Aboneliğimi nasıl yönetirim?</p>
            <p class="faq-a">Abonelik yönetimi lattessa.com üzerinden yapılmaktadır. Tarayıcınızdan hesabınıza giriş yaparak abonelik planınızı görüntüleyebilir ve güncelleyebilirsiniz.</p>
        </div>
        <div class="faq-item">
            <p class="faq-q">Verilerimi nasıl silebilirim?</p>
            <p class="faq-a">Hesap silme talebi için info@lattessa.com adresine e-posta gönderebilirsiniz. Verileriniz 30 gün içinde silinir.</p>
        </div>
        <div class="faq-item">
            <p class="faq-q">Teknik sorun yaşıyorum, ne yapmalıyım?</p>
            <p class="faq-a">info@lattessa.com adresine e-posta gönderin veya panel içindeki Destek bölümünden talep oluşturun. En kısa sürede yanıt vereceğiz.</p>
        </div>
    </div>

    <div class="card">
        <h2>İletişim</h2>
        <p>Sorularınız için <a href="mailto:info@lattessa.com">info@lattessa.com</a> adresine e-posta gönderebilirsiniz. Hafta içi 09:00-18:00 saatleri arasında destek sağlıyoruz.</p>
    </div>
</div>
</body>
</html>
