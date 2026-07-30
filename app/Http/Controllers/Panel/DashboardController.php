<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $user   = auth()->user();

        // Personel / sekreter / şube müdürü → kısıtlı dashboard
        if ($user->role !== 'firma_sahibi') {
            return $this->staffDashboard($tenant, $user);
        }

        // Bugunun randevulari
        $todayAppointmentList = DB::table('appointments')
            ->join('customers', 'appointments.customer_id', '=', 'customers.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('users', 'appointments.staff_id', '=', 'users.id')
            ->where('appointments.tenant_id', $tenant->id)
            ->whereNull('appointments.deleted_at')
            ->whereDate('appointments.start_time', today())
            ->select(
                'appointments.id',
                'appointments.start_time',
                'appointments.status',
                'appointments.price',
                'customers.name as customer_name',
                'services.name as service_name',
                'users.name as staff_name'
            )
            ->orderBy('appointments.start_time')
            ->get();

        $todayAppointments = $todayAppointmentList->count();
        $pendingAppointments = $todayAppointmentList->whereIn('status', ['pending', 'confirmed'])->count();

        // Bugunun cirosu (tamamlanan randevular + satislar)
        $todayRevenue = DB::table('cash_transactions')
            ->where('tenant_id', $tenant->id)
            ->where('type', 'income')
            ->whereDate('created_at', today())
            ->sum('amount');

        $monthRevenue = DB::table('cash_transactions')
            ->where('tenant_id', $tenant->id)
            ->where('type', 'income')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Musteriler
        $totalCustomers = DB::table('customers')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->count();

        $newCustomersThisMonth = DB::table('customers')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Personel
        $activeStaff = DB::table('users')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->whereIn('role', ['personel', 'firma_sahibi', 'sube_muduru', 'sekreter'])
            ->count();

        $totalStaff = DB::table('users')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->whereIn('role', ['personel', 'firma_sahibi', 'sube_muduru', 'sekreter'])
            ->count();

        // Son musteriler
        $recentCustomers = DB::table('customers')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'name', 'phone')
            ->get();

        return view('panel.dashboard', compact(
            'tenant',
            'todayAppointments',
            'pendingAppointments',
            'todayRevenue',
            'monthRevenue',
            'totalCustomers',
            'newCustomersThisMonth',
            'activeStaff',
            'totalStaff',
            'todayAppointmentList',
            'recentCustomers'
        ));
    }

    private function staffDashboard($tenant, $user): View
    {
        $userId = $user->id;
        $now    = now();
        $period = $now->format('Y-m');

        // Bugünkü randevularım
        $todayAppointments = DB::table('appointments')
            ->join('customers', 'appointments.customer_id', '=', 'customers.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->where('appointments.tenant_id', $tenant->id)
            ->where('appointments.staff_id', $userId)
            ->whereNull('appointments.deleted_at')
            ->whereDate('appointments.start_time', today())
            ->orderBy('appointments.start_time')
            ->select(
                'appointments.id',
                'appointments.start_time',
                'appointments.end_time',
                'appointments.status',
                'appointments.price',
                'customers.name as customer_name',
                'services.name as service_name'
            )
            ->get();

        // Bu ay istatistikler
        $monthStats = DB::table('appointments')
            ->where('tenant_id', $tenant->id)
            ->where('staff_id', $userId)
            ->whereNull('deleted_at')
            ->whereMonth('start_time', $now->month)
            ->whereYear('start_time', $now->year)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status='no_show' THEN 1 ELSE 0 END) as no_show,
                SUM(CASE WHEN status='completed' THEN price ELSE 0 END) as revenue
            ")
            ->first();

        // Bu hafta istatistikler
        $weekStats = DB::table('appointments')
            ->where('tenant_id', $tenant->id)
            ->where('staff_id', $userId)
            ->whereNull('deleted_at')
            ->whereBetween('start_time', [$now->startOfWeek()->copy(), $now->endOfWeek()->copy()])
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status='completed' THEN price ELSE 0 END) as revenue
            ")
            ->first();

        // Bu ay prim
        $monthCommission = DB::table('staff_commissions')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $userId)
            ->where('period', $period)
            ->sum('amount');

        // Sabit maaş
        $fixedSalary = DB::table('staff_commissions')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $userId)
            ->where('type', 'fixed')
            ->value('fixed_amount') ?? 0;

        // Çalışma takvimi (bu hafta)
        $dayNames = [0=>'Paz',1=>'Pzt',2=>'Sal',3=>'Çar',4=>'Per',5=>'Cum',6=>'Cmt'];
        $schedules = DB::table('staff_schedules')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $userId)
            ->get()
            ->keyBy('day_of_week');

        // İzinler (aktif)
        $activeLeaves = DB::table('staff_leaves')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $userId)
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->first();

        return view('panel.dashboard-staff', compact(
            'tenant', 'user',
            'todayAppointments',
            'monthStats', 'weekStats',
            'monthCommission', 'fixedSalary',
            'schedules', 'dayNames',
            'activeLeaves', 'period'
        ));
    }
}
