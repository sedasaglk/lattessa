<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CrmController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $tags = DB::table('customer_tags')
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        $tagCounts = DB::table('customer_tag_pivot')
            ->join('customer_tags', 'customer_tag_pivot.tag_id', '=', 'customer_tags.id')
            ->where('customer_tags.tenant_id', $tenant->id)
            ->select('tag_id', DB::raw('COUNT(*) as count'))
            ->groupBy('tag_id')
            ->pluck('count', 'tag_id');

        $segments = $this->getSegments($tenant->id);

        return view('panel.crm.index', compact('tenant', 'tags', 'tagCounts', 'segments'));
    }

    public function storeTag(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string'],
        ]);

        try {
            DB::table('customer_tags')->insert([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'color' => $request->color,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Bu etiket zaten mevcut.');
        }

        return back()->with('success', 'Etiket eklendi.');
    }

    public function destroyTag(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('customer_tags')->where('id', $id)->where('tenant_id', $tenant->id)->delete();
        return back()->with('success', 'Etiket silindi.');
    }

    public function customers(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $search = request('search');
        $tagId = request('tag_id');
        $segment = request('segment');

        $query = DB::table('customers')
            ->leftJoin('loyalty_tiers', 'customers.loyalty_tier_id', '=', 'loyalty_tiers.id')
            ->where('customers.tenant_id', $tenant->id)
            ->whereNull('customers.deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'like', "%{$search}%")
                  ->orWhere('customers.phone', 'like', "%{$search}%")
                  ->orWhere('customers.email', 'like', "%{$search}%");
            });
        }

        if ($tagId) {
            $query->join('customer_tag_pivot', function ($join) use ($tagId) {
                $join->on('customer_tag_pivot.customer_id', '=', 'customers.id')
                     ->where('customer_tag_pivot.tag_id', '=', $tagId);
            });
        }

        match($segment) {
            'vip' => $query->where('customers.total_spent', '>=', 5000),
            'loyal' => $query->where('customers.visit_count', '>=', 10),
            'inactive' => $query->where('customers.last_visit_at', '<=', now()->subDays(60)),
            'new' => $query->where('customers.created_at', '>=', now()->subDays(30)),
            'birthday' => $query->whereMonth('customers.birth_date', now()->month),
            default => null,
        };

        $customers = $query->select(
            'customers.*',
            'loyalty_tiers.name as tier_name',
            'loyalty_tiers.color as tier_color'
        )
        ->orderByDesc('customers.total_spent')
        ->paginate(20);

        // Her musterinin etiketleri - duzeltilmis sorgu
        $customerIds = $customers->pluck('id')->toArray();

        $customerTags = collect();
        if (!empty($customerIds)) {
            $customerTags = DB::table('customer_tag_pivot')
                ->join('customer_tags', 'customer_tag_pivot.tag_id', '=', 'customer_tags.id')
                ->whereIn('customer_tag_pivot.customer_id', $customerIds)
                ->where('customer_tags.tenant_id', $tenant->id)
                ->select(
                    'customer_tag_pivot.customer_id',
                    'customer_tag_pivot.tag_id',
                    'customer_tags.name',
                    'customer_tags.color'
                )
                ->get()
                ->groupBy('customer_id');
        }

        $tags = DB::table('customer_tags')->where('tenant_id', $tenant->id)->orderBy('name')->get();
        $segments = $this->getSegments($tenant->id);

        return view('panel.crm.customers', compact(
            'tenant', 'customers', 'customerTags', 'tags',
            'segments', 'search', 'tagId', 'segment'
        ));
    }

    public function updateCustomerTags(Request $request, TenantContext $ctx, string $tenant_slug, string $customerId): RedirectResponse
    {
        $tenant = $ctx->get();

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$customer) abort(404);

        DB::table('customer_tag_pivot')->where('customer_id', $customerId)->delete();

        $tagIds = $request->input('tag_ids', []);
        foreach ($tagIds as $tagId) {
            $tag = DB::table('customer_tags')
                ->where('id', $tagId)
                ->where('tenant_id', $tenant->id)
                ->first();

            if ($tag) {
                try {
                    DB::table('customer_tag_pivot')->insert([
                        'customer_id' => $customerId,
                        'tag_id' => $tagId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    // duplicate ignore
                }
            }
        }

        return back()->with('success', 'Etiketler guncellendi.');
    }

    public function addNote(Request $request, TenantContext $ctx, string $tenant_slug, string $customerId): RedirectResponse
    {
        $tenant = $ctx->get();

        $request->validate(['note' => ['required', 'string', 'max:500']]);

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$customer) abort(404);

        $notes = json_decode($customer->notes ?? '[]', true) ?: [];
        $notes[] = [
            'text' => $request->note,
            'created_by' => auth()->user()->name,
            'created_at' => now()->format('d.m.Y H:i'),
        ];

        DB::table('customers')
            ->where('id', $customerId)
            ->update([
                'notes' => json_encode($notes),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Not eklendi.');
    }

    protected function getSegments(int $tenantId): array
    {
        return [
            'vip' => [
                'label' => 'VIP Musteriler',
                'description' => '5.000 TL+ harcama',
                'color' => 'amber',
                'count' => DB::table('customers')->where('tenant_id', $tenantId)->whereNull('deleted_at')->where('total_spent', '>=', 5000)->count(),
            ],
            'loyal' => [
                'label' => 'Sadik Musteriler',
                'description' => '10+ ziyaret',
                'color' => 'green',
                'count' => DB::table('customers')->where('tenant_id', $tenantId)->whereNull('deleted_at')->where('visit_count', '>=', 10)->count(),
            ],
            'inactive' => [
                'label' => 'Kayip Musteriler',
                'description' => '60+ gundur gelmeyen',
                'color' => 'red',
                'count' => DB::table('customers')->where('tenant_id', $tenantId)->whereNull('deleted_at')->where('last_visit_at', '<=', now()->subDays(60))->count(),
            ],
            'new' => [
                'label' => 'Yeni Musteriler',
                'description' => 'Son 30 gunde kayit',
                'color' => 'blue',
                'count' => DB::table('customers')->where('tenant_id', $tenantId)->whereNull('deleted_at')->where('created_at', '>=', now()->subDays(30))->count(),
            ],
            'birthday' => [
                'label' => 'Bu Ay Dogum Gunu',
                'description' => now()->format('F') . ' ayi',
                'color' => 'purple',
                'count' => DB::table('customers')->where('tenant_id', $tenantId)->whereNull('deleted_at')->whereMonth('birth_date', now()->month)->count(),
            ],
        ];
    }
}
