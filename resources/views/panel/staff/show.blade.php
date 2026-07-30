@extends('layouts.panel')
@section('title', $member->name)
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.staff.index', ['tenant_slug' => $tenant->slug]) }}"
           class="text-gray-400 hover:text-gray-900">← Geri</a>
        <h1 class="text-2xl font-semibold text-gray-900">{{ $member->name }}</h1>
        <span class="text-xs px-2 py-0.5 rounded-full {{ $member->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $member->status === 'active' ? 'Aktif' : 'Pasif' }}
        </span>
    </div>
    <a href="{{ route('panel.staff.edit', ['tenant_slug' => $tenant->slug, 'id' => $member->id]) }}"
       class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
        Duzenle
    </a>
</div>

{{-- Ozet Kartlar --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Bu Ay Randevu</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $monthlyStats->total_appointments ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Bu Ay Ciro</p>
        <p class="text-2xl font-semibold text-green-600 mt-1">{{ number_format($monthlyStats->total_revenue ?? 0, 0, ',', '.') }} TL</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Bu Ay Prim</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($monthlyCommission, 0, ',', '.') }} TL</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Sabit Maas</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($commission->fixed_amount ?? 0, 0, ',', '.') }} TL</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Calisma Takvimi --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-4">Calisma Takvimi</h2>
        <form method="POST" action="{{ route('panel.staff.schedule', ['tenant_slug' => $tenant->slug, 'id' => $member->id]) }}" class="space-y-2">
            @csrf
            @foreach([1=>'Pazartesi', 2=>'Sali', 3=>'Carsamba', 4=>'Persembe', 5=>'Cuma', 6=>'Cumartesi', 0=>'Pazar'] as $dayNum => $dayName)
            @php $schedule = $schedules[$dayNum] ?? null; @endphp
            <div class="flex flex-wrap items-center gap-2 p-2 bg-gray-50 rounded-lg">
                <label class="flex items-center gap-2 w-28 flex-shrink-0">
                    <input type="checkbox" name="days[{{ $dayNum }}][is_working]" value="1"
                           {{ ($schedule && $schedule->is_working) ? 'checked' : '' }}
                           class="rounded border-gray-300">
                    <span class="text-sm text-gray-700">{{ $dayName }}</span>
                </label>
                <input type="time" name="days[{{ $dayNum }}][start_time]"
                       value="{{ $schedule->start_time ?? '09:00' }}"
                       class="px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-gray-900 outline-none">
                <span class="text-gray-400">-</span>
                <input type="time" name="days[{{ $dayNum }}][end_time]"
                       value="{{ $schedule->end_time ?? '18:00' }}"
                       class="px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-gray-900 outline-none">
                @if(count($branches) > 1)
                <select name="days[{{ $dayNum }}][branch_id]"
                        class="px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-gray-900 outline-none bg-white">
                    <option value="">Şube seçin</option>
                    @foreach($branches as $br)
                    <option value="{{ $br->id }}" {{ ($schedule && $schedule->branch_id == $br->id) ? 'selected' : '' }}>
                        {{ $br->name }}
                    </option>
                    @endforeach
                </select>
                @else
                <input type="hidden" name="days[{{ $dayNum }}][branch_id]" value="{{ $branches->first()->id ?? '' }}">
                @endif
            </div>
            @endforeach
            <button type="submit" class="w-full bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition mt-2">
                Takvimi Kaydet
            </button>
        </form>
    </div>

    {{-- Izin Yonetimi --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-900 mb-4">Izin Yonetimi</h2>

        {{-- Izin Ekle --}}
        <form method="POST" action="{{ route('panel.staff.leave.store', ['tenant_slug' => $tenant->slug, 'id' => $member->id]) }}" class="space-y-2 mb-4">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Izin Turu</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="annual">Yillik Izin</option>
                        <option value="sick">Hastalık Izni</option>
                        <option value="unpaid">Ucretsiz Izin</option>
                        <option value="other">Diger</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Not</label>
                    <input type="text" name="notes" placeholder="Opsiyonel"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Baslangic</label>
                    <input type="date" name="start_date" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Bitis</label>
                    <input type="date" name="end_date" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
            </div>
            <button type="submit" class="w-full bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Izin Ekle
            </button>
        </form>

        {{-- Izin Listesi --}}
        @if($leaves->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Henuz izin kaydı yok.</p>
        @else
            <div class="space-y-2">
                @foreach($leaves as $leave)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">
                            {{ match($leave->type) {
                                'annual' => 'Yillik Izin',
                                'sick' => 'Hastalik Izni',
                                'unpaid' => 'Ucretsiz Izin',
                                default => 'Diger'
                            } }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d.m.Y') }} -
                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d.m.Y') }}
                            ({{ $leave->total_days }} gun)
                        </p>
                    </div>
                    <form method="POST" action="{{ route('panel.staff.leave.destroy', ['tenant_slug' => $tenant->slug, 'id' => $member->id, 'leave_id' => $leave->id]) }}"
                          onsubmit="return confirm('Silmek istediginizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Sil</button>
                    </form>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

{{-- Yetki Ayarları (yalnızca firma sahibi görebilir, firma sahibine gösterilmez) --}}
@if(auth()->user()->role === 'firma_sahibi' && $member->role !== 'firma_sahibi')
<div class="mt-4 bg-white rounded-xl border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="font-semibold text-gray-900">🔐 Erişim Yetkileri</h2>
            <p class="text-xs text-gray-400 mt-0.5">
                @if($hasCustomPermissions)
                    Özel yetkiler aktif — aşağıdaki seçimler geçerli.
                @else
                    Şu an <strong>{{ match($member->role) { 'sube_muduru' => 'Şube Müdürü', 'sekreter' => 'Sekreter', 'personel' => 'Personel', default => $member->role } }}</strong> rol varsayılanları geçerli. Değiştirmek için düzenleyin.
                @endif
            </p>
        </div>
        @if($hasCustomPermissions)
        <form method="POST" action="{{ route('panel.staff.permissions', ['tenant_slug' => $tenant->slug, 'id' => $member->id]) }}">
            @csrf
            {{-- Boş gönder → özel yetkiler silinir, rol varsayılanına döner --}}
            <button type="submit" onclick="return confirm('Özel yetkiler silinerek rol varsayılanına dönülsün mü?')"
                    class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg">
                Varsayılana Dön
            </button>
        </form>
        @endif
    </div>

    <form method="POST" action="{{ route('panel.staff.permissions', ['tenant_slug' => $tenant->slug, 'id' => $member->id]) }}">
        @csrf
        @php
            $activePerms = $hasCustomPermissions ? $customPermissions : $roleDefaults;
            $groups = collect($permissionDefs)->groupBy(fn($d) => $d['group']);
        @endphp

        @foreach($groups as $groupName => $items)
        <div class="mb-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ $groupName }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($items as $key => $def)
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                           {{ in_array($key, $activePerms) ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-gray-900">
                    <span class="text-lg leading-none">{{ $def['icon'] }}</span>
                    <span class="text-sm text-gray-700">{{ $def['label'] }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="mt-4 flex gap-2">
            <button type="submit" class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                Kaydet
            </button>
            <p class="text-xs text-gray-400 self-center">Kaydettiğinizde özel yetkiler aktive olur.</p>
        </div>
    </form>
</div>
@endif

@endsection
