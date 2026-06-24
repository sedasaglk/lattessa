<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $period = request('period', 'this_month');
        [$startDate, $endDate] = $this->getPeriodDates($period);

        // Ciro (kasa gelirleri)
        $totalRevenue = DB::table('cash_transactions')
            ->where('tenant_id', $tenant->id)
            ->where('type', 'income')
            ->whereNull('deleted_at')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // Gider
        $totalExpense = DB::table('cash_transactions')
            ->where('tenant_id', $tenant->id)
            ->where('type', 'expense')
            ->whereNull('deleted_at')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // Randevu istatistikleri
        $appointmentStats = DB::table('appointments')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw('DATE(start_time)'), [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled'),
                DB::raw('SUM(CASE WHEN status = "no_show" THEN 1 ELSE 0 END) as no_show'),
                DB::raw('SUM(CASE WHEN source = "online" THEN 1 ELSE 0 END) as online')
            )
            ->first();

        // Yeni musteri sayisi
        $newCustomers = DB::table('customers')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->count();

        // Personel performansi
        $staffPerformance = DB::table('appointments')
            ->join('users', 'appointments.staff_id', '=', 'users.id')
            ->where('appointments.tenant_id', $tenant->id)
            ->whereNull('appointments.deleted_at')
            ->where('appointments.status', 'completed')
            ->whereBetween(DB::raw('DATE(appointments.start_time)'), [$startDate, $endDate])
            ->select(
                'users.name as staff_name',
                DB::raw('COUNT(*) as total_appointments'),
                DB::raw('SUM(appointments.price) as total_revenue')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_revenue')
            ->get();

        // Hizmet performansi
        $servicePerformance = DB::table('appointments')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->where('appointments.tenant_id', $tenant->id)
            ->whereNull('appointments.deleted_at')
            ->where('appointments.status', 'completed')
            ->whereBetween(DB::raw('DATE(appointments.start_time)'), [$startDate, $endDate])
            ->select(
                'services.name as service_name',
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(appointments.price) as total_revenue')
            )
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total_count')
            ->get();

        // Gunluk ciro (son 30 gun veya secilen period)
        $dailyRevenue = DB::table('cash_transactions')
            ->where('tenant_id', $tenant->id)
            ->where('type', 'income')
            ->whereNull('deleted_at')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->select(
                'transaction_date',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get()
            ->keyBy('transaction_date');

        // Gunluk randevu sayisi
        $dailyAppointments = DB::table('appointments')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(start_time)'), [$startDate, $endDate])
            ->select(
                DB::raw('DATE(start_time) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('DATE(start_time)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        return view('panel.reports.index', compact(
            'tenant', 'period', 'startDate', 'endDate',
            'totalRevenue', 'totalExpense', 'appointmentStats',
            'newCustomers', 'staffPerformance', 'servicePerformance',
            'dailyRevenue', 'dailyAppointments'
        ));
    }

    protected function getPeriodDates(string $period): array
    {
        return match ($period) {
            'today' => [today()->format('Y-m-d'), today()->format('Y-m-d')],
            'this_week' => [now()->startOfWeek()->format('Y-m-d'), now()->endOfWeek()->format('Y-m-d')],
            'this_month' => [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
            'last_month' => [now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
            'this_year' => [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')],
            default => [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
        };
    }
}
