<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $branch = DB::table('branches')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        $workingHours = $branch && $branch->working_hours
            ? json_decode($branch->working_hours, true)
            : $this->defaultWorkingHours();

        $closedDays = DB::table('closed_days')
            ->where('tenant_id', $tenant->id)
            ->where('end_date', '>=', today()->format('Y-m-d'))
            ->orderBy('start_date')
            ->get();

        return view('panel.settings.index', compact('tenant', 'branch', 'workingHours', 'closedDays'));
    }

    public function updateBusiness(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'business_type' => ['required', 'string'],
            'timezone' => ['required', 'string'],
        ]);

        DB::table('tenants')
            ->where('id', $tenant->id)
            ->update([
                ...$validated,
                'show_price_online' => $request->boolean('show_price_online'),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Isletme bilgileri guncellendi.');
    }

    public function updateBranch(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'branch_name' => ['required', 'string', 'max:255'],
            'branch_phone' => ['nullable', 'string', 'max:20'],
            'branch_address' => ['nullable', 'string', 'max:500'],
        ]);

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $dayNames = ['Pazartesi', 'Sali', 'Carsamba', 'Persembe', 'Cuma', 'Cumartesi', 'Pazar'];
        $workingHours = [];

        foreach ($days as $i => $day) {
            $workingHours[$day] = [
                'name' => $dayNames[$i],
                'is_open' => $request->boolean("days.{$day}.is_open"),
                'start' => $request->input("days.{$day}.start", '09:00'),
                'end' => $request->input("days.{$day}.end", '18:00'),
            ];
        }

        DB::table('branches')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->update([
                'name' => $validated['branch_name'],
                'phone' => $validated['branch_phone'] ?? null,
                'address' => $validated['branch_address'] ?? null,
                'working_hours' => json_encode($workingHours),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Sube bilgileri ve calisma saatleri guncellendi.');
    }

    public function updatePassword(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Mevcut sifre zorunludur.',
            'password.min' => 'Sifre en az 8 karakter olmalidir.',
            'password.confirmed' => 'Sifreler eslesmiyor.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mevcut sifre yanlis.']);
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update(['password' => Hash::make($request->password), 'updated_at' => now()]);

        return back()->with('success', 'Sifreniz guncellendi.');
    }

    protected function defaultWorkingHours(): array
    {
        $days = [
            'monday' => 'Pazartesi',
            'tuesday' => 'Sali',
            'wednesday' => 'Carsamba',
            'thursday' => 'Persembe',
            'friday' => 'Cuma',
            'saturday' => 'Cumartesi',
            'sunday' => 'Pazar',
        ];

        $hours = [];
        foreach ($days as $key => $name) {
            $hours[$key] = [
                'name' => $name,
                'is_open' => $key !== 'sunday',
                'start' => '09:00',
                'end' => '18:00',
            ];
        }

        return $hours;
    }

    public function storeClosedDay(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'title'      => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'gte:start_date'],
        ], [
            'title.required'      => 'Başlık giriniz.',
            'start_date.required' => 'Başlangıç tarihi seçiniz.',
            'end_date.required'   => 'Bitiş tarihi seçiniz.',
            'end_date.gte'        => 'Bitiş tarihi başlangıçtan önce olamaz.',
        ]);

        DB::table('closed_days')->insert([
            'tenant_id'  => $tenant->id,
            'title'      => $validated['title'],
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Kapalı gün eklendi.');
    }

    public function destroyClosedDay(TenantContext $ctx, string $tenant_slug, int $id): RedirectResponse
    {
        $tenant = $ctx->get();

        DB::table('closed_days')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->delete();

        return back()->with('success', 'Kapalı gün silindi.');
    }
}
