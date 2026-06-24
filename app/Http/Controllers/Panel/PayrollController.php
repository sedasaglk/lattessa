<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PayrollController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $period = request('period', now()->format('Y-m'));

        $staff = DB::table('users')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->whereIn('role', ['firma_sahibi', 'sube_muduru', 'sekreter', 'personel', 'muhasebe'])
            ->orderBy('name')
            ->get();

        // Her personel icin bu donem bordro
        $payrolls = DB::table('staff_payroll')
            ->where('tenant_id', $tenant->id)
            ->where('period', $period)
            ->get()
            ->keyBy('user_id');

        // Bordrosu olmayan personel icin canli hesaplama
        $staffWithPayroll = $staff->map(function ($member) use ($tenant, $period, $payrolls) {
            if (isset($payrolls[$member->id])) {
                return array_merge((array) $member, ['payroll' => $payrolls[$member->id]]);
            }

            $calculated = $this->calculatePayroll($tenant->id, $member->id, $period);
            return array_merge((array) $member, ['payroll' => (object) array_merge(['id' => null, 'status' => 'draft'], $calculated)]);
        });

        // Toplam ozet
        $totalBase = $staffWithPayroll->sum(fn($s) => $s['payroll']->base_salary ?? 0);
        $totalCommission = $staffWithPayroll->sum(fn($s) => $s['payroll']->commission_total ?? 0);
        $totalNet = $staffWithPayroll->sum(fn($s) => $s['payroll']->net_total ?? 0);

        return view('panel.payroll.index', compact(
            'tenant', 'period', 'staffWithPayroll', 'totalBase', 'totalCommission', 'totalNet'
        ));
    }

    public function show(TenantContext $ctx, string $tenant_slug, string $userId): View
    {
        $tenant = $ctx->get();
        $period = request('period', now()->format('Y-m'));

        $member = DB::table('users')
            ->where('id', $userId)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$member) abort(404);

        $payroll = DB::table('staff_payroll')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $userId)
            ->where('period', $period)
            ->first();

        $calculated = $this->calculatePayroll($tenant->id, $userId, $period);

        // Bu donem randevu detaylari
        [$year, $month] = explode('-', $period);
        $appointments = DB::table('appointments')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('customers', 'appointments.customer_id', '=', 'customers.id')
            ->where('appointments.tenant_id', $tenant->id)
            ->where('appointments.staff_id', $userId)
            ->whereNull('appointments.deleted_at')
            ->where('appointments.status', 'completed')
            ->whereYear('appointments.start_time', $year)
            ->whereMonth('appointments.start_time', $month)
            ->select(
                'appointments.id',
                'appointments.start_time',
                'appointments.price',
                'services.name as service_name',
                'customers.name as customer_name'
            )
            ->orderBy('appointments.start_time')
            ->get();

        // Komisyon ayari
        $commission = DB::table('staff_commissions')
            ->where('user_id', $userId)
            ->where('tenant_id', $tenant->id)
            ->whereNull('reference_id')
            ->first();

        // Gecmis bordro
        $history = DB::table('staff_payroll')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $userId)
            ->orderByDesc('period')
            ->limit(12)
            ->get();

        return view('panel.payroll.show', compact(
            'tenant', 'member', 'period', 'payroll', 'calculated',
            'appointments', 'commission', 'history'
        ));
    }

    public function generate(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $request->validate([
            'period' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'user_id' => ['nullable', 'integer'],
        ]);

        $period = $request->period;

        if ($request->user_id) {
            // Tek personel icin
            $this->generatePayroll($tenant->id, $request->user_id, $period, $request);
        } else {
            // Tum personel icin
            $staff = DB::table('users')
                ->where('tenant_id', $tenant->id)
                ->whereNull('deleted_at')
                ->where('status', 'active')
                ->whereIn('role', ['firma_sahibi', 'sube_muduru', 'sekreter', 'personel', 'muhasebe'])
                ->get();

            foreach ($staff as $member) {
                $this->generatePayroll($tenant->id, $member->id, $period, null);
            }
        }

        return back()->with('success', "{$period} donemi bordrosu olusturuldu.");
    }

    public function update(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $payroll = DB::table('staff_payroll')->where('id', $id)->where('tenant_id', $tenant->id)->first();
        if (!$payroll) abort(404);

        $netTotal = $payroll->base_salary + $payroll->commission_total
            + ($validated['bonus'] ?? 0) - ($validated['deductions'] ?? 0);

        DB::table('staff_payroll')->where('id', $id)->update([
            'bonus' => $validated['bonus'] ?? 0,
            'deductions' => $validated['deductions'] ?? 0,
            'net_total' => $netTotal,
            'notes' => $validated['notes'] ?? null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Bordro guncellendi.');
    }

    public function markPaid(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $payroll = DB::table('staff_payroll')->where('id', $id)->where('tenant_id', $tenant->id)->first();
        if (!$payroll) abort(404);

        DB::table('staff_payroll')->where('id', $id)->update([
            'status' => 'paid',
            'paid_at' => now(),
            'updated_at' => now(),
        ]);

        // Kasaya gider kaydet
        DB::table('cash_transactions')->insert([
            'tenant_id' => $tenant->id,
            'branch_id' => DB::table('branches')->where('tenant_id', $tenant->id)->value('id'),
            'type' => 'expense',
            'amount' => $payroll->net_total,
            'description' => "Maas odemesi - " . DB::table('users')->where('id', $payroll->user_id)->value('name') . " ({$payroll->period})",
            'payment_method' => 'transfer',
            'reference_type' => 'payroll',
            'reference_id' => $id,
            'created_by' => auth()->id(),
            'transaction_date' => today()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Maas odendi ve kasaya gider kaydedildi.');
    }

    protected function calculatePayroll(int $tenantId, int $userId, string $period): array
    {
        [$year, $month] = explode('-', $period);

        // Tamamlanan randevular
        $appointments = DB::table('appointments')
            ->where('tenant_id', $tenantId)
            ->where('staff_id', $userId)
            ->whereNull('deleted_at')
            ->where('status', 'completed')
            ->whereYear('start_time', $year)
            ->whereMonth('start_time', $month)
            ->get();

        $appointmentRevenue = $appointments->sum('price');
        $appointmentCount = $appointments->count();

        // Komisyon ayari
        $commissionSetting = DB::table('staff_commissions')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->whereNull('reference_id')
            ->first();

        $baseSalary = $commissionSetting->fixed_amount ?? 0;
        $commissionRate = $commissionSetting->rate ?? 0;
        $commissionTotal = $commissionRate > 0 ? round($appointmentRevenue * ($commissionRate / 100), 2) : 0;
        $netTotal = $baseSalary + $commissionTotal;

        return [
            'base_salary' => $baseSalary,
            'commission_total' => $commissionTotal,
            'bonus' => 0,
            'deductions' => 0,
            'net_total' => $netTotal,
            'appointment_count' => $appointmentCount,
            'appointment_revenue' => $appointmentRevenue,
            'period' => $period,
        ];
    }

    protected function generatePayroll(int $tenantId, int $userId, string $period, ?Request $request): void
    {
        $calculated = $this->calculatePayroll($tenantId, $userId, $period);

        $bonus = $request?->input("bonus.{$userId}", 0) ?? 0;
        $deductions = $request?->input("deductions.{$userId}", 0) ?? 0;
        $notes = $request?->input("notes.{$userId}") ?? null;
        $netTotal = $calculated['base_salary'] + $calculated['commission_total'] + $bonus - $deductions;

        DB::table('staff_payroll')->updateOrInsert(
            ['tenant_id' => $tenantId, 'user_id' => $userId, 'period' => $period],
            [
                'base_salary' => $calculated['base_salary'],
                'commission_total' => $calculated['commission_total'],
                'bonus' => $bonus,
                'deductions' => $deductions,
                'net_total' => $netTotal,
                'appointment_count' => $calculated['appointment_count'],
                'appointment_revenue' => $calculated['appointment_revenue'],
                'status' => 'draft',
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
