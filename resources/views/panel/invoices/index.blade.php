@extends('layouts.panel')
@section('title', 'Faturalar')
@section('content')

<div class="page-header">
    <h1>Faturalar</h1>
    <p>Abonelik faturalarınızı görüntüleyin ve indirin.</p>
</div>

<div class="card">
    @if($invoices->isEmpty())
        <div class="p-10 text-center">
            <p class="text-gray-400 text-sm">Henüz fatura bulunmuyor.</p>
        </div>
    @else
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="text-left p-4 text-xs font-semibold text-gray-500 uppercase">Fatura No</th>
                <th class="text-left p-4 text-xs font-semibold text-gray-500 uppercase">Paket</th>
                <th class="text-left p-4 text-xs font-semibold text-gray-500 uppercase">Tarih</th>
                <th class="text-left p-4 text-xs font-semibold text-gray-500 uppercase">Tutar</th>
                <th class="text-left p-4 text-xs font-semibold text-gray-500 uppercase">Durum</th>
                <th class="text-right p-4 text-xs font-semibold text-gray-500 uppercase">İşlem</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($invoices as $invoice)
            <tr class="table-row">
                <td class="p-4 text-sm font-medium text-gray-900">
                    #{{ $invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}
                </td>
                <td class="p-4 text-sm text-gray-600">{{ $invoice->plan_name ?? 'Abonelik' }}</td>
                <td class="p-4 text-sm text-gray-600">
                    {{ \Carbon\Carbon::parse($invoice->created_at)->format('d.m.Y') }}
                </td>
                <td class="p-4 text-sm font-semibold text-gray-900">
                    {{ number_format($invoice->amount ?? 0, 2, ',', '.') }} TL
                </td>
                <td class="p-4">
                    @php
                        $status = $invoice->status ?? 'pending';
                    @endphp
                    <span class="badge-{{ match($status) { 'paid' => 'green', 'pending' => 'amber', 'cancelled' => 'red', default => 'gray' } }}">
                        {{ match($status) { 'paid' => 'Ödendi', 'pending' => 'Bekliyor', 'cancelled' => 'İptal', default => $status } }}
                    </span>
                </td>
                <td class="p-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('panel.invoices.show', ['tenant_slug' => $tenant->slug, 'id' => $invoice->id]) }}"
                           target="_blank"
                           class="btn-secondary text-xs px-3 py-1.5">
                            Görüntüle
                        </a>
                        <a href="{{ route('panel.invoices.download', ['tenant_slug' => $tenant->slug, 'id' => $invoice->id]) }}"
                           class="btn-primary text-xs px-3 py-1.5">
                            İndir
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection
