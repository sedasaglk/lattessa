<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SmsProviderController extends Controller
{
    public function index(): View
    {
        $providers = DB::table('sms_providers')
            ->whereNull('tenant_id')
            ->orderBy('priority')
            ->get();

        // Son 7 gun sms istatistikleri
        $stats = DB::table('sms_logs')
            ->where('created_at', '>=', now()->subDays(7))
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $recentLogs = DB::table('sms_logs')
            ->join('tenants', 'sms_logs.tenant_id', '=', 'tenants.id')
            ->select('sms_logs.*', 'tenants.company_name')
            ->orderByDesc('sms_logs.created_at')
            ->limit(20)
            ->get();

        return view('super-admin.sms.index', compact('providers', 'stats', 'recentLogs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'in:vatansms,netgsm,iletimerkezi,mutlucell'],
            'display_name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'sender' => ['required', 'string', 'max:20'],
            'priority' => ['required', 'integer', 'min:1'],
        ]);

        $credentials = json_encode([
            'username' => $validated['username'],
            'password' => $validated['password'],
            'sender' => $validated['sender'],
        ]);

        DB::table('sms_providers')->insert([
            'tenant_id' => null,
            'provider' => $validated['provider'],
            'display_name' => $validated['display_name'],
            'credentials' => $credentials,
            'is_active' => true,
            'is_system_default' => false,
            'priority' => $validated['priority'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'SMS saglayici eklendi.');
    }

    public function setDefault(string $id): RedirectResponse
    {
        // Onceki default'u kaldir
        DB::table('sms_providers')
            ->whereNull('tenant_id')
            ->update(['is_system_default' => false]);

        DB::table('sms_providers')
            ->where('id', $id)
            ->update(['is_system_default' => true, 'updated_at' => now()]);

        return back()->with('success', 'Varsayilan SMS saglayici guncellendi.');
    }

    public function toggle(string $id): RedirectResponse
    {
        $provider = DB::table('sms_providers')->where('id', $id)->first();
        if (!$provider) abort(404);

        DB::table('sms_providers')->where('id', $id)->update([
            'is_active' => !$provider->is_active,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Saglayici durumu guncellendi.');
    }

    public function destroy(string $id): RedirectResponse
    {
        DB::table('sms_providers')->where('id', $id)->whereNull('tenant_id')->delete();
        return back()->with('success', 'Saglayici silindi.');
    }
}
