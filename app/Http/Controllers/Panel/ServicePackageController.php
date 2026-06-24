<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ServicePackageController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $packages = DB::table('service_packages')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Her paket icin icindeki hizmetler
        $packageItems = DB::table('service_package_items')
            ->join('services', 'service_package_items.service_id', '=', 'services.id')
            ->whereIn('service_package_items.package_id', $packages->pluck('id'))
            ->select('service_package_items.*', 'services.name as service_name')
            ->get()
            ->groupBy('package_id');

        // Satis istatistikleri
        $salesStats = DB::table('customer_packages')
            ->where('tenant_id', $tenant->id)
            ->select('package_id', DB::raw('COUNT(*) as sold_count'))
            ->groupBy('package_id')
            ->pluck('sold_count', 'package_id');

        $services = DB::table('services')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('panel.service-packages.index', compact(
            'tenant', 'packages', 'packageItems', 'salesStats', 'services'
        ));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'validity_days' => ['required', 'integer', 'min:1'],
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'integer'],
            'services.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $packageId = DB::table('service_packages')->insertGetId([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'validity_days' => $validated['validity_days'],
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($validated['services'] as $item) {
            DB::table('service_package_items')->insert([
                'package_id' => $packageId,
                'service_id' => $item['service_id'],
                'quantity' => $item['quantity'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Paket olusturuldu.');
    }

    public function destroy(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('service_packages')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->update(['deleted_at' => now()]);

        return back()->with('success', 'Paket silindi.');
    }

    // Musteri paketleri
    public function customerPackages(TenantContext $ctx, string $tenant_slug, string $customerId): View
    {
        $tenant = $ctx->get();

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$customer) abort(404);

        $customerPackages = DB::table('customer_packages')
            ->join('service_packages', 'customer_packages.package_id', '=', 'service_packages.id')
            ->where('customer_packages.tenant_id', $tenant->id)
            ->where('customer_packages.customer_id', $customerId)
            ->select('customer_packages.*', 'service_packages.name as package_name', 'service_packages.validity_days')
            ->orderByDesc('customer_packages.created_at')
            ->get();

        // Her musteri paketi icin kullanim detaylari
        $usageDetails = [];
        foreach ($customerPackages as $cp) {
            $packageItems = DB::table('service_package_items')
                ->join('services', 'service_package_items.service_id', '=', 'services.id')
                ->where('service_package_items.package_id', $cp->package_id)
                ->select('service_package_items.*', 'services.name as service_name')
                ->get();

            $usages = DB::table('customer_package_usages')
                ->where('customer_package_id', $cp->id)
                ->get();

            $usageDetails[$cp->id] = [
                'items' => $packageItems,
                'usages' => $usages->groupBy('service_id'),
            ];
        }

        $packages = DB::table('service_packages')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->get();

        return view('panel.service-packages.customer', compact(
            'tenant', 'customer', 'customerPackages', 'usageDetails', 'packages'
        ));
    }

    public function sellToCustomer(Request $request, TenantContext $ctx, string $tenant_slug, string $customerId): RedirectResponse
    {
        $tenant = $ctx->get();

        $request->validate([
            'package_id' => ['required', 'integer'],
        ]);

        $package = DB::table('service_packages')
            ->where('id', $request->package_id)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$package) abort(404);

        // Musteri paketini olustur
        $customerPackageId = DB::table('customer_packages')->insertGetId([
            'tenant_id' => $tenant->id,
            'customer_id' => $customerId,
            'package_id' => $package->id,
            'expires_at' => now()->addDays($package->validity_days)->format('Y-m-d'),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kasaya gelir kaydet
        $branch = DB::table('branches')->where('tenant_id', $tenant->id)->first();
        DB::table('cash_transactions')->insert([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch?->id,
            'type' => 'income',
            'amount' => $package->price,
            'description' => "Paket satisi: {$package->name}",
            'payment_method' => $request->payment_method ?? 'cash',
            'customer_id' => $customerId,
            'reference_type' => 'customer_package',
            'reference_id' => $customerPackageId,
            'created_by' => auth()->id(),
            'transaction_date' => today()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Musteri istatistiklerini guncelle
        DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenant->id)
            ->update([
                'total_spent' => DB::raw("total_spent + {$package->price}"),
                'updated_at' => now(),
            ]);

        return back()->with('success', "'{$package->name}' paketi musteriye tanimi yapildi.");
    }

    public function useSession(Request $request, TenantContext $ctx, string $tenant_slug, string $customerPackageId): RedirectResponse
    {
        $tenant = $ctx->get();

        $request->validate([
            'service_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $customerPackage = DB::table('customer_packages')
            ->where('id', $customerPackageId)
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->first();

        if (!$customerPackage) {
            return back()->with('error', 'Gecerli bir paket bulunamadi.');
        }

        // Paket suresi dolmus mu?
        if (now()->isAfter($customerPackage->expires_at)) {
            DB::table('customer_packages')->where('id', $customerPackageId)->update(['status' => 'expired', 'updated_at' => now()]);
            return back()->with('error', 'Bu paketin suresi dolmus.');
        }

        // Kullanim hakki var mi?
        $packageItem = DB::table('service_package_items')
            ->where('package_id', $customerPackage->package_id)
            ->where('service_id', $request->service_id)
            ->first();

        if (!$packageItem) {
            return back()->with('error', 'Bu hizmet pakette yer almiyor.');
        }

        $usedCount = DB::table('customer_package_usages')
            ->where('customer_package_id', $customerPackageId)
            ->where('service_id', $request->service_id)
            ->sum('quantity');

        if ($usedCount >= $packageItem->quantity) {
            return back()->with('error', 'Bu hizmet icin paket hakki tukenmis.');
        }

        DB::table('customer_package_usages')->insert([
            'tenant_id' => $tenant->id,
            'customer_package_id' => $customerPackageId,
            'service_id' => $request->service_id,
            'appointment_id' => $request->appointment_id ?? null,
            'used_by' => auth()->id(),
            'quantity' => 1,
            'notes' => $request->notes ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tum haklar tukendi mi kontrol et
        $this->checkPackageCompletion($tenant->id, $customerPackageId);

        return back()->with('success', 'Seans kullanildi.');
    }

    protected function checkPackageCompletion(int $tenantId, int $customerPackageId): void
    {
        $customerPackage = DB::table('customer_packages')->find($customerPackageId);
        $packageItems = DB::table('service_package_items')
            ->where('package_id', $customerPackage->package_id)
            ->get();

        $allCompleted = true;
        foreach ($packageItems as $item) {
            $used = DB::table('customer_package_usages')
                ->where('customer_package_id', $customerPackageId)
                ->where('service_id', $item->service_id)
                ->sum('quantity');

            if ($used < $item->quantity) {
                $allCompleted = false;
                break;
            }
        }

        if ($allCompleted) {
            DB::table('customer_packages')
                ->where('id', $customerPackageId)
                ->update(['status' => 'completed', 'updated_at' => now()]);
        }
    }
}
