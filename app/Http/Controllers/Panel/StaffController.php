<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StaffController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $staff = DB::table('users')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->whereIn('role', ['firma_sahibi', 'sube_muduru', 'sekreter', 'personel', 'muhasebe'])
            ->orderBy('name')
            ->get();

        // Her personel icin bu ayki performans
        $currentMonth = now()->format('Y-m');
        $staffIds = $staff->pluck('id');

        $monthlyStats = DB::table('appointments')
            ->whereIn('staff_id', $staffIds)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->where('status', 'completed')
            ->whereRaw("DATE_FORMAT(start_time, '%Y-%m') = ?", [$currentMonth])
            ->select(
                'staff_id',
                DB::raw('COUNT(*) as total_appointments'),
                DB::raw('SUM(price) as total_revenue')
            )
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');

        return view('panel.staff.index', compact('tenant', 'staff', 'monthlyStats'));
    }

    public function create(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $branches = DB::table('branches')->where('tenant_id', $tenant->id)->whereNull('deleted_at')->get();
        return view('panel.staff.create', compact('tenant', 'branches'));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:firma_sahibi,sube_muduru,sekreter,personel,muhasebe'],
            'branch_id' => ['nullable', 'integer'],
            'password' => ['required', 'string', 'min:8'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fixed_salary' => ['nullable', 'numeric', 'min:0'],
        ]);

        $userId = DB::table('users')->insertGetId([
            'tenant_id' => $tenant->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Komisyon ayari varsa kaydet
        if (!empty($validated['commission_rate']) || !empty($validated['fixed_salary'])) {
            DB::table('staff_commissions')->insert([
                'tenant_id' => $tenant->id,
                'user_id' => $userId,
                'type' => 'appointment',
                'rate' => $validated['commission_rate'] ?? 0,
                'fixed_amount' => $validated['fixed_salary'] ?? 0,
                'amount' => 0,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Varsayilan calisma saatleri olustur
        $this->createDefaultSchedule($tenant->id, $userId);

        return redirect()
            ->route('panel.staff.show', ['tenant_slug' => $tenant->slug, 'id' => $userId])
            ->with('success', 'Personel basariyla eklendi.');
    }

    public function show(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();

        $member = DB::table('users')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$member) abort(404);

        // Bu ayki istatistikler
        $currentMonth = now()->format('Y-m');
        $monthlyStats = DB::table('appointments')
            ->where('staff_id', $id)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->where('status', 'completed')
            ->whereRaw("DATE_FORMAT(start_time, '%Y-%m') = ?", [$currentMonth])
            ->select(
                DB::raw('COUNT(*) as total_appointments'),
                DB::raw('SUM(price) as total_revenue')
            )
            ->first();

        // Calisma takvimi
        $schedules = DB::table('staff_schedules')
            ->where('user_id', $id)
            ->where('tenant_id', $tenant->id)
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');

        // Izinler
        $leaves = DB::table('staff_leaves')
            ->where('user_id', $id)
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('start_date')
            ->limit(10)
            ->get();

        // Komisyon ayari
        $commission = DB::table('staff_commissions')
            ->where('user_id', $id)
            ->where('tenant_id', $tenant->id)
            ->whereNull('reference_id')
            ->first();

        // Bu ayki toplam komisyon
        $monthlyCommission = DB::table('staff_commissions')
            ->where('user_id', $id)
            ->where('tenant_id', $tenant->id)
            ->where('period', $currentMonth)
            ->whereNotNull('reference_id')
            ->sum('amount');

        $days = ['Pazar', 'Pazartesi', 'Sali', 'Carsamba', 'Persembe', 'Cuma', 'Cumartesi'];

        return view('panel.staff.show', compact(
            'tenant', 'member', 'monthlyStats', 'schedules',
            'leaves', 'commission', 'monthlyCommission', 'days'
        ));
    }

    public function edit(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();
        $member = DB::table('users')->where('id', $id)->where('tenant_id', $tenant->id)->whereNull('deleted_at')->first();
        if (!$member) abort(404);

        $branches = DB::table('branches')->where('tenant_id', $tenant->id)->whereNull('deleted_at')->get();
        $commission = DB::table('staff_commissions')
            ->where('user_id', $id)
            ->where('tenant_id', $tenant->id)
            ->whereNull('reference_id')
            ->first();

        return view('panel.staff.edit', compact('tenant', 'member', 'branches', 'commission'));
    }

    public function update(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        $member = DB::table('users')->where('id', $id)->where('tenant_id', $tenant->id)->whereNull('deleted_at')->first();
        if (!$member) abort(404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', "unique:users,email,{$id}"],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:firma_sahibi,sube_muduru,sekreter,personel,muhasebe'],
            'branch_id' => ['nullable', 'integer'],
            'status' => ['required', 'in:active,inactive'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fixed_salary' => ['nullable', 'numeric', 'min:0'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'branch_id' => $validated['branch_id'] ?? null,
            'status' => $validated['status'],
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => ['min:8']]);
            $updateData['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $id)->update($updateData);

        // Komisyon guncelle
        $existingCommission = DB::table('staff_commissions')
            ->where('user_id', $id)
            ->where('tenant_id', $tenant->id)
            ->whereNull('reference_id')
            ->first();

        if ($existingCommission) {
            DB::table('staff_commissions')
                ->where('id', $existingCommission->id)
                ->update([
                    'rate' => $validated['commission_rate'] ?? 0,
                    'fixed_amount' => $validated['fixed_salary'] ?? 0,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('staff_commissions')->insert([
                'tenant_id' => $tenant->id,
                'user_id' => $id,
                'type' => 'appointment',
                'rate' => $validated['commission_rate'] ?? 0,
                'fixed_amount' => $validated['fixed_salary'] ?? 0,
                'amount' => 0,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('panel.staff.show', ['tenant_slug' => $tenant->slug, 'id' => $id])
            ->with('success', 'Personel guncellendi.');
    }

    public function updateSchedule(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $days = [0, 1, 2, 3, 4, 5, 6];
        foreach ($days as $day) {
            $isWorking = $request->boolean("days.{$day}.is_working");
            $startTime = $request->input("days.{$day}.start_time", '09:00');
            $endTime = $request->input("days.{$day}.end_time", '18:00');

            $existing = DB::table('staff_schedules')
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $id)
                ->where('day_of_week', $day)
                ->first();

            if ($existing) {
                DB::table('staff_schedules')
                    ->where('id', $existing->id)
                    ->update([
                        'is_working' => $isWorking,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('staff_schedules')->insert([
                    'tenant_id' => $tenant->id,
                    'user_id' => $id,
                    'day_of_week' => $day,
                    'is_working' => $isWorking,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return back()->with('success', 'Calisma takvimi guncellendi.');
    }

    public function storeLeave(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'type' => ['required', 'in:annual,sick,unpaid,other'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'gte:start_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);
        $totalDays = $start->diffInDays($end) + 1;

        DB::table('staff_leaves')->insert([
            'tenant_id' => $tenant->id,
            'user_id' => $id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'status' => 'approved',
            'notes' => $validated['notes'] ?? null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "{$totalDays} gunluk izin eklendi.");
    }

    public function destroyLeave(TenantContext $ctx, string $tenant_slug, string $staffId, string $leaveId): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('staff_leaves')
            ->where('id', $leaveId)
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $staffId)
            ->delete();

        return back()->with('success', 'Izin silindi.');
    }

    public function destroy(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('users')->where('id', $id)->where('tenant_id', $tenant->id)->update(['deleted_at' => now()]);
        return redirect()->route('panel.staff.index', ['tenant_slug' => $tenant->slug])->with('success', 'Personel silindi.');
    }

    protected function createDefaultSchedule(int $tenantId, int $userId): void
    {
        $days = [
            ['day' => 1, 'working' => true],
            ['day' => 2, 'working' => true],
            ['day' => 3, 'working' => true],
            ['day' => 4, 'working' => true],
            ['day' => 5, 'working' => true],
            ['day' => 6, 'working' => true],
            ['day' => 0, 'working' => false],
        ];

        foreach ($days as $day) {
            DB::table('staff_schedules')->insert([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'day_of_week' => $day['day'],
                'start_time' => '09:00',
                'end_time' => '18:00',
                'is_working' => $day['working'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
