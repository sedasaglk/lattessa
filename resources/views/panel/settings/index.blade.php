@extends('layouts.panel')
@section('title', 'Ayarlar')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Ayarlar</h1>
</div>

<div class="space-y-6">

    {{-- Isletme Bilgileri --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Isletme Bilgileri</h2>

        <form method="POST" action="{{ route('panel.settings.business', ['tenant_slug' => $tenant->slug]) }}" class="space-y-4 max-w-2xl">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Isletme Adi</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $tenant->company_name) }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Yetkili Adi</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name', $tenant->owner_name) }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Isletme Turu</label>
                    <select name="business_type" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                        @foreach([
                            'kuafor' => 'Kuafor',
                            'berber' => 'Berber',
                            'guzellik_merkezi' => 'Guzellik Merkezi',
                            'diyetisyen' => 'Diyetisyen',
                            'psikolog' => 'Psikolog',
                            'spa' => 'Spa',
                            'estetik' => 'Estetik Merkezi',
                            'klinik' => 'Klinik',
                        ] as $value => $label)
                        <option value="{{ $value }}" {{ old('business_type', $tenant->business_type) === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Saat Dilimi</label>
                    <select name="timezone" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                        <option value="Europe/Istanbul" {{ old('timezone', $tenant->timezone) === 'Europe/Istanbul' ? 'selected' : '' }}>
                            Turkiye (Europe/Istanbul)
                        </option>
                        <option value="UTC" {{ old('timezone', $tenant->timezone) === 'UTC' ? 'selected' : '' }}>UTC</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Online Randevu Linki</label>
                    <div class="flex items-center gap-2">
                        <input type="text" value="{{ config('app.url') }}/{{ $tenant->slug }}/randevu" readonly
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 text-sm">
                        <button type="button" onclick="navigator.clipboard.writeText('{{ config('app.url') }}/{{ $tenant->slug }}/randevu')"
                                class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 whitespace-nowrap">
                            Kopyala
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Kaydet
            </button>
        </form>
    </div>

    {{-- Sube ve Calisma Saatleri --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Sube Bilgileri ve Calisma Saatleri</h2>

        <form method="POST" action="{{ route('panel.settings.branch', ['tenant_slug' => $tenant->slug]) }}" class="space-y-4 max-w-2xl">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sube Adi</label>
                    <input type="text" name="branch_name" value="{{ old('branch_name', $branch->name ?? 'Merkez Sube') }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sube Telefonu</label>
                    <input type="text" name="branch_phone" value="{{ old('branch_phone', $branch->phone ?? '') }}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
                <textarea name="branch_address" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">{{ old('branch_address', $branch->address ?? '') }}</textarea>
            </div>

            {{-- Calisma Saatleri --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Calisma Saatleri</label>
                <div class="space-y-2">
                    @foreach($workingHours as $dayKey => $day)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <label class="flex items-center gap-2 w-32">
                            <input type="checkbox" name="days[{{ $dayKey }}][is_open]" value="1"
                                   {{ $day['is_open'] ? 'checked' : '' }}
                                   class="rounded border-gray-300">
                            <span class="text-sm text-gray-700">{{ $day['name'] }}</span>
                        </label>
                        <div class="flex items-center gap-2 flex-1">
                            <input type="time" name="days[{{ $dayKey }}][start]" value="{{ $day['start'] }}"
                                   class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                            <span class="text-gray-400 text-sm">-</span>
                            <input type="time" name="days[{{ $dayKey }}][end]" value="{{ $day['end'] }}"
                                   class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Kaydet
            </button>
        </form>
    </div>

    {{-- Sifre Degistir --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Sifre Degistir</h2>

        <form method="POST" action="{{ route('panel.settings.password', ['tenant_slug' => $tenant->slug]) }}" class="space-y-4 max-w-md">
            @csrf
            @method('PUT')

            @if($errors->has('current_password'))
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    {{ $errors->first('current_password') }}
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mevcut Sifre</label>
                <input type="password" name="current_password" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Yeni Sifre</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm"
                       placeholder="En az 8 karakter">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Yeni Sifre Tekrar</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            </div>

            <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Sifreyi Guncelle
            </button>
        </form>
    </div>

    {{-- Hesap Bilgileri --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Hesap Bilgileri</h2>
        <div class="grid grid-cols-2 gap-4 max-w-md text-sm">
            <div>
                <p class="text-gray-500">Firma Slug</p>
                <p class="font-medium text-gray-900">{{ $tenant->slug }}</p>
            </div>
            <div>
                <p class="text-gray-500">Hesap Durumu</p>
                <p class="font-medium {{ $tenant->status === 'trial' ? 'text-amber-600' : 'text-green-600' }}">
                    {{ $tenant->status === 'trial' ? 'Deneme' : 'Aktif' }}
                </p>
            </div>
            <div>
                <p class="text-gray-500">Referans Kodunuz</p>
                <p class="font-medium text-gray-900">{{ $tenant->referral_code }}</p>
            </div>
            <div>
                <p class="text-gray-500">Kayit Tarihi</p>
                <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($tenant->created_at)->format('d.m.Y') }}</p>
            </div>
            @if($tenant->status === 'trial' && $tenant->trial_ends_at)
            <div>
                <p class="text-gray-500">Deneme Bitis</p>
                <p class="font-medium text-amber-600">{{ \Carbon\Carbon::parse($tenant->trial_ends_at)->format('d.m.Y') }}</p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
