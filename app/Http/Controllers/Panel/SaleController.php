<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SaleController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $date = request('date', today()->format('Y-m-d'));

        $sales = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'sales.staff_id', '=', 'users.id')
            ->where('sales.tenant_id', $tenant->id)
            ->whereDate('sales.created_at', $date)
            ->select(
                'sales.*',
                'customers.name as customer_name',
                'users.name as staff_name'
            )
            ->orderByDesc('sales.created_at')
            ->get();

        $todayTotal = $sales->where('status', 'completed')->sum('total_amount');
        $todayCount = $sales->where('status', 'completed')->count();

        return view('panel.sales.index', compact(
            'tenant', 'sales', 'date', 'todayTotal', 'todayCount'
        ));
    }

    public function create(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $customers = DB::table('customers')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $products = DB::table('products')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->orderBy('name')
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
            ->whereIn('role', ['firma_sahibi', 'sube_muduru', 'personel', 'sekreter'])
            ->orderBy('name')
            ->get();

        $branches = DB::table('branches')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->get();

        // Stok seviyeleri
        $stockLevels = DB::table('stock_movements')
            ->where('tenant_id', $tenant->id)
            ->select('product_id', DB::raw('SUM(CASE WHEN type = "in" OR type = "adjustment" THEN quantity WHEN type = "out" THEN -quantity ELSE 0 END) as stock'))
            ->groupBy('product_id')
            ->pluck('stock', 'product_id');

        return view('panel.sales.create', compact(
            'tenant', 'customers', 'products', 'services', 'staff', 'branches', 'stockLevels'
        ));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $request->validate([
            'branch_id' => ['required', 'integer'],
            'payment_method' => ['required', 'in:cash,card,transfer,mixed'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:product,service'],
            'items.*.item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $items = $request->items;
        $subtotal = 0;
        $discountTotal = 0;

        $itemsData = [];
        foreach ($items as $item) {
            $discount = $item['discount'] ?? 0;
            $total = ($item['unit_price'] * $item['quantity']) - $discount;
            $subtotal += $item['unit_price'] * $item['quantity'];
            $discountTotal += $discount;

            // Urun adi al
            if ($item['item_type'] === 'product') {
                $obj = DB::table('products')->find($item['item_id']);
            } else {
                $obj = DB::table('services')->find($item['item_id']);
            }

            $itemsData[] = [
                'item_type' => $item['item_type'],
                'item_id' => $item['item_id'],
                'item_name' => $obj->name ?? 'Bilinmiyor',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount' => $discount,
                'total_price' => $total,
            ];
        }

        $totalAmount = $subtotal - $discountTotal;

        $saleId = DB::table('sales')->insertGetId([
            'tenant_id' => $tenant->id,
            'branch_id' => $request->branch_id,
            'customer_id' => $request->customer_id ?: null,
            'staff_id' => $request->staff_id ?: null,
            'subtotal' => $subtotal,
            'discount_amount' => $discountTotal,
            'tax_amount' => 0,
            'total_amount' => $totalAmount,
            'payment_method' => $request->payment_method,
            'status' => 'completed',
            'notes' => $request->notes,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($itemsData as $item) {
            DB::table('sale_items')->insert([
                'sale_id' => $saleId,
                ...$item,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Urun ise stok dusur
            if ($item['item_type'] === 'product') {
                $warehouse = DB::table('warehouses')->where('tenant_id', $tenant->id)->first();
                if ($warehouse) {
                    DB::table('stock_movements')->insert([
                        'tenant_id' => $tenant->id,
                        'product_id' => $item['item_id'],
                        'warehouse_id' => $warehouse->id,
                        'type' => 'out',
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'reference_type' => 'sale',
                        'reference_id' => $saleId,
                        'notes' => "Satis #{$saleId}",
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Kasaya gelir kaydet
        $branch = DB::table('branches')->where('tenant_id', $tenant->id)->first();
        DB::table('cash_transactions')->insert([
            'tenant_id' => $tenant->id,
            'branch_id' => $request->branch_id,
            'type' => 'income',
            'category_id' => null,
            'amount' => $totalAmount,
            'description' => "Satis #{$saleId} - Otomatik",
            'payment_method' => $request->payment_method,
            'customer_id' => $request->customer_id ?: null,
            'reference_type' => 'sale',
            'reference_id' => $saleId,
            'created_by' => auth()->id(),
            'transaction_date' => today()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Musteri istatistiklerini guncelle
        if ($request->customer_id) {
            DB::table('customers')
                ->where('id', $request->customer_id)
                ->where('tenant_id', $tenant->id)
                ->update([
                    'total_spent' => DB::raw("total_spent + {$totalAmount}"),
                    'updated_at' => now(),
                ]);
        }

        return redirect()
            ->route('panel.sales.show', ['tenant_slug' => $tenant->slug, 'id' => $saleId])
            ->with('success', 'Satis basariyla tamamlandi.');
    }

    public function show(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();

        $sale = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'sales.staff_id', '=', 'users.id')
            ->leftJoin('branches', 'sales.branch_id', '=', 'branches.id')
            ->where('sales.id', $id)
            ->where('sales.tenant_id', $tenant->id)
            ->select('sales.*', 'customers.name as customer_name', 'customers.phone as customer_phone', 'users.name as staff_name', 'branches.name as branch_name')
            ->first();

        if (!$sale) abort(404);

        $items = DB::table('sale_items')->where('sale_id', $id)->get();

        return view('panel.sales.show', compact('tenant', 'sale', 'items'));
    }

    public function refund(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $sale = DB::table('sales')->where('id', $id)->where('tenant_id', $tenant->id)->first();
        if (!$sale) abort(404);

        DB::table('sales')->where('id', $id)->update(['status' => 'refunded', 'updated_at' => now()]);

        // Kasaya iade kaydet
        DB::table('cash_transactions')->insert([
            'tenant_id' => $tenant->id,
            'branch_id' => $sale->branch_id,
            'type' => 'expense',
            'amount' => $sale->total_amount,
            'description' => "Satis #{$id} iadesi",
            'payment_method' => $sale->payment_method,
            'reference_type' => 'sale_refund',
            'reference_id' => $id,
            'created_by' => auth()->id(),
            'transaction_date' => today()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Satis iade edildi.');
    }
}
