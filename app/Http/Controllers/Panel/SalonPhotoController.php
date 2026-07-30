<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SalonPhotoController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug)
    {
        $tenant = $ctx->get();
        $photos = DB::table('salon_photos')
            ->where('tenant_id', $tenant->id)
            ->orderBy('order')
            ->get();
        return view('panel.salon.photos', compact('tenant', 'photos'));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug)
    {
        $tenant = $ctx->get();
        $request->validate(['photos.*' => ['required', 'image', 'max:4096']]);

        $maxOrder = DB::table('salon_photos')->where('tenant_id', $tenant->id)->max('order') ?? 0;

        foreach ($request->file('photos') as $file) {
            $path = $file->store("tenants/{$tenant->id}/salon", 'public');
            DB::table('salon_photos')->insert([
                'tenant_id' => $tenant->id,
                'path' => $path,
                'order' => ++$maxOrder,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Fotoğraflar yüklendi.');
    }

    public function destroy(TenantContext $ctx, string $tenant_slug, int $id)
    {
        $tenant = $ctx->get();
        $photo = DB::table('salon_photos')->where('id', $id)->where('tenant_id', $tenant->id)->first();
        if (!$photo) abort(404);

        Storage::disk('public')->delete($photo->path);
        DB::table('salon_photos')->where('id', $id)->delete();

        return back()->with('success', 'Fotoğraf silindi.');
    }
}
