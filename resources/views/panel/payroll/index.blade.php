@extends('layouts.panel')
@section('title', 'Bordro')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Bordro & Maaş Yönetimi</h1>
</div>

{{-- Donem Sec --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <form method="GET" class="flex items-center gap-3">
        <input type="month" name="period" value="{{ $period }}"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
        <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            Goruntule
        </button>
        <span class="text-sm text-gray-500">{{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }} donemi</span>
    </form>
</div>

{{-- Ozet --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase">Toplam Sabit Maas</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($totalBase, 0, ',', '.') }} TL</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase">Toplam Prim</p>
        <p class="text-2xl font-semibold text-green-600 mt-1">{{ number_format($totalCommission, 0, ',', '.') }} TL</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase">Toplam Net Odeme</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($totalNet, 0, ',', '.') }} TL</p>
    </div>
</div>

{{-- Bordro Olustur --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-900">{{ $period }} Personel Bordrolar</h2>
        <form method="POST" action="{{ route('panel.payroll.generate', ['tenant_slug' => $tenant->slug]) }}">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
            <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                Tum Bordroyu Hesapla
            </button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100">
                <tr>
                    <th class="text-left py-3 px-4 text-xs text-gray-500 font-medium">Personel</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Randevu</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Ciro</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Sabit Maas</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Prim</th>
                    <th class="text-right py-3 px-4 text-xs text-gray-500 font-medium">Net Toplam</th>
                    <th class="text-center py-3 px-4 text-xs text-gray-500 font-medium">Durum</th>
                    <th class="py-3 px-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($staffWithPayroll as $member)
                @php $payroll = $member['payroll']; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4">
                        <p class="font-medium text-gray-900">{{ $member['name'] }}</p>
                        <p class="text-xs text-gray-400">{{ match($member['role']) {
                            'firma_sahibi' => 'Firma Sahibi',
                            'sube_muduru' => 'Sube Muduru',
                            'sekreter' => 'Sekreter',
                            'personel' => 'Personel',
                            default => $member['role']
                        } }}</p>
                    </td>
                    <td class="py-3 px-4 text-right text-gray-700">{{ $payroll->appointment_count ?? 0 }}</td>
                    <td class="py-3 px-4 text-right text-gray-700">{{ number_format($payroll->appointment_revenue ?? 0, 0, ',', '.') }} TL</td>
                    <td class="py-3 px-4 text-right text-gray-700">{{ number_format($payroll->base_salary ?? 0, 0, ',', '.') }} TL</td>
                    <td class="py-3 px-4 text-right text-green-600">{{ number_format($payroll->commission_total ?? 0, 0, ',', '.') }} TL</td>
                    <td class="py-3 px-4 text-right font-semibold text-gray-900">{{ number_format($payroll->net_total ?? 0, 0, ',', '.') }} TL</td>
                    <td class="py-3 px-4 text-center">
                        @if(isset($payroll->status))
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $payroll->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $payroll->status === 'paid' ? 'Odendi' : 'Taslak' }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400">Hesaplanmadi</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <a href="{{ route('panel.payroll.show', ['tenant_slug' => $tenant->slug, 'user_id' => $member['id']]) }}?period={{ $period }}"
                           class="text-sm text-gray-500 hover:text-gray-900">Detay</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
