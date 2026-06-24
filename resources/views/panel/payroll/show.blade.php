@extends('layouts.panel')
@section('title', $member->name . ' Bordro')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.payroll.index', ['tenant_slug' => $tenant->slug]) }}?period={{ $period }}"
       class="text-gray-400 hover:text-gray-900">← Bordro</a>
    <h1 class="text-2xl font-semibold text-gray-900">{{ $member->name }} — {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}</h1>
</div>

{{-- Donem Sec --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
    <form method="GET" class="flex items-center gap-3">
        <input type="month" name="period" value="{{ $period }}"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
        <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            Goruntule
        </button>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Bordro Detayi --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Ozet Kartlar --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Randevu</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $calculated['appointment_count'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Ciro</p>
                <p class="text-xl font-semibold text-green-600">{{ number_format($calculated['appointment_revenue'], 0, ',', '.') }} TL</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Prim Orani</p>
                <p class="text-2xl font-semibold text-gray-900">%{{ $commission->rate ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Net Maas</p>
                <p class="text-xl font-semibold text-gray-900">{{ number_format($payroll->net_total ?? $calculated['net_total'], 0, ',', '.') }} TL</p>
            </div>
        </div>

        {{-- Bordro Formu --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Bordro Detayi</h2>
                @if($payroll && $payroll->status === 'paid')
                    <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full font-medium">Odendi</span>
                @endif
            </div>

            @if($payroll)
            <form method="POST" action="{{ route('panel.payroll.update', ['tenant_slug' => $tenant->slug, 'id' => $payroll->id]) }}"
                  class="space-y-4">
                @csrf
                @method('PUT')
            @else
            <form method="POST" action="{{ route('panel.payroll.generate', ['tenant_slug' => $tenant->slug]) }}"
                  class="space-y-4">
                @csrf
                <input type="hidden" name="period" value="{{ $period }}">
                <input type="hidden" name="user_id" value="{{ $member->id }}">
            @endif

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-500">Sabit Maas</span>
                        <span class="font-medium">{{ number_format($payroll->base_salary ?? $calculated['base_salary'], 2, ',', '.') }} TL</span>
                    </div>
                    <div class="flex justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-500">Komisyon Pirimi</span>
                        <span class="font-medium text-green-600">{{ number_format($payroll->commission_total ?? $calculated['commission_total'], 2, ',', '.') }} TL</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ek Prim/Bonus (TL)</label>
                        <input type="number" name="bonus" value="{{ $payroll->bonus ?? 0 }}" min="0" step="0.01"
                               {{ $payroll && $payroll->status === 'paid' ? 'readonly' : '' }}
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kesintiler (TL)</label>
                        <input type="number" name="deductions" value="{{ $payroll->deductions ?? 0 }}" min="0" step="0.01"
                               {{ $payroll && $payroll->status === 'paid' ? 'readonly' : '' }}
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Not</label>
                    <textarea name="notes" rows="2"
                              {{ $payroll && $payroll->status === 'paid' ? 'readonly' : '' }}
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">{{ $payroll->notes ?? '' }}</textarea>
                </div>

                @if(!$payroll || $payroll->status !== 'paid')
                <div class="flex gap-3">
                    <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                        {{ $payroll ? 'Guncelle' : 'Bordro Olustur' }}
                    </button>
                    @if($payroll)
                    <form method="POST" action="{{ route('panel.payroll.paid', ['tenant_slug' => $tenant->slug, 'id' => $payroll->id]) }}">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Maas odendi olarak isaretlenecek ve kasaya gider kaydedilecek. Emin misiniz?')"
                                class="bg-green-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                            Odendi Olarak Isaretle
                        </button>
                    </form>
                    @endif
                </div>
                @endif
            </form>
        </div>

        {{-- Bu Donem Randevular --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Bu Donem Tamamlanan Randevular ({{ $appointments->count() }})</h2>
            @if($appointments->isEmpty())
                <p class="text-sm text-gray-400 text-center py-4">Bu donemde tamamlanan randevu yok.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-100">
                            <tr>
                                <th class="text-left py-2 text-xs text-gray-500">Tarih</th>
                                <th class="text-left py-2 text-xs text-gray-500">Musteri</th>
                                <th class="text-left py-2 text-xs text-gray-500">Hizmet</th>
                                <th class="text-right py-2 text-xs text-gray-500">Ucret</th>
                                <th class="text-right py-2 text-xs text-gray-500">Prim</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($appointments as $appt)
                            <tr>
                                <td class="py-2 text-gray-600">{{ \Carbon\Carbon::parse($appt->start_time)->format('d.m.Y') }}</td>
                                <td class="py-2 text-gray-900">{{ $appt->customer_name }}</td>
                                <td class="py-2 text-gray-600">{{ $appt->service_name }}</td>
                                <td class="py-2 text-right text-gray-900">{{ number_format($appt->price, 2, ',', '.') }} TL</td>
                                <td class="py-2 text-right text-green-600">
                                    {{ number_format($appt->price * (($commission->rate ?? 0) / 100), 2, ',', '.') }} TL
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-gray-200">
                            <tr>
                                <td colspan="3" class="py-2 text-right font-medium text-gray-700">Toplam</td>
                                <td class="py-2 text-right font-semibold">{{ number_format($appointments->sum('price'), 2, ',', '.') }} TL</td>
                                <td class="py-2 text-right font-semibold text-green-600">{{ number_format($calculated['commission_total'], 2, ',', '.') }} TL</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

    </div>

    {{-- Sag Sidebar --}}
    <div class="space-y-4">

        {{-- Komisyon Ayari --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Maas Ayarlari</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-500">Sabit Maas</span>
                    <span class="font-medium">{{ number_format($commission->fixed_amount ?? 0, 0, ',', '.') }} TL</span>
                </div>
                <div class="flex justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-500">Komisyon Orani</span>
                    <span class="font-medium">%{{ $commission->rate ?? 0 }}</span>
                </div>
            </div>
            <a href="{{ route('panel.staff.edit', ['tenant_slug' => $tenant->slug, 'id' => $member->id]) }}"
               class="block mt-3 text-center text-xs text-gray-500 hover:text-gray-900 underline">
                Maas ayarlarini duzenle
            </a>
        </div>

        {{-- Gecmis Bordrolar --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Gecmis Bordrolar</h2>
            @if($history->isEmpty())
                <p class="text-sm text-gray-400 text-center py-3">Bordro gecmisi yok.</p>
            @else
                <div class="space-y-2">
                    @foreach($history as $h)
                    <a href="{{ route('panel.payroll.show', ['tenant_slug' => $tenant->slug, 'user_id' => $member->id]) }}?period={{ $h->period }}"
                       class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::createFromFormat('Y-m', $h->period)->format('M Y') }}</p>
                            <span class="text-xs {{ $h->status === 'paid' ? 'text-green-600' : 'text-amber-600' }}">
                                {{ $h->status === 'paid' ? 'Odendi' : 'Taslak' }}
                            </span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($h->net_total, 0, ',', '.') }} TL</span>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
