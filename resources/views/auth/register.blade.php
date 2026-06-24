@extends('layouts.guest')

@section('title', 'Kayıt Ol - Lattessa')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-semibold text-gray-900">Lattessa'ya Hos Geldiniz</h1>
        <p class="text-gray-500 mt-2 text-sm">14 gun ucretsiz deneyin, kredi karti gerekmez.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad</label>
            <input type="text" name="owner_name" value="{{ old('owner_name') }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 outline-none text-sm"
                   placeholder="Ahmet Yilmaz" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
            <input type="text" name="phone" value="{{ old('phone') }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 outline-none text-sm"
                   placeholder="05XX XXX XX XX" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 outline-none text-sm"
                   placeholder="ornek@email.com" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Isletme/Sirket Adi</label>
            <input type="text" name="company_name" value="{{ old('company_name') }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 outline-none text-sm"
                   placeholder="Ahmet Kuafor Salonu" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Isletme Turu</label>
            <select name="business_type"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 outline-none text-sm" required>
                <option value="">Seciniz</option>
                <option value="kuafor">Kuafor</option>
                <option value="berber">Berber</option>
                <option value="guzellik_merkezi">Guzellik Merkezi</option>
                <option value="diyetisyen">Diyetisyen</option>
                <option value="psikolog">Psikolog</option>
                <option value="spa">Spa</option>
                <option value="estetik">Estetik Merkezi</option>
                <option value="klinik">Klinik</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sifre</label>
            <input type="password" name="password"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 outline-none text-sm"
                   placeholder="En az 8 karakter" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sifre Tekrar</label>
            <input type="password" name="password_confirmation"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 outline-none text-sm"
                   placeholder="Sifrenizi tekrar girin" required>
        </div>

        <button type="submit"
                class="w-full bg-gray-900 text-white py-3 rounded-lg font-medium text-sm hover:bg-gray-800 transition">
            Ucretsiz Denemeyi Baslat
        </button>
    </form>
</div>
@endsection
