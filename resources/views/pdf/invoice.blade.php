<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura #{{ $invoice->invoice_number ?? $invoice->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; color: #111; background: #fff; padding: 40px; }

        .header { display: table; width: 100%; margin-bottom: 40px; }
        .header-left { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { display: table-cell; vertical-align: top; width: 50%; text-align: right; }

        .logo-box { background: #6366F1; color: #fff; display: inline-block; padding: 8px 16px; border-radius: 8px; font-size: 20px; font-weight: bold; margin-bottom: 8px; }
        .company-name { font-size: 11px; color: #6B7280; margin-top: 4px; }

        .invoice-title { font-size: 28px; font-weight: bold; color: #111; }
        .invoice-number { font-size: 13px; color: #6B7280; margin-top: 4px; }
        .invoice-status { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: bold; margin-top: 8px; }
        .status-paid { background: #DCFCE7; color: #166534; }
        .status-pending { background: #FEF3C7; color: #92400E; }
        .status-cancelled { background: #FEE2E2; color: #991B1B; }

        .divider { border: none; border-top: 2px solid #E5E7EB; margin: 24px 0; }
        .thin-divider { border: none; border-top: 1px solid #E5E7EB; margin: 16px 0; }

        .info-section { display: table; width: 100%; margin-bottom: 32px; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; }
        .info-box.right { text-align: right; }
        .info-label { font-size: 10px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; font-weight: bold; }
        .info-value { font-size: 13px; color: #111; line-height: 1.6; }
        .info-value strong { font-weight: bold; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.items thead tr { background: #111; color: #fff; }
        table.items thead th { padding: 10px 12px; text-align: left; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; }
        table.items thead th.right { text-align: right; }
        table.items tbody tr { border-bottom: 1px solid #E5E7EB; }
        table.items tbody tr:nth-child(even) { background: #F9FAFB; }
        table.items tbody td { padding: 10px 12px; font-size: 13px; }
        table.items tbody td.right { text-align: right; }

        .totals { width: 280px; float: right; }
        .total-row { display: table; width: 100%; padding: 6px 0; }
        .total-label { display: table-cell; color: #6B7280; font-size: 13px; }
        .total-value { display: table-cell; text-align: right; font-size: 13px; }
        .total-grand { background: #6366F1; color: #fff; padding: 10px 12px; border-radius: 8px; margin-top: 8px; }
        .total-grand .total-label { color: #fff; font-weight: bold; font-size: 14px; }
        .total-grand .total-value { color: #fff; font-weight: bold; font-size: 16px; }

        .clearfix::after { content: ''; display: table; clear: both; }

        .footer { margin-top: 60px; padding-top: 20px; border-top: 1px solid #E5E7EB; }
        .footer-text { font-size: 11px; color: #9CA3AF; text-align: center; line-height: 1.6; }
        .footer-brand { font-weight: bold; color: #6366F1; }

        .note-box { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px; padding: 12px 16px; margin-top: 24px; }
        .note-label { font-size: 11px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; font-weight: bold; }
        .note-text { font-size: 12px; color: #374151; }

        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 80px; color: rgba(99,102,241,0.06); font-weight: bold; z-index: -1; }
    </style>
</head>
<body>

@php
    $statusLabel = match($invoice->status ?? 'pending') {
        'paid' => 'Odendi',
        'pending' => 'Bekliyor',
        'cancelled' => 'Iptal',
        default => $invoice->status ?? 'Bekliyor'
    };
    $statusClass = match($invoice->status ?? 'pending') {
        'paid' => 'status-paid',
        'pending' => 'status-pending',
        'cancelled' => 'status-cancelled',
        default => 'status-pending'
    };
    $invoiceDate = $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('d.m.Y') : now()->format('d.m.Y');
    $dueDate = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') : now()->addDays(7)->format('d.m.Y');
@endphp

<div class="watermark">LATTESSA</div>

{{-- Header --}}
<div class="header">
    <div class="header-left">
        <div class="logo-box">Lattessa</div>
        <div class="company-name">Salon & Klinik Yonetim Platformu</div>
        <div style="margin-top:8px; font-size:11px; color:#6B7280;">
            lattessa.com<br>
            info@lattessa.com
        </div>
    </div>
    <div class="header-right">
        <div class="invoice-title">FATURA</div>
        <div class="invoice-number">#{{ $invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</div>
        <div>
            <span class="invoice-status {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
    </div>
</div>

<hr class="divider">

{{-- Fatura Bilgileri --}}
<div class="info-section">
    <div class="info-box">
        <div class="info-label">Fatura Kesilen</div>
        <div class="info-value">
            <strong>{{ $invoice->company_name }}</strong><br>
            @if($invoice->phone) {{ $invoice->phone }}<br> @endif
            @if($invoice->email) {{ $invoice->email }}<br> @endif
            @if($invoice->address) {{ $invoice->address }} @endif
        </div>
    </div>
    <div class="info-box right">
        <div class="info-label">Fatura Tarihi</div>
        <div class="info-value">{{ $invoiceDate }}</div>
        <br>
        <div class="info-label">Vade Tarihi</div>
        <div class="info-value">{{ $dueDate }}</div>
        @if($invoice->paid_at)
        <br>
        <div class="info-label">Odeme Tarihi</div>
        <div class="info-value">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d.m.Y') }}</div>
        @endif
    </div>
</div>

{{-- Fatura Kalemleri --}}
<table class="items">
    <thead>
        <tr>
            <th>Aciklama</th>
            <th>Donem</th>
            <th class="right">Miktar</th>
            <th class="right">Tutar</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <strong>{{ $invoice->plan_name ?? 'Abonelik Paketi' }}</strong><br>
                <span style="font-size:11px; color:#6B7280;">Lattessa SaaS Aboneligi</span>
            </td>
            <td style="font-size:12px; color:#6B7280;">
                @if($invoice->period_start && $invoice->period_end)
                    {{ \Carbon\Carbon::parse($invoice->period_start)->format('d.m.Y') }} -
                    {{ \Carbon\Carbon::parse($invoice->period_end)->format('d.m.Y') }}
                @else
                    Aylik
                @endif
            </td>
            <td class="right">1</td>
            <td class="right"><strong>{{ number_format($invoice->amount ?? 0, 2, ',', '.') }} TL</strong></td>
        </tr>
    </tbody>
</table>

{{-- Toplam --}}
<div class="clearfix">
    <div class="totals">
        @php
            $amount = $invoice->amount ?? 0;
            $tax = $invoice->tax ?? 0;
            $total = $amount + $tax;
        @endphp

        <div class="total-row">
            <span class="total-label">Ara Toplam</span>
            <span class="total-value">{{ number_format($amount, 2, ',', '.') }} TL</span>
        </div>
        @if($tax > 0)
        <div class="total-row">
            <span class="total-label">KDV (%{{ $invoice->tax_rate ?? 20 }})</span>
            <span class="total-value">{{ number_format($tax, 2, ',', '.') }} TL</span>
        </div>
        @endif
        <hr class="thin-divider">
        <div class="total-row total-grand">
            <span class="total-label">TOPLAM</span>
            <span class="total-value">{{ number_format($total, 2, ',', '.') }} TL</span>
        </div>
    </div>
</div>

@if($invoice->notes)
<div class="note-box" style="margin-top: 40px;">
    <div class="note-label">Notlar</div>
    <div class="note-text">{{ $invoice->notes }}</div>
</div>
@endif

{{-- Footer --}}
<div class="footer">
    <div class="footer-text">
        Bu fatura <span class="footer-brand">Lattessa</span> tarafindan otomatik olarak olusturulmustur.<br>
        Sorulariniz icin: info@lattessa.com | lattessa.com
    </div>
</div>

</body>
</html>
