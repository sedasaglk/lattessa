@extends('layouts.panel')
@section('title', 'Abonelik')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Abonelik</h1>
</div>

@if(request('success'))
<div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
    Odemeniz basariyla alindi! Aboneliginiz aktif edildi.
</div>
@endif

{{-- Mevcut Durum --}}
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 mb-1">Mevcut Plan</p>
            <p class="text-2xl font-semibold text-gray-900">
                {{ $subscription->package_name ?? 'Paket Yok' }}
            </p>
            @if($tenant->status === 'trial')
                <div class="flex items-center gap-2 mt-2">
                    <span class="bg-amber-100 text-amber-700 text-xs font-medium px-2.5 py-1 rounded-full">
                        Deneme Suresi
                    </span>
                    <span class="text-sm text-gray-500">{{ $daysLeft }} gun kaldi</span>
                </div>
            @elseif($tenant->status === 'active')
                <div class="flex items-center gap-2 mt-2">
                    <span class="bg-green-100 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">Aktif</span>
                    @if($subscription && $subscription->ends_at)
                        <span class="text-sm text-gray-500">
                            Yenileme: {{ \Carbon\Carbon::parse($subscription->ends_at)->format('d.m.Y') }}
                        </span>
                    @endif
                </div>
            @elseif($tenant->status === 'suspended')
                <div class="flex items-center gap-2 mt-2">
                    <span class="bg-red-100 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full">Askiya Alindi</span>
                </div>
            @endif
        </div>
        @if($tenant->status === 'trial')
            <div class="text-right">
                <p class="text-sm text-gray-500 mb-2">Denemeniz bitiyor!</p>
                <a href="#paketler" class="bg-gray-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Paket Sec
                </a>
            </div>
        @endif
    </div>

    @if($subscription)
    <div class="mt-6 pt-6 border-t border-gray-100">
        <p class="text-sm font-medium text-gray-700 mb-4">Kullanim Durumu</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="flex items-center justify-between text-sm mb-1.5">
                    <span class="text-gray-600">Personel</span>
                    <span class="font-medium text-gray-900">
                        {{ $usage['staff'] }} / {{ $subscription->staff_limit ?? 'Sinirsiz' }}
                    </span>
                </div>
                @if($subscription->staff_limit)
                    @php $pct = min(100, ($usage['staff'] / $subscription->staff_limit) * 100) @endphp
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-green-500') }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                @else
                    <div class="w-full bg-green-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full bg-green-500 w-full"></div>
                    </div>
                @endif
            </div>
            <div>
                <div class="flex items-center justify-between text-sm mb-1.5">
                    <span class="text-gray-600">Sube</span>
                    <span class="font-medium text-gray-900">
                        {{ $usage['branches'] }} / {{ $subscription->branch_limit ?? 'Sinirsiz' }}
                    </span>
                </div>
                @if($subscription->branch_limit)
                    @php $pct = min(100, ($usage['branches'] / $subscription->branch_limit) * 100) @endphp
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-green-500') }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                @else
                    <div class="w-full bg-green-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full bg-green-500 w-full"></div>
                    </div>
                @endif
            </div>
            <div>
                <div class="flex items-center justify-between text-sm mb-1.5">
                    <span class="text-gray-600">SMS (Bu Ay)</span>
                    <span class="font-medium text-gray-900">
                        {{ $usage['sms_used'] }} / {{ $subscription->sms_limit }}
                    </span>
                </div>
                @php $pct = $subscription->sms_limit > 0 ? min(100, ($usage['sms_used'] / $subscription->sms_limit) * 100) : 0 @endphp
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-green-500') }}"
                         style="width: {{ $pct }}%"></div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Paket Secimi --}}
<div id="paketler" class="mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ $tenant->status === 'trial' ? 'Paket Sec' : 'Plan Degistir' }}
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($packages as $pkg)
        @php
            $isCurrent = $subscription && $subscription->package_slug === $pkg->slug && $tenant->status === 'active';
        @endphp
        <div class="bg-white rounded-xl border {{ $isCurrent ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-200' }} p-5">
            @if($isCurrent)
                <div class="text-xs font-medium text-gray-900 bg-gray-100 px-2 py-0.5 rounded-full inline-block mb-3">
                    Mevcut Plan
                </div>
            @endif
            <p class="font-semibold text-gray-900">{{ $pkg->name }}</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ number_format($pkg->price_monthly, 0, ',', '.') }} TL
                <span class="text-sm text-gray-400 font-normal">/ay</span>
            </p>
            <p class="text-xs text-gray-400 mt-0.5">
                Yillik {{ number_format($pkg->price_yearly, 0, ',', '.') }} TL
            </p>
            <hr class="my-4 border-gray-100">
            <ul class="space-y-2 text-sm text-gray-600 mb-5">
                <li class="flex items-center gap-2">
                    <span class="text-green-500">✓</span>
                    {{ $pkg->staff_limit ? $pkg->staff_limit . ' personel' : 'Sinirsiz personel' }}
                </li>
                <li class="flex items-center gap-2">
                    <span class="text-green-500">✓</span>
                    {{ $pkg->branch_limit ? $pkg->branch_limit . ' sube' : 'Sinirsiz sube' }}
                </li>
                <li class="flex items-center gap-2">
                    <span class="text-green-500">✓</span>
                    {{ $pkg->sms_limit }} SMS/ay
                </li>
                <li class="flex items-center gap-2">
                    <span class="text-green-500">✓</span>
                    {{ round($pkg->storage_limit_mb / 1024, 0) }} GB depolama
                </li>
            </ul>

            @if($isCurrent)
                <button disabled class="w-full border border-gray-200 text-gray-400 py-2.5 rounded-lg text-sm font-medium cursor-not-allowed">
                    Mevcut Planin
                </button>
            @else
                <form method="POST" action="{{ route('panel.checkout', ['tenant_slug' => $tenant->slug]) }}">
                    @csrf
                    <input type="hidden" name="package" value="{{ $pkg->slug }}">
                    <button type="submit"
                            class="w-full {{ $pkg->slug === 'profesyonel' ? 'bg-gray-900 text-white hover:bg-gray-800' : 'border border-gray-200 text-gray-700 hover:bg-gray-50' }} py-2.5 rounded-lg text-sm font-medium transition">
                        {{ $tenant->status === 'trial' ? 'Bu Paketi Sec' : 'Bu Pakete Gec' }}
                    </button>
                </form>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- Fatura Gecmisi --}}
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h2 class="font-semibold text-gray-900 mb-4">Fatura Gecmisi</h2>
    @if($invoices->isEmpty())
        <p class="text-sm text-gray-400 text-center py-6">Henuz fatura bulunmuyor.</p>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($invoices as $invoice)
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($invoice->created_at)->format('d.m.Y') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-900">
                        {{ number_format($invoice->total_amount, 2, ',', '.') }} TL
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $invoice->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $invoice->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}
                    ">
                        {{ match($invoice->status) {
                            'paid' => 'Odendi',
                            'pending' => 'Bekliyor',
                            'failed' => 'Basarisiz',
                            default => $invoice->status
                        } }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
