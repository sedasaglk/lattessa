<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use App\Services\WhatsApp\VatanWhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class WhatsAppConnectionController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $connection = DB::table('whatsapp_connections')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->first();

        return view('panel.whatsapp.index', compact('tenant', 'connection'));
    }

    public function startPhoneLogin(Request $request, TenantContext $ctx, string $tenant_slug, VatanWhatsAppService $service): RedirectResponse
    {
        $tenant = $ctx->get();

        $request->validate(['phone' => ['required', 'string']]);

        $result = $service->loginWithPhone($request->phone);

        if (!$result['success']) {
            return back()->with('error', 'Giris baslatilamadi: ' . ($result['response']['description'] ?? 'Bilinmeyen hata'));
        }

        // Eski baglantilari pasiflestir
        DB::table('whatsapp_connections')
            ->where('tenant_id', $tenant->id)
            ->update(['status' => 'disconnected', 'updated_at' => now()]);

        DB::table('whatsapp_connections')->insert([
            'tenant_id' => $tenant->id,
            'reg_id' => $result['reg_id'],
            'phone_number' => $request->phone,
            'status' => 'pending',
            'pairing_code' => $result['code'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Eslestirme kodu: {$result['code']}. WhatsApp'taki bildirime tiklayip bu kodu 30 saniye icinde girin.");
    }

    public function startQrLogin(TenantContext $ctx, string $tenant_slug, VatanWhatsAppService $service): RedirectResponse
    {
        $tenant = $ctx->get();

        $result = $service->loginWithQr();

        if (!$result['success']) {
            return back()->with('error', 'QR olusturulamadi: ' . ($result['response']['description'] ?? 'Bilinmeyen hata'));
        }

        DB::table('whatsapp_connections')
            ->where('tenant_id', $tenant->id)
            ->update(['status' => 'disconnected', 'updated_at' => now()]);

        DB::table('whatsapp_connections')->insert([
            'tenant_id' => $tenant->id,
            'reg_id' => $result['reg_id'],
            'status' => 'pending',
            'qr_code' => $result['qr'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'QR kod olusturuldu. WhatsApp uygulamanizdan okutun.');
    }

    public function checkStatus(TenantContext $ctx, string $tenant_slug, string $id, VatanWhatsAppService $service): JsonResponse
    {
        $tenant = $ctx->get();

        $connection = DB::table('whatsapp_connections')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$connection || !$connection->reg_id) {
            return response()->json(['connected' => false]);
        }

        $result = $service->checkActiveLogin($connection->reg_id);

        if ($result['is_connected']) {
            $data = $result['response']['data']['data'] ?? $result['response']['data'] ?? [];
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

    public function disconnect(TenantContext $ctx, string $tenant_slug, string $id, VatanWhatsAppService $service): RedirectResponse
    {
        $tenant = $ctx->get();

        $connection = DB::table('whatsapp_connections')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->first();

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
