<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BranchController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $branches = DB::table('branches')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->get();

        $period = request('period', 'this_month');
        [$startDate, $endDate] = $this->getPeriodDates($period);

        // Her sube icin istatistikler
        $branchStats = [];
        foreach ($branches as $branch) {
            $revenue = DB::table('cash_transactions')
                ->where('tenant_id', $tenant->id)
                ->where('branch_id', $branch->id)
                ->where('type', 'income')
                ->whereNull('deleted_at')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $appointments = DB::table('appointments')
                ->where('tenant_id', $tenant->id)
                ->where('branch_id', $branch->id)
                ->whereNull('deleted_at')
                ->where('status', 'completed')
                ->whereBetween(DB::raw('DATE(start_time)'), [$startDate, $endDate])
                ->count();

            $staffCount = DB::table('users')
                ->where('tenant_id', $tenant->id)
                ->where('branch_id', $branch->id)
                ->whereNull('deleted_at')
                ->where('status', 'active')
                ->count();

            $branchStats[$branch->id] = [
                'revenue' => $revenue,
                'appointments' => $appointments,
                'staff_count' => $staffCount,
            ];
        }

        return view('panel.branches.index', compact(
            'tenant', 'branches', 'branchStats', 'period', 'startDate', 'endDate'
        ));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        DB::table('branches')->insert([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Sube eklendi.');
    }

    public function update(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        DB::table('branches')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->update([...$validated, 'updated_at' => now()]);

        return back()->with('success', 'Sube guncellendi.');
    }

    public function destroy(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('branches')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->update(['deleted_at' => now()]);

        return back()->with('success', 'Sube silindi.');
    }

    protected function getPeriodDates(string $period): array
    {
        return match ($period) {
            'today' => [today()->format('Y-m-d'), today()->format('Y-m-d')],
            'this_week' => [now()->startOfWeek()->format('Y-m-d'), now()->endOfWeek()->format('Y-m-d')],
            'this_month' => [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
            'last_month' => [now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
            default => [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
        };
    }
}
