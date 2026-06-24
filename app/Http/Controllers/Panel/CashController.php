<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class CashController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $month = request('month', now()->format('Y-m'));
        $monthStart = Carbon::parse($month . '-01')->startOfMonth();
        $monthEnd = Carbon::parse($month . '-01')->endOfMonth();

        $transactions = DB::table('cash_transactions')
            ->leftJoin('cash_categories', 'cash_transactions.category_id', '=', 'cash_categories.id')
            ->leftJoin('customers', 'cash_transactions.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'cash_transactions.created_by', '=', 'users.id')
            ->where('cash_transactions.tenant_id', $tenant->id)
            ->whereNull('cash_transactions.deleted_at')
            ->whereBetween('cash_transactions.transaction_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->orderByDesc('cash_transactions.transaction_date')
            ->orderByDesc('cash_transactions.id')
            ->select(
                'cash_transactions.*',
                'cash_categories.name as category_name',
                'customers.name as customer_name',
                'users.name as created_by_name'
            )
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        $branches = DB::table('branches')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->get();

        $categories = DB::table('cash_categories')
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        return view('panel.cash.index', compact(
            'tenant', 'transactions', 'totalIncome',
            'totalExpense', 'netBalance', 'month',
            'branches', 'categories'
        ));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'integer'],
            'payment_method' => ['required', 'in:cash,card,transfer'],
            'branch_id' => ['required', 'integer'],
            'transaction_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'integer'],
        ], [
            'type.required' => 'Islem turu secmelisiniz.',
            'amount.required' => 'Tutar zorunludur.',
            'amount.min' => 'Tutar 0dan buyuk olmalidir.',
            'payment_method.required' => 'Odeme yontemi secmelisiniz.',
            'branch_id.required' => 'Sube secmelisiniz.',
            'transaction_date.required' => 'Tarih zorunludur.',
        ]);

        DB::table('cash_transactions')->insert([
            'tenant_id' => $tenant->id,
            'branch_id' => $validated['branch_id'],
            'type' => $validated['type'],
            'category_id' => $validated['category_id'] ?? null,
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'payment_method' => $validated['payment_method'],
            'customer_id' => $validated['customer_id'] ?? null,
            'created_by' => auth()->id(),
            'transaction_date' => $validated['transaction_date'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('panel.cash.index', ['tenant_slug' => $tenant->slug])
            ->with('success', $validated['type'] === 'income' ? 'Gelir kaydedildi.' : 'Gider kaydedildi.');
    }

    public function destroy(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $transaction = DB::table('cash_transactions')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$transaction) abort(404);

        DB::table('cash_transactions')
            ->where('id', $id)
            ->update(['deleted_at' => now()]);

        return back()->with('success', 'Islem silindi.');
    }

    public function storeCategory(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:income,expense'],
        ]);

        DB::table('cash_categories')->insert([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Kategori eklendi.');
    }
}
