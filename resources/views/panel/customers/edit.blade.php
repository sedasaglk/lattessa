@extends('layouts.panel')
@section('title', 'Musteri Duzenle')
@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.customers.show', ['tenant_slug' => $tenant->slug, 'id' => $customer->id]) }}"
       class="text-gray-400 hover:text-gray-900">← Geri</a>
    <h1 class="text-2xl font-semibold text-gray-900">Musteri Duzenle</h1>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
    <form method="POST" action="{{ route('panel.customers.update', ['tenant_slug' => $tenant->slug, 'id' => $customer->id]) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad</label>
            <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
            <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
            <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dogum Tarihi</label>
            <input type="date" name="birth_date" value="{{ old('birth_date', $customer->birth_date?->format('Y-m-d')) }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cinsiyet</label>
            <select name="gender" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                <option value="">Belirtilmemis</option>
                <option value="female" {{ old('gender', $customer->gender) == 'female' ? 'selected' : '' }}>Kadin</option>
                <option value="male" {{ old('gender', $customer->gender) == 'male' ? 'selected' : '' }}>Erkek</option>
                <option value="other" {{ old('gender', $customer->gender) == 'other' ? 'selected' : '' }}>Diger</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notlar</label>
            <textarea name="notes" rows="3"
                      class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">{{ old('notes', $customer->notes) }}</textarea>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Guncelle
            </button>
            <a href="{{ route('panel.customers.show', ['tenant_slug' => $tenant->slug, 'id' => $customer->id]) }}"
               class="px-6 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                Iptal
            </a>
        </div>
    </form>
</div>
@endsection
