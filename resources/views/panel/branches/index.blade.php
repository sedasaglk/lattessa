@extends('layouts.panel')
@section('title', 'Şubeler')
@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Şube Yönetimi</h1>
    <button onclick="openCreateModal()"
            class="inline-flex items-center gap-2 bg-gray-900 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Şube Ekle
    </button>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
    {{ $errors->first() }}
</div>
@endif

{{-- Period Filtresi --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <div class="flex items-center gap-2 flex-wrap">
        @foreach([
            'today' => 'Bugün',
            'this_week' => 'Bu Hafta',
            'this_month' => 'Bu Ay',
            'last_month' => 'Geçen Ay',
        ] as $value => $label)
        <a href="?period={{ $value }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
               {{ $period === $value ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ $label }}
        </a>
        @endforeach
        <span class="text-xs text-gray-400 ml-2">
            {{ \Carbon\Carbon::parse($startDate)->format('d.m.Y') }} –
            {{ \Carbon\Carbon::parse($endDate)->format('d.m.Y') }}
        </span>
    </div>
</div>

{{-- Şube Kartları --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    @forelse($branches as $branch)
    @php $stats = $branchStats[$branch->id] ?? ['revenue' => 0, 'appointments' => 0, 'staff_count' => 0]; @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="font-semibold text-gray-900">{{ $branch->name }}</p>
                @if($branch->phone)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $branch->phone }}</p>
                @endif
                @if($branch->address)
                    <p class="text-xs text-gray-400">{{ $branch->address }}</p>
                @endif
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $branch->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $branch->status === 'active' ? 'Aktif' : 'Pasif' }}
            </span>
        </div>

        <div class="space-y-2 mb-4">
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-500">Ciro</span>
                <span class="font-semibold text-green-600">{{ number_format($stats['revenue'], 0, ',', '.') }} TL</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-500">Tamamlanan Randevu</span>
                <span class="font-semibold text-gray-900">{{ $stats['appointments'] }}</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-500">Aktif Personel</span>
                <span class="font-semibold text-gray-900">{{ $stats['staff_count'] }}</span>
            </div>
        </div>

        @if($branch->booking_slug)
        <div class="mb-3 px-3 py-2 bg-blue-50 rounded-lg flex items-center justify-between gap-2">
            <span class="text-xs text-blue-600 truncate">
                🔗 {{ config('app.url') }}/{{ $tenant->slug }}/randevu/{{ $branch->booking_slug }}
            </span>
            <button type="button"
                    onclick="navigator.clipboard.writeText('{{ config('app.url') }}/{{ $tenant->slug }}/randevu/{{ $branch->booking_slug }}').then(()=>alert('Link kopyalandı!'))"
                    class="text-xs text-blue-700 font-medium whitespace-nowrap hover:underline">Kopyala</button>
        </div>
        @endif

        <div class="flex gap-2 pt-4 border-t border-gray-100">
            <button onclick='openEditModal(@json($branch))'
                    class="flex-1 bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Düzenle
            </button>
            <form method="POST" action="{{ route('panel.branches.destroy', ['tenant_slug' => $tenant->slug, 'id' => $branch->id]) }}"
                  onsubmit="return confirm('Şubeyi silmek istediğinizden emin misiniz?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm hover:bg-red-50 transition">
                    Sil
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-12 text-gray-400 text-sm">
        Henüz şube eklenmemiş. "Şube Ekle" butonunu kullanın.
    </div>
    @endforelse
</div>

{{-- Şube Karşılaştırma Tablosu --}}
@if($branches->count() > 1)
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h2 class="font-semibold text-gray-900 mb-4">Şube Karşılaştırması</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100">
                <tr>
                    <th class="text-left py-2 px-3 text-xs text-gray-500 font-medium">Şube</th>
                    <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Ciro</th>
                    <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Randevu</th>
                    <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Personel</th>
                    <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Ort. Randevu Değeri</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($branches as $branch)
                @php
                    $stats = $branchStats[$branch->id] ?? ['revenue' => 0, 'appointments' => 0, 'staff_count' => 0];
                    $avgValue = $stats['appointments'] > 0 ? $stats['revenue'] / $stats['appointments'] : 0;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-3 font-medium text-gray-900">{{ $branch->name }}</td>
                    <td class="py-2 px-3 text-right text-green-600 font-medium">{{ number_format($stats['revenue'], 0, ',', '.') }} TL</td>
                    <td class="py-2 px-3 text-right text-gray-700">{{ $stats['appointments'] }}</td>
                    <td class="py-2 px-3 text-right text-gray-700">{{ $stats['staff_count'] }}</td>
                    <td class="py-2 px-3 text-right text-gray-700">{{ number_format($avgValue, 0, ',', '.') }} TL</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t border-gray-200">
                @php
                    $totalRevenue = array_sum(array_column($branchStats, 'revenue'));
                    $totalAppts = array_sum(array_column($branchStats, 'appointments'));
                @endphp
                <tr class="font-semibold">
                    <td class="py-2 px-3 text-gray-900">Toplam</td>
                    <td class="py-2 px-3 text-right text-green-600">{{ number_format($totalRevenue, 0, ',', '.') }} TL</td>
                    <td class="py-2 px-3 text-right text-gray-900">{{ $totalAppts }}</td>
                    <td class="py-2 px-3 text-right text-gray-900">–</td>
                    <td class="py-2 px-3 text-right text-gray-900">–</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- CREATE MODAL --}}
<div id="createModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeCreateModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 text-lg">Yeni Şube Ekle</h3>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('panel.branches.store', ['tenant_slug' => $tenant->slug]) }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şube Adı <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Merkez Şube"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="text" name="phone" placeholder="0212 XXX XX XX"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
                    <input type="text" name="address" placeholder="Mahalle, Sokak, No"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeCreateModal()"
                            class="flex-1 border border-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        İptal
                    </button>
                    <button type="submit"
                            class="flex-1 bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                        Şube Ekle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div id="editModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeEditModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 text-lg">Şube Düzenle</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="editForm" method="POST" action="" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şube Adı <span class="text-red-500">*</span></label>
                    <input type="text" id="editName" name="name" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="text" id="editPhone" name="phone"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
                    <input type="text" id="editAddress" name="address"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Randevu Link Kodu</label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 whitespace-nowrap">.../randevu/</span>
                        <input type="text" id="editBookingSlug" name="booking_slug"
                               placeholder="carsi-sube"
                               class="flex-1 px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Sadece harf, rakam ve tire kullanın.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                    <select id="editStatus" name="status"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="active">Aktif</option>
                        <option value="inactive">Pasif</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()"
                            class="flex-1 border border-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        İptal
                    </button>
                    <button type="submit"
                            class="flex-1 bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const tenantSlug = '{{ $tenant->slug }}';

function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function openEditModal(branch) {
    document.getElementById('editName').value = branch.name || '';
    document.getElementById('editPhone').value = branch.phone || '';
    document.getElementById('editAddress').value = branch.address || '';
    document.getElementById('editBookingSlug').value = branch.booking_slug || '';
    document.getElementById('editStatus').value = branch.status || 'active';
    document.getElementById('editForm').action = `/${tenantSlug}/subeler/${branch.id}`;
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
        closeEditModal();
    }
});
</script>

@endsection
