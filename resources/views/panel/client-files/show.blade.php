@extends('layouts.panel')
@section('title', $customer->name . ' - Danisan Dosyasi')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.customers.show', ['tenant_slug' => $tenant->slug, 'id' => $customer->id]) }}"
       class="text-gray-400 hover:text-gray-900">← Musteri</a>
    <h1 class="text-2xl font-semibold text-gray-900">{{ $customer->name }} — Danisan Dosyasi</h1>
</div>

{{-- Ozet --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Toplam Seans</p>
        <p class="text-2xl font-semibold text-gray-900">{{ $sessionCount }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Boy/Kilo</p>
        <p class="text-lg font-semibold text-gray-900">
            {{ $clientFile->height ?? '-' }} cm /
            {{ $clientFile->weight ?? '-' }} kg
        </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Kan Grubu</p>
        <p class="text-2xl font-semibold text-gray-900">{{ $clientFile->blood_type ?? '-' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Tani</p>
        <p class="text-sm font-medium text-gray-900 truncate">{{ $clientFile->diagnosis ?? 'Girilmemis' }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Sol: Danisan Dosyasi Formu --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Klinik Bilgiler</h2>
            <form method="POST" action="{{ route('panel.client-files.update', ['tenant_slug' => $tenant->slug, 'customer_id' => $customer->id]) }}"
                  class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Boy (cm)</label>
                    <input type="number" name="height" value="{{ $clientFile->height ?? '' }}" step="0.1" min="0" max="300"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Kilo (kg)</label>
                    <input type="number" name="weight" value="{{ $clientFile->weight ?? '' }}" step="0.1" min="0" max="500"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Kan Grubu</label>
                    <select name="blood_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="">Secilmedi</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','0+','0-'] as $bt)
                            <option value="{{ $bt }}" {{ ($clientFile->blood_type ?? '') === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Basvuru Sikayeti</label>
                    <textarea name="complaint" rows="2"
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">{{ $clientFile->complaint ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Alerjiler (virgul ile ayirin)</label>
                    <input type="text" name="allergies"
                           value="{{ $clientFile && $clientFile->allergies ? implode(', ', json_decode($clientFile->allergies, true) ?? []) : '' }}"
                           placeholder="Penisilin, Polen..."
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Kullanilan Ilaclar</label>
                    <input type="text" name="medications"
                           value="{{ $clientFile && $clientFile->medications ? implode(', ', json_decode($clientFile->medications, true) ?? []) : '' }}"
                           placeholder="Aspirin, Metformin..."
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Anamnez</label>
                    <textarea name="anamnesis" rows="3"
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">{{ $clientFile->anamnesis ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tani</label>
                    <textarea name="diagnosis" rows="2"
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">{{ $clientFile->diagnosis ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tedavi Plani</label>
                    <textarea name="treatment_plan" rows="3"
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">{{ $clientFile->treatment_plan ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Gecmis Hastaliklar</label>
                    <textarea name="medical_history" rows="2"
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">{{ $clientFile->medical_history ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Aile Hikayesi</label>
                    <textarea name="family_history" rows="2"
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">{{ $clientFile->family_history ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Genel Notlar</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">{{ $clientFile->notes ?? '' }}</textarea>
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Dosyayi Kaydet
                </button>
            </form>
        </div>
    </div>

    {{-- Sag: Seans Notlari --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Yeni Seans Notu --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Yeni Seans Notu (SOAP)</h2>
            <form method="POST" action="{{ route('panel.client-files.notes.store', ['tenant_slug' => $tenant->slug, 'customer_id' => $customer->id]) }}"
                  class="space-y-3">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Seans Tarihi</label>
                        <input type="date" name="session_date" value="{{ today()->format('Y-m-d') }}" required
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Terapist/Doktor</label>
                        <select name="staff_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ auth()->id() === $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Iliskilendirilecek Randevu</label>
                        <select name="appointment_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                            <option value="">Secilmedi</option>
                            @foreach($appointments as $appt)
                                <option value="{{ $appt->id }}">
                                    {{ \Carbon\Carbon::parse($appt->start_time)->format('d.m.Y') }} - {{ $appt->service_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Kilo (kg)</label>
                        <input type="number" name="weight" step="0.1"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">S — Subjektif (Hasta Ne Soyluyor?)</label>
                    <textarea name="subjective" rows="2" placeholder="Hasta sikayetleri, hissettikleri..."
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">O — Objektif (Gozlemler/Olcumler)</label>
                    <textarea name="objective" rows="2" placeholder="Muayene bulgulari, test sonuclari..."
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">A — Degerlendirme</label>
                    <textarea name="assessment" rows="2" placeholder="Klinik degerlendirme, tani guncellemesi..."
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">P — Plan</label>
                    <textarea name="plan" rows="2" placeholder="Tedavi plani, bir sonraki adimlar..."
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Ruh Hali (1-10)</label>
                        <input type="number" name="mood_score" min="1" max="10"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 cursor-pointer pb-2">
                            <input type="checkbox" name="is_private" value="1" class="rounded border-gray-300">
                            <span class="text-xs text-gray-700">Gizli Not (sadece ben gorebilirim)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Ev Odevi</label>
                    <textarea name="homework" rows="1" placeholder="Hastaya verilen gorevler..."
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Sonraki Seans Plani</label>
                    <textarea name="next_session_plan" rows="1"
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Seans Notunu Kaydet
                </button>
            </form>
        </div>

        {{-- Seans Gecmisi --}}
        @if($sessionNotes->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Seans Gecmisi ({{ $sessionCount }} seans)</h2>
            <div class="space-y-4">
                @foreach($sessionNotes as $note)
                <div class="border border-gray-100 rounded-xl p-4 {{ $note->is_private ? 'bg-amber-50 border-amber-200' : '' }}">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <span class="bg-gray-900 text-white text-xs px-2 py-0.5 rounded-full">
                                #{{ $note->session_number }}
                            </span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($note->session_date)->format('d.m.Y') }}
                            </span>
                            <span class="text-xs text-gray-500">{{ $note->staff_name }}</span>
                            @if($note->is_private)
                                <span class="text-xs text-amber-600">🔒 Gizli</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($note->mood_score)
                                <span class="text-xs text-gray-500">Ruh hali: {{ $note->mood_score }}/10</span>
                            @endif
                            @if($note->weight)
                                <span class="text-xs text-gray-500">{{ $note->weight }} kg</span>
                            @endif
                            <form method="POST" action="{{ route('panel.client-files.notes.destroy', ['tenant_slug' => $tenant->slug, 'customer_id' => $customer->id, 'note_id' => $note->id]) }}"
                                  onsubmit="return confirm('Seans notunu silmek istediginizden emin misiniz?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600">Sil</button>
                            </form>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        @if($note->subjective)
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs font-medium text-blue-700 mb-1">S — Subjektif</p>
                            <p class="text-gray-700">{{ $note->subjective }}</p>
                        </div>
                        @endif
                        @if($note->objective)
                        <div class="p-3 bg-green-50 rounded-lg">
                            <p class="text-xs font-medium text-green-700 mb-1">O — Objektif</p>
                            <p class="text-gray-700">{{ $note->objective }}</p>
                        </div>
                        @endif
                        @if($note->assessment)
                        <div class="p-3 bg-amber-50 rounded-lg">
                            <p class="text-xs font-medium text-amber-700 mb-1">A — Degerlendirme</p>
                            <p class="text-gray-700">{{ $note->assessment }}</p>
                        </div>
                        @endif
                        @if($note->plan)
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <p class="text-xs font-medium text-purple-700 mb-1">P — Plan</p>
                            <p class="text-gray-700">{{ $note->plan }}</p>
                        </div>
                        @endif
                    </div>

                    @if($note->homework || $note->next_session_plan)
                    <div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-3 text-sm">
                        @if($note->homework)
                        <div>
                            <p class="text-xs font-medium text-gray-500 mb-1">Ev Odevi</p>
                            <p class="text-gray-700">{{ $note->homework }}</p>
                        </div>
                        @endif
                        @if($note->next_session_plan)
                        <div>
                            <p class="text-xs font-medium text-gray-500 mb-1">Sonraki Seans Plani</p>
                            <p class="text-gray-700">{{ $note->next_session_plan }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

</div>
@endsection
