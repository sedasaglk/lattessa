<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class WaitingListController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $waiting = DB::table('waiting_list')
            ->leftJoin('customers', 'waiting_list.customer_id', '=', 'customers.id')
            ->leftJoin('services', 'waiting_list.service_id', '=', 'services.id')
            ->leftJoin('users', 'waiting_list.staff_id', '=', 'users.id')
            ->where('waiting_list.tenant_id', $tenant->id)
            ->whereIn('waiting_list.status', ['waiting', 'notified'])
            ->select(
                'waiting_list.*',
                'services.name as service_name',
                'users.name as staff_name'
            )
            ->orderBy('waiting_list.preferred_date')
            ->orderBy('waiting_list.created_at')
            ->get();

        $history = DB::table('waiting_list')
            ->leftJoin('services', 'waiting_list.service_id', '=', 'services.id')
            ->where('waiting_list.tenant_id', $tenant->id)
            ->whereIn('waiting_list.status', ['booked', 'cancelled'])
            ->select('waiting_list.*', 'services.name as service_name')
            ->orderByDesc('waiting_list.updated_at')
            ->limit(20)
            ->get();

        $services = DB::table('services')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $staff = DB::table('users')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->whereIn('role', ['firma_sahibi', 'sube_muduru', 'personel'])
            ->orderBy('name')
            ->get();

        $branches = DB::table('branches')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->get();

        return view('panel.waiting-list.index', compact(
            'tenant', 'waiting', 'history', 'services', 'staff', 'branches'
        ));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'branch_id' => ['required', 'integer'],
            'service_id' => ['nullable', 'integer'],
            'staff_id' => ['nullable', 'integer'],
            'preferred_date' => ['nullable', 'date'],
            'preferred_time' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Musteri kayitli mi kontrol et
        $customer = DB::table('customers')
            ->where('tenant_id', $tenant->id)
            ->where('phone', $validated['customer_phone'])
            ->whereNull('deleted_at')
            ->first();

        DB::table('waiting_list')->insert([
            'tenant_id' => $tenant->id,
            'branch_id' => $validated['branch_id'],
            'customer_id' => $customer?->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'service_id' => $validated['service_id'] ?? null,
            'staff_id' => $validated['staff_id'] ?? null,
            'preferred_date' => $validated['preferred_date'] ?? null,
            'preferred_time' => $validated['preferred_time'] ?? null,
            'status' => 'waiting',
            'notes' => $validated['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Bekleme listesine eklendi.');
    }

    public function notify(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $entry = DB::table('waiting_list')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$entry) abort(404);

        // SMS/email bildirimi (deploy sonrasi aktif olacak)
        \Illuminate\Support\Facades\Log::info("Bekleme listesi bildirimi: {$entry->customer_phone} - {$entry->customer_name}");

        DB::table('waiting_list')
            ->where('id', $id)
            ->update([
                'status' => 'notified',
                'notified_at' => now(),
                'updated_at' => now(),
            ]);

        return back()->with('success', "{$entry->customer_name} bilgilendirildi.");
    }

    public function book(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        DB::table('waiting_list')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->update([
                'status' => 'booked',
                'updated_at' => now(),
            ]);

        $entry = DB::table('waiting_list')->find($id);

        // Randevu olusturma sayfasina yonlendir
        return redirect()
            ->route('panel.appointments.create', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Bekleme listesinden randevuya alindi. Lutfen randevuyu tamamlayin.');
    }

    public function cancel(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        DB::table('waiting_list')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Bekleme listesinden cikarildi.');
    }
}
