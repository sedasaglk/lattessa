@extends('layouts.panel')
@section('title', 'Yeni Musteri')
@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.customers.index', ['tenant_slug' => $tenant->slug]) }}"
       class="text-gray-400 hover:text-gray-900">← Geri</a>
    <h1 class="text-2xl font-semibold text-gray-900">Yeni Musteri</h1>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('panel.customers.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
            <input type="text" name="phone" value="{{ old('phone') }}" required
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">E-posta (opsiyonel)</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dogum Tarihi (opsiyonel)</label>
            <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cinsiyet</label>
            <select name="gender" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">
                <option value="">Belirtilmemis</option>
                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Kadin</option>
                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Erkek</option>
                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Diger</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notlar (opsiyonel)</label>
            <textarea name="notes" rows="3"
                      class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 outline-none text-sm">{{ old('notes') }}</textarea>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Musteri Ekle
            </button>
            <a href="{{ route('panel.customers.index', ['tenant_slug' => $tenant->slug]) }}"
               class="px-6 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                Iptal
            </a>
        </div>
    </form>
</div>
@push('scripts')
<script>
// Contact Picker API - Android Chrome destekler, iOS'ta gizlenir
if ('contacts' in navigator && 'ContactsManager' in window) {
    document.getElementById('contactPickerBtn').classList.remove('hidden');
}

async function pickContact() {
    try {
        const props = ['name', 'tel', 'email'];
        const opts = { multiple: false };
        const contacts = await navigator.contacts.select(props, opts);
        if (!contacts || contacts.length === 0) return;

        const contact = contacts[0];
        if (contact.name && contact.name[0]) {
            document.getElementById('nameInput').value = contact.name[0];
        }
        if (contact.tel && contact.tel[0]) {
            document.getElementById('phoneInput').value = contact.tel[0].replace(/\s/g, '');
        }
        if (contact.email && contact.email[0]) {
            document.getElementById('emailInput').value = contact.email[0];
        }
    } catch (err) {
        console.log('Contact picker iptal edildi veya hata:', err);
    }
}
</script>
@endpush
@endsection
