@extends('emails.layout')
@section('title', 'Randevunuz Onaylandı')
@section('content')

<h1>Randevunuz Onaylandı ✓</h1>
<p>Merhaba <strong>{{ $customerName }}</strong>,</p>
<p><strong>{{ $companyName }}</strong> için randevunuz başarıyla oluşturuldu.</p>

<div class="info-card">
    <div class="info-row">
        <span class="info-label">Hizmet</span>
        <span class="info-value">{{ $serviceName }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Personel</span>
        <span class="info-value">{{ $staffName }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Tarih</span>
        <span class="info-value">{{ $date }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Saat</span>
        <span class="info-value">{{ $time }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Ücret</span>
        <span class="info-value">{{ $price }} ₺</span>
    </div>
</div>

<p class="small">Randevunuzu iptal etmek veya değiştirmek için lütfen bizi arayın.</p>
<p class="small">— {{ $companyName }}</p>

@endsection
