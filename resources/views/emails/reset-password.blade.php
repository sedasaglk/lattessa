@extends('emails.layout')
@section('title', 'Şifre Sıfırlama')
@section('content')

<h1>Şifre Sıfırlama</h1>
<p>Merhaba <strong>{{ $name }}</strong>,</p>
<p>Lattessa hesabınız için şifre sıfırlama talebinde bulundunuz. Aşağıdaki butona tıklayarak yeni şifrenizi belirleyebilirsiniz.</p>

<div style="text-align:center;">
    <a href="{{ $resetUrl }}" class="btn">Şifremi Sıfırla →</a>
</div>

<div class="info-card">
    <p style="margin:0; font-size:14px; color:#6B7280;">
        ⏱ Bu link <strong>{{ $expiresIn }}</strong> geçerlidir.<br>
        🔒 Güvenliğiniz için link yalnızca bir kez kullanılabilir.
    </p>
</div>

<p class="small">Bu talebi siz yapmadıysanız bu maili görmezden gelebilirsiniz. Hesabınız güvende.</p>

@endsection
