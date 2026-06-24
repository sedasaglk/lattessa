@extends('layouts.panel')
@section('title', 'Personel Duzenle')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.staff.show', ['tenant_slug' => $tenant->slug, 'id' => $member->id]) }}"
       class="text-gray-400 hover:text-gray-900">← Geri</a>
    <h1 class="text-2xl font-semibold text-gray-900">Personel Duzenle</h1>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('panel.staff.update', ['tenant_slug' => $tenant->slug, 'id' => $member->id]) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad</label>
                <input type="text" name="name" value="{{ old('name', $member->name) }}" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $member->email) }}" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                <input type="text" name="phone" value="{{ old('phone', $member->phone) }}"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                <select name="role" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                    @foreach(['personel' => 'Personel', 'sube_muduru' => 'Sube Muduru', 'sekreter' => 'Sekreter', 'muhasebe' => 'Muhasebe', 'firma_sahibi' => 'Firma Sahibi'] as $val => $label)
                        <option value="{{ $val }}" {{ old('role', $member->role) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                    <option value="active" {{ old('status', $member->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status', $member->status) === 'inactive' ? 'selected' : '' }}>Pasif</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sifre (degistirmek icin doldurun)</label>
                <input type="password" name="password"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Komisyon Orani (%)</label>
                <input type="number" name="commission_rate" value="{{ old('commission_rate', $commission->rate ?? 0) }}" min="0" max="100" step="0.5"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sabit Maas (TL)</label>
                <input type="number" name="fixed_salary" value="{{ old('fixed_salary', $commission->fixed_amount ?? 0) }}" min="0" step="0.01"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Guncelle
            </button>
            <a href="{{ route('panel.staff.show', ['tenant_slug' => $tenant->slug, 'id' => $member->id]) }}"
               class="px-6 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                Iptal
            </a>
        </div>
    </form>

    {{-- Silme formu ayri blokta --}}
    <div class="mt-6 pt-6 border-t border-gray-100">
        <p class="text-sm text-gray-500 mb-3">Tehlikeli Alan</p>
        <form method="POST" action="{{ route('panel.staff.destroy', ['tenant_slug' => $tenant->slug, 'id' => $member->id]) }}"
              onsubmit="return confirm('Bu personeli silmek istediginizden emin misiniz? Bu islem geri alinamaz.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-medium text-red-600 border border-red-200 hover:bg-red-50 transition">
                Personeli Sil
            </button>
        </form>
    </div>
</div>
@endsection
