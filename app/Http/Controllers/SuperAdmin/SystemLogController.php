<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SystemLogController extends Controller
{
    public function index(): View
    {
        $type = request('type', 'activity');

        // Aktivite logları (tenant bazlı)
        $activityLogs = DB::table('activity_logs')
            ->join('tenants', 'activity_logs.tenant_id', '=', 'tenants.id')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select(
                'activity_logs.*',
                'tenants.company_name',
                'tenants.slug',
                'users.name as user_name'
            )
            ->orderByDesc('activity_logs.created_at')
            ->limit(100)
            ->get();

        // Laravel log dosyası (son 100 satır)
        $laravelLog = [];
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            $lines = array_slice(file($logPath), -100);
            $laravelLog = array_reverse($lines);
        }

        // SMS log ozeti
        $smsStats = DB::table('sms_logs')
            ->select(
                DB::raw('DATE(created_at) as date'),
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date', 'status')
            ->orderByDesc('date')
            ->get();

        // Sistem ozeti
        $systemStats = [
            'total_tenants' => DB::table('tenants')->count(),
            'active_tenants' => DB::table('tenants')->where('status', 'active')->count(),
            'trial_tenants' => DB::table('tenants')->where('status', 'trial')->count(),
            'total_appointments_today' => DB::table('appointments')
                ->whereDate('start_time', today())
                ->whereNull('deleted_at')
                ->count(),
            'total_sms_today' => DB::table('sms_logs')
                ->whereDate('created_at', today())
                ->count(),
            'failed_sms_today' => DB::table('sms_logs')
                ->whereDate('created_at', today())
                ->where('status', 'failed')
                ->count(),
        ];

        return view('super-admin.logs.index', compact(
            'type', 'activityLogs', 'laravelLog', 'smsStats', 'systemStats'
        ));
    }
}
