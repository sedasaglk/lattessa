<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $reviews = DB::table('reviews')
            ->leftJoin('customers', 'reviews.customer_id', '=', 'customers.id')
            ->leftJoin('appointments', 'reviews.appointment_id', '=', 'appointments.id')
            ->leftJoin('services', 'appointments.service_id', '=', 'services.id')
            ->where('reviews.tenant_id', $tenant->id)
            ->orderByDesc('reviews.created_at')
            ->select(
                'reviews.*',
                'customers.name as customer_name',
                'customers.phone as customer_phone',
                'services.name as service_name'
            )
            ->paginate(20);

        $stats = DB::table('reviews')
            ->where('tenant_id', $tenant->id)
            ->where('rating', '>', 0)
            ->selectRaw('COUNT(*) as total, AVG(rating) as avg_rating,
                SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN is_published = 0 THEN 1 ELSE 0 END) as hidden')
            ->first();

        return view('panel.reviews.index', compact('tenant', 'reviews', 'stats'));
    }

    public function publish(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('reviews')->where('id', $id)->where('tenant_id', $tenant->id)
            ->update(['is_published' => true, 'updated_at' => now()]);
        return back()->with('success', 'Yorum yayınlandı.');
    }

    public function hide(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('reviews')->where('id', $id)->where('tenant_id', $tenant->id)
            ->update(['is_published' => false, 'updated_at' => now()]);
        return back()->with('success', 'Yorum gizlendi.');
    }

    public function destroy(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('reviews')->where('id', $id)->where('tenant_id', $tenant->id)->delete();
        return back()->with('success', 'Yorum silindi.');
    }
}
