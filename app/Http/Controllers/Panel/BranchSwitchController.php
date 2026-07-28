<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\BranchContext;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchSwitchController extends Controller
{
    public function switch(Request $request, TenantContext $ctx, BranchContext $branchCtx, string $tenant_slug): RedirectResponse
    {
        $user = auth()->user();
        if ($user->role !== 'firma_sahibi') abort(403);

        $branchId = $request->input('branch_id');
        $tenant = $ctx->get();

        if ($branchId !== null && $branchId !== '') {
            $exists = DB::table('branches')
                ->where('id', $branchId)
                ->where('tenant_id', $tenant->id)
                ->whereNull('deleted_at')
                ->exists();
            if (!$exists) abort(403);
        }

        $branchCtx->switchTo($branchId ? (int) $branchId : null);
        return redirect()->back()->with('success', 'Şube değiştirildi.');
    }
}
