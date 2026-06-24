<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function sendToCustomer(
        Request $request,
        TenantContext $ctx,
        string $tenant_slug,
        string $customerId,
        NotificationService $notificationService
    ): RedirectResponse {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'channel' => ['required', 'in:auto,whatsapp,sms'],
        ]);

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$customer) abort(404);

        $result = $notificationService->notify(
            $tenant->id,
            $customer->phone,
            $validated['message'],
            'general',
            $customer->id,
            $validated['channel']
        );

        if ($result['success']) {
            $channelLabel = $result['channel'] === 'whatsapp' ? 'WhatsApp' : 'SMS';
            return back()->with('success', "Mesaj {$channelLabel} ile gonderildi.");
        }

        return back()->with('error', 'Mesaj gonderilemedi. Saglayici baglantisini kontrol edin.');
    }
}
