<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InventoryController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $search = request('search');
        $categoryId = request('category_id');

        $products = DB::table('products')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('suppliers', 'products.supplier_id', '=', 'suppliers.id')
            ->where('products.tenant_id', $tenant->id)
            ->whereNull('products.deleted_at')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('products.name', 'like', "%{$search}%")
                       ->orWhere('products.barcode', 'like', "%{$search}%")
                       ->orWhere('products.sku', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn($q) => $q->where('products.category_id', $categoryId))
            ->select('products.*', 'product_categories.name as category_name', 'suppliers.name as supplier_name')
            ->orderBy('products.name')
            ->paginate(20);

        $productIds = $products->pluck('id');
        $stockLevels = DB::table('stock_movements')
            ->whereIn('product_id', $productIds)
            ->where('tenant_id', $tenant->id)
            ->select('product_id', DB::raw('SUM(CASE WHEN type = "in" OR type = "adjustment" THEN quantity WHEN type = "out" THEN -quantity ELSE 0 END) as stock'))
            ->groupBy('product_id')
            ->pluck('stock', 'product_id');

        $categories = DB::table('product_categories')
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        return view('panel.inventory.index', compact(
            'tenant', 'products', 'stockLevels', 'categories', 'search', 'categoryId'
        ));
    }

    public function categories(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $categories = DB::table('product_categories')
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();
        return view('panel.inventory.categories', compact('tenant', 'categories'));
    }

    public function storeCategory(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();
        $request->validate(['name' => ['required', 'string', 'max:100']]);

        DB::table('product_categories')->insert([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Kategori eklendi.');
    }

    public function destroyCategory(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('product_categories')->where('id', $id)->where('tenant_id', $tenant->id)->delete();
        return back()->with('success', 'Kategori silindi.');
    }

    public function create(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $categories = DB::table('product_categories')->where('tenant_id', $tenant->id)->orderBy('name')->get();
        $suppliers = DB::table('suppliers')->where('tenant_id', $tenant->id)->whereNull('deleted_at')->orderBy('name')->get();
        return view('panel.inventory.create', compact('tenant', 'categories', 'suppliers'));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'supplier_id' => ['nullable', 'integer'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string'],
            'min_stock_level' => ['required', 'integer', 'min:0'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
        ]);

        $productId = DB::table('products')->insertGetId([
            'tenant_id' => $tenant->id,
            'category_id' => $validated['category_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'name' => $validated['name'],
            'barcode' => $validated['barcode'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'purchase_price' => $validated['purchase_price'],
            'sale_price' => $validated['sale_price'],
            'unit' => $validated['unit'],
            'min_stock_level' => $validated['min_stock_level'],
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!empty($validated['initial_stock']) && $validated['initial_stock'] > 0) {
            $warehouse = $this->getOrCreateWarehouse($tenant->id);
            DB::table('stock_movements')->insert([
                'tenant_id' => $tenant->id,
                'product_id' => $productId,
                'warehouse_id' => $warehouse->id,
                'type' => 'in',
                'quantity' => $validated['initial_stock'],
                'unit_price' => $validated['purchase_price'],
                'notes' => 'Baslangic stoku',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('panel.inventory.index', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Urun basariyla eklendi.');
    }

    public function show(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();

        $product = DB::table('products')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('suppliers', 'products.supplier_id', '=', 'suppliers.id')
            ->where('products.id', $id)
            ->where('products.tenant_id', $tenant->id)
            ->whereNull('products.deleted_at')
            ->select('products.*', 'product_categories.name as category_name', 'suppliers.name as supplier_name')
            ->first();

        if (!$product) abort(404);

        $warehouse = $this->getOrCreateWarehouse($tenant->id);

        $currentStock = DB::table('stock_movements')
            ->where('product_id', $id)
            ->where('tenant_id', $tenant->id)
            ->selectRaw('SUM(CASE WHEN type = "in" OR type = "adjustment" THEN quantity WHEN type = "out" THEN -quantity ELSE 0 END) as stock')
            ->value('stock') ?? 0;

        $movements = DB::table('stock_movements')
            ->leftJoin('users', 'stock_movements.created_by', '=', 'users.id')
            ->where('stock_movements.product_id', $id)
            ->where('stock_movements.tenant_id', $tenant->id)
            ->select('stock_movements.*', 'users.name as created_by_name')
            ->orderByDesc('stock_movements.created_at')
            ->limit(20)
            ->get();

        return view('panel.inventory.show', compact('tenant', 'product', 'currentStock', 'movements', 'warehouse'));
    }

    public function edit(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();
        $product = DB::table('products')->where('id', $id)->where('tenant_id', $tenant->id)->whereNull('deleted_at')->first();
        if (!$product) abort(404);

        $categories = DB::table('product_categories')->where('tenant_id', $tenant->id)->orderBy('name')->get();
        $suppliers = DB::table('suppliers')->where('tenant_id', $tenant->id)->whereNull('deleted_at')->orderBy('name')->get();

        return view('panel.inventory.edit', compact('tenant', 'product', 'categories', 'suppliers'));
    }

    public function update(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        $product = DB::table('products')->where('id', $id)->where('tenant_id', $tenant->id)->whereNull('deleted_at')->first();
        if (!$product) abort(404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'supplier_id' => ['nullable', 'integer'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string'],
            'min_stock_level' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
        ]);

        DB::table('products')->where('id', $id)->where('tenant_id', $tenant->id)->update([
            ...$validated,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('panel.inventory.show', ['tenant_slug' => $tenant->slug, 'id' => $id])
            ->with('success', 'Urun guncellendi.');
    }

    public function addStock(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        $product = DB::table('products')->where('id', $id)->where('tenant_id', $tenant->id)->whereNull('deleted_at')->first();
        if (!$product) abort(404);

        $validated = $request->validate([
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $warehouse = $this->getOrCreateWarehouse($tenant->id);

        DB::table('stock_movements')->insert([
            'tenant_id' => $tenant->id,
            'product_id' => $id,
            'warehouse_id' => $warehouse->id,
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'] ?? 0,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Stok hareketi eklendi.');
    }

    public function destroy(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('products')->where('id', $id)->where('tenant_id', $tenant->id)->update(['deleted_at' => now()]);
        return redirect()->route('panel.inventory.index', ['tenant_slug' => $tenant->slug])->with('success', 'Urun silindi.');
    }

    public function suppliers(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $suppliers = DB::table('suppliers')->where('tenant_id', $tenant->id)->whereNull('deleted_at')->orderBy('name')->get();
        return view('panel.inventory.suppliers', compact('tenant', 'suppliers'));
    }

    public function storeSupplier(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::table('suppliers')->insert([
            'tenant_id' => $tenant->id,
            ...$validated,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Tedarikci eklendi.');
    }

    public function destroySupplier(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('suppliers')->where('id', $id)->where('tenant_id', $tenant->id)->update(['deleted_at' => now()]);
        return back()->with('success', 'Tedarikci silindi.');
    }

    protected function getOrCreateWarehouse(int $tenantId): object
    {
        $warehouse = DB::table('warehouses')->where('tenant_id', $tenantId)->first();
        if (!$warehouse) {
            $id = DB::table('warehouses')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => 'Merkez Depo',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $warehouse = DB::table('warehouses')->find($id);
        }
        return $warehouse;
    }
}
