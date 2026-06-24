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
}
