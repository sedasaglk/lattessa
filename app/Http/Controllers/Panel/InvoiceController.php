<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\InvoicePdfService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $invoices = DB::table('invoices')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->get();

        return view('panel.invoices.index', compact('tenant', 'invoices'));
    }

    public function download(TenantContext $ctx, string $tenant_slug, int $id, InvoicePdfService $pdfService)
    {
        $tenant = $ctx->get();

        $invoice = DB::table('invoices')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$invoice) abort(404);

        return $pdfService->download($id);
    }

    public function show(TenantContext $ctx, string $tenant_slug, int $id, InvoicePdfService $pdfService)
    {
        $tenant = $ctx->get();

        $invoice = DB::table('invoices')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$invoice) abort(404);

        return $pdfService->stream($id);
    }
}
