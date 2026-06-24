<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\VatanWhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class WhatsAppController extends Controller
{
    public function index(): View
    {
        $connection = DB::table('whatsapp_connections')
            ->whereNull('tenant_id')
            ->orderByDesc('created_at')
            ->first();

        $recentLogs = DB::table('whatsapp_logs')
            ->join('tenants', 'whatsapp_logs.tenant_id', '=', 'tenants.id')
            ->select('whatsapp_logs.*', 'tenants.company_name')
            ->orderByDesc('whatsapp_logs.created_at')
            ->limit(20)
            ->get();

        $stats = DB::table('whatsapp_logs')
            ->where('created_at', '>=', now()->subDays(7))
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('super-admin.whatsapp.index', compact('connection', 'recentLogs', 'stats'));
    }

    public function startPhoneLogin(Request $request, VatanWhatsAppService $service): RedirectResponse
    {
        $request->validate(['phone' => ['required', 'string']]);

        $result = $service->loginWithPhone($request->phone);

        if (!$result['success']) {
            return back()->with('error', 'Giris baslatilamadi: ' . ($result['response']['description'] ?? 'Bilinmeyen hata'));
        }

        DB::table('whatsapp_connections')->insert([
            'tenant_id' => null,
            'reg_id' => $result['reg_id'],
            'phone_number' => $request->phone,
            'status' => 'pending',
            'pairing_code' => $result['code'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Eslestime kodu: {$result['code']}. WhatsApp'taki bildirime tiklayip bu kodu 30 saniye icinde girin.");
    }

    public function startQrLogin(VatanWhatsAppService $service): RedirectResponse
    {
        $result = $service->loginWithQr();

        if (!$result['success']) {
            return back()->with('error', 'QR olusturulamadi: ' . ($result['response']['description'] ?? 'Bilinmeyen hata'));
        }

        DB::table('whatsapp_connections')->insert([
            'tenant_id' => null,
            'reg_id' => $result['reg_id'],
            'status' => 'pending',
            'qr_code' => $result['qr'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'QR kod olusturuldu. WhatsApp uygulamanizdan okutun.');
    }

    public function checkStatus(string $id, VatanWhatsAppService $service): JsonResponse
    {
        $connection = DB::table('whatsapp_connections')->where('id', $id)->first();

        if (!$connection || !$connection->reg_id) {
            return response()->json(['connected' => false]);
        }

        $result = $service->checkActiveLogin($connection->reg_id);

        if ($result['is_connected']) {
            $data = $result['response']['data'] ?? [];
            DB::table('whatsapp_connections')->where('id', $id)->update([
                'status' => 'connected',
                'phone_number' => $data['device_number'] ?? $connection->phone_number,
                'platform' => $data['platform'] ?? null,
                'user_name' => $data['user_name'] ?? null,
                'connected_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['connected' => $result['is_connected']]);
    }

    public function disconnect(string $id, VatanWhatsAppService $service): RedirectResponse
    {
        $connection = DB::table('whatsapp_connections')->where('id', $id)->first();

        if ($connection && $connection->reg_id) {
            $service->logout($connection->reg_id);
        }

        DB::table('whatsapp_connections')->where('id', $id)->update([
            'status' => 'disconnected',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'WhatsApp baglantisi kesildi.');
    }
}
