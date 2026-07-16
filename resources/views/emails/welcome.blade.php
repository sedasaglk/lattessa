@extends('emails.layout')
@section('title', 'Lattessa\'ya Hoş Geldiniz!')
@section('content')

<h1>Hoş Geldiniz, {{ $ownerName }}! 🎉</h1>
<p><strong>{{ $companyName }}</strong> için Lattessa hesabınız başarıyla oluşturuldu. Artık salonunuzu dijital ortamda yönetmeye hazırsınız.</p>

<div class="highlight">
    <h2>⏱ 14 Gün Ücretsiz Deneme</h2>
    <p>Kredi kartı gerekmez. Tüm özellikleri özgürce keşfedin.</p>
</div>

<p><strong>Neler yapabilirsiniz?</strong></p>
<div class="info-card">
    <div class="info-row">
        <span class="info-label">📅 Randevu Yönetimi</span>
        <span class="info-value">Takvim + Online Randevu</span>
    </div>
    <div class="info-row">
        <span class="info-label">👥 Müşteri Takibi</span>
        <span class="info-value">Kart + CRM + Sadakat</span>
    </div>
    <div class="info-row">
        <span class="info-label">👤 Personel Yönetimi</span>
        <span class="info-value">Mesai + Bordro + İzin</span>
    </div>
    <div class="info-row">
        <span class="info-label">💬 WhatsApp Bildirimleri</span>
        <span class="info-value">Otomatik Hatırlatma</span>
    </div>
    <div class="info-row">
        <span class="info-label">💰 Kasa & Raporlar</span>
        <span class="info-value">Gelir + Gider + Analiz</span>
    </div>
</div>

<div style="text-align:center;">
    <a href="{{ $loginUrl }}" class="btn">Panele Giriş Yap →</a>
</div>

<hr class="divider">

<p class="small">Online randevu sayfanız hazır:<br>
<a href="{{ $bookingUrl }}" style="color:#6366F1;">{{ $bookingUrl }}</a><br>
Bu linki sosyal medya profilinize ekleyebilirsiniz.</p>

<p class="small">Sorularınız için panelden <strong>Destek</strong> bölümünü kullanabilirsiniz.</p>

@endsection
