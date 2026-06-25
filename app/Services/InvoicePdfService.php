<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class InvoicePdfService
{
    public function generate(int $invoiceId): \Barryvdh\DomPDF\PDF
    {
        $invoice = DB::table('invoices')
            ->join('tenants', 'invoices.tenant_id', '=', 'tenants.id')
            ->where('invoices.id', $invoiceId)
            ->select('invoices.*', 'tenants.company_name', 'tenants.phone', 'tenants.email', 'tenants.address')
            ->first();

        if (!$invoice) {
            abort(404, 'Fatura bulunamadi.');
        }

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'isFontSubsettingEnabled' => true,
            ]);

        return $pdf;
    }

    public function download(int $invoiceId): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($invoiceId);
        $invoice = DB::table('invoices')->where('id', $invoiceId)->first();
        $filename = 'fatura-' . ($invoice->invoice_number ?? $invoiceId) . '.pdf';

        return $pdf->download($filename);
    }

    public function stream(int $invoiceId): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($invoiceId);
        $invoice = DB::table('invoices')->where('id', $invoiceId)->first();
        $filename = 'fatura-' . ($invoice->invoice_number ?? $invoiceId) . '.pdf';

        return $pdf->stream($filename);
    }

    public function saveToStorage(int $invoiceId): string
    {
        $pdf = $this->generate($invoiceId);
        $invoice = DB::table('invoices')->where('id', $invoiceId)->first();
        $filename = 'fatura-' . ($invoice->invoice_number ?? $invoiceId) . '.pdf';
        $path = 'invoices/' . $filename;

        \Illuminate\Support\Facades\Storage::put('public/' . $path, $pdf->output());

        return $path;
    }
}
