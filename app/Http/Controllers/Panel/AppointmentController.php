<?php

namespace App\Http\Controllers\Panel;

use App\Events\AppointmentCompleted;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\RecurringAppointmentService;
use App\Services\TenantContext;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentService $appointmentService,
        protected RecurringAppointmentService $recurringService
    ) {}

    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $view = request('view', 'grid');
        $date = request('date', today()->format('Y-m-d'));

        if ($view === 'list') {
            $appointments = Appointment::with(['customer', 'service', 'staff', 'branch'])
                ->whereDate('start_time', $date)
                ->orderBy('start_time')
                ->get();
        } else {
            $appointments = collect();
        }

        $branches = Branch::where('tenant_id', $tenant->id)->where('status', 'active')->get();

        $staffColors = [
            '#6366F1', '#EC4899', '#F59E0B', '#10B981', '#3B82F6',
            '#8B5CF6', '#EF4444', '#14B8A6', '#F97316', '#84CC16',
        ];
        $branchCtx = app(BranchContext::class);
        $branchCtx->setFromUser();
        $staffQuery = User::whereIn('role', ['personel', 'firma_sahibi', 'sube_muduru'])
            ->where('tenant_id', $tenant->id)
            ->where(function($q) { $q->where('status', 'active')->orWhere('role', 'firma_sahibi'); })
            ->orderBy('name');
        if ($branchCtx->getBranchId()) {
            $staffQuery->where(function($q) use ($branchCtx) {
                $q->where('branch_id', $branchCtx->getBranchId())->orWhere('role', 'firma_sahibi');
            });
        }
        $staffMembers = $staffQuery->get();
        $staffColorMap = [];
        foreach ($staffMembers as $i => $member) {
            $staffColorMap[$member->id] = $staffColors[$i % count($staffColors)];
        }

        return view('panel.appointments.index', compact(
            'tenant', 'appointments', 'date', 'branches', 'view', 'staffMembers', 'staffColorMap'
        ));
    }

    public function calendarEvents(TenantContext $ctx, string $tenant_slug): JsonResponse
    {
        $tenant = $ctx->get();

        $start = request('start', now()->startOfMonth()->format('Y-m-d'));
        $end = request('end', now()->endOfMonth()->format('Y-m-d'));
        $staffId = request('staff_id');

        $appointments = DB::table('appointments')
            ->join('customers', 'appointments.customer_id', '=', 'customers.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('users', 'appointments.staff_id', '=', 'users.id')
            ->where('appointments.tenant_id', $tenant->id)
            ->whereNull('appointments.deleted_at')
            ->whereBetween(DB::raw('DATE(appointments.start_time)'), [$start, $end])
            ->when($staffId, fn($q) => $q->where('appointments.staff_id', $staffId))
            ->select(
                'appointments.id',
                'appointments.start_time',
                'appointments.end_time',
                'appointments.status',
                'appointments.price',
                'appointments.is_recurring',
                'customers.name as customer_name',
                'services.name as service_name',
                'users.name as staff_name',
                'users.id as staff_id'
            )
            ->get();

        // Personel renk paleti
        $staffColors = [
            '#6366F1', '#EC4899', '#F59E0B', '#10B981', '#3B82F6',
            '#8B5CF6', '#EF4444', '#14B8A6', '#F97316', '#84CC16',
        ];

        // Personellere renk ata
        $staffColorMap = [];
        $staffIds = $appointments->pluck('staff_id')->unique()->values();
        foreach ($staffIds as $i => $sid) {
            $staffColorMap[$sid] = $staffColors[$i % count($staffColors)];
        }

        $events = $appointments->map(function ($appt) use ($tenant_slug, $staffColorMap) {
            $color = $staffColorMap[$appt->staff_id] ?? '#6366F1';

            // Iptal ve tamamlanan randevular soluk gosterilsin
            $opacity = match($appt->status) {
                'cancelled', 'no_show' => true,
                default => false,
            };

            $title = "{$appt->customer_name}";
            if ($appt->is_recurring) $title = "↻ " . $title;

            $borderColor = $opacity ? '#9CA3AF' : $color;
            $bgColor = $opacity ? '#9CA3AF' : $color;

            return [
                'id' => $appt->id,
                'title' => $title,
                'start' => $appt->start_time,
                'end' => $appt->end_time,
                'backgroundColor' => $bgColor,
                'borderColor' => $borderColor,
                'textColor' => '#ffffff',
                'url' => "/{$tenant_slug}/randevular/{$appt->id}",
                'extendedProps' => [
                    'customer' => $appt->customer_name,
                    'service' => $appt->service_name,
                    'staff' => $appt->staff_name,
                    'staff_id' => $appt->staff_id,
                    'staff_color' => $color,
                    'status' => $appt->status,
                    'price' => $appt->price,
                    'is_recurring' => $appt->is_recurring,
                ],
            ];
        });

        return response()->json($events);
    }

    public function create(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $authUser = auth()->user();

        $customers = Customer::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $services = Service::where('tenant_id', $tenant->id)->where('status', 'active')->orderBy('name')->get();
        $branches = Branch::where('tenant_id', $tenant->id)->where('status', 'active')->get();
        $defaultDate = request('date', now()->format('Y-m-d\TH:i'));

        // Firma sahibi tüm personeli görebilir; diğerleri sadece kendileri adına randevu girer
        if ($authUser->role === 'firma_sahibi') {
            $staff = User::whereIn('role', ['personel', 'firma_sahibi', 'sube_muduru'])
                ->where('tenant_id', $tenant->id)
                ->orderBy('name')
                ->get();
        } else {
            $staff = User::where('id', $authUser->id)->get();
        }

        // Kullanıcının şubesi (firma_sahibi için null olabilir, o zaman tüm şubeler gösterilir)
        $userBranchId = $authUser->branch_id;

        return view('panel.appointments.create', compact(
            'tenant', 'customers', 'services', 'staff', 'branches', 'defaultDate', 'userBranchId', 'authUser'
        ));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $isGroup = $request->boolean('is_group');

        $rules = [
            'branch_id'        => ['required'],
            'staff_id'         => ['required'],
            'service_id'       => ['required'],
            'start_time'       => ['required', 'date', 'after:now'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'is_recurring'     => ['nullable', 'boolean'],
            'recurrence_rule'  => ['nullable', 'in:daily,weekly,biweekly,monthly'],
            'recurrence_count' => ['nullable', 'integer', 'min:2', 'max:52'],
        ];

        if ($isGroup) {
            $rules['customer_ids']   = ['required', 'array', 'min:1'];
            $rules['customer_ids.*'] = ['required', 'integer'];
            $rules['group_capacity'] = ['required', 'integer', 'min:1', 'max:500'];
        } else {
            $rules['customer_id'] = ['required'];
        }

        $validated = $request->validate($rules, [
            'branch_id.required'      => 'Sube secmelisiniz.',
            'customer_id.required'    => 'Musteri secmelisiniz.',
            'customer_ids.required'   => 'En az bir musteri eklemelisiniz.',
            'group_capacity.required' => 'Grup kapasitesi giriniz.',
            'staff_id.required'       => 'Personel secmelisiniz.',
            'service_id.required'     => 'Hizmet secmelisiniz.',
            'start_time.required'     => 'Tarih ve saat secmelisiniz.',
            'start_time.after'        => 'Gecmis bir tarih secemezsiniz.',
        ]);

        try {
            // --- GRUP RANDEVUSU ---
            if ($isGroup) {
                $service   = Service::find($validated['service_id']);
                $startTime = \Carbon\Carbon::parse($validated['start_time']);
                $endTime   = $startTime->copy()->addMinutes($service->duration_minutes ?? 60);
                $capacity  = (int) $validated['group_capacity'];
                $customerIds = array_unique(array_filter($validated['customer_ids']));

                $groupId   = null;
                $created   = 0;
                foreach ($customerIds as $custId) {
                    $apptId = \Illuminate\Support\Facades\DB::table('appointments')->insertGetId([
                        'tenant_id'      => $tenant->id,
                        'branch_id'      => $validated['branch_id'],
                        'customer_id'    => $custId,
                        'staff_id'       => $validated['staff_id'],
                        'service_id'     => $validated['service_id'],
                        'start_time'     => $startTime,
                        'end_time'       => $endTime,
                        'price'          => $service->price ?? 0,
                        'notes'          => $validated['notes'] ?? null,
                        'status'         => 'confirmed',
                        'source'         => 'panel',
                        'group_id'       => $groupId,
                        'group_capacity' => $capacity,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                    if ($groupId === null) {
                        $groupId = $apptId;
                        \Illuminate\Support\Facades\DB::table('appointments')
                            ->where('id', $apptId)
                            ->update(['group_id' => $groupId]);
                    }
                    $created++;
                }

                return redirect()
                    ->route('panel.appointments.index', ['tenant_slug' => $tenant->slug])
                    ->with('success', "Grup randevusu oluşturuldu. {$created} müşteri eklendi.");
            }

            // --- TEKİL / TEKRARLAYAN ---
            if ($request->boolean('is_recurring') && $request->recurrence_rule && $request->recurrence_count) {
                // Hizmet suresini al
                $service = Service::find($validated['service_id']);
                $startTime = \Carbon\Carbon::parse($validated['start_time']);
                $endTime = $startTime->copy()->addMinutes($service->duration_minutes ?? 60);

                $data = [
                    'tenant_id' => $tenant->id,
                    'branch_id' => $validated['branch_id'],
                    'customer_id' => $validated['customer_id'],
                    'staff_id' => $validated['staff_id'],
                    'service_id' => $validated['service_id'],
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'price' => $service->price ?? 0,
                    'notes' => $validated['notes'] ?? null,
                ];

                $created = $this->recurringService->createRecurring(
                    $data,
                    $validated['recurrence_rule'],
                    (int) $validated['recurrence_count']
                );

                return redirect()
                    ->route('panel.appointments.index', ['tenant_slug' => $tenant->slug])
                    ->with('success', count($created) . ' tekrarlayan randevu olusturuldu.');
            }

            $this->appointmentService->create($validated);

            // FCM bildirimi gonder
            try {
                $customer = \App\Models\Customer::find($validated['customer_id']);
                $service = \App\Models\Service::find($validated['service_id']);
                $startTime = \Carbon\Carbon::parse($validated['start_time']);
                app(FcmService::class)->sendToTenant(
                    $tenant->id,
                    '📅 Yeni Randevu',
                    ($customer?->name ?? 'Musteri') . ' — ' . ($service?->name ?? '') . ' ' . $startTime->format('d.m H:i'),
                    ['type' => 'new_appointment']
                );
            } catch (\Throwable $e) {}

            return redirect()
                ->route('panel.appointments.index', ['tenant_slug' => $tenant->slug])
                ->with('success', 'Randevu basariyla olusturuldu.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();
        $appointment = Appointment::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->with(['customer', 'service', 'staff', 'branch'])
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        // Tekrarlayan seri
        $seriesAppointments = collect();
        if ($appointment->is_recurring && $appointment->parent_appointment_id) {
            $seriesAppointments = $this->recurringService->getSeriesAppointments(
                $appointment->parent_appointment_id,
                $tenant->id
            );
        }

        return view('panel.appointments.show', compact('tenant', 'appointment', 'seriesAppointments'));
    }

    public function edit(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();
        $authUser = auth()->user();

        $appointment = Appointment::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $customers = Customer::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $services = Service::where('tenant_id', $tenant->id)->where('status', 'active')->orderBy('name')->get();
        $branches = Branch::where('tenant_id', $tenant->id)->where('status', 'active')->get();

        if ($authUser->role === 'firma_sahibi') {
            $staff = User::whereIn('role', ['personel', 'firma_sahibi', 'sube_muduru'])
                ->where('tenant_id', $tenant->id)
                ->orderBy('name')->get();
        } else {
            $staff = User::where('id', $authUser->id)->get();
        }

        $userBranchId = $authUser->branch_id;

        return view('panel.appointments.edit', compact(
            'tenant', 'appointment', 'customers', 'services', 'staff', 'branches', 'userBranchId', 'authUser'
        ));
    }

    public function update(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        $authUser = auth()->user();

        $appointment = Appointment::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $validated = $request->validate([
            'branch_id'  => ['required'],
            'customer_id'=> ['required'],
            'staff_id'   => ['required'],
            'service_id' => ['required'],
            'start_time' => ['required', 'date'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ], [
            'start_time.required' => 'Tarih ve saat seçmelisiniz.',
        ]);

        $service = Service::find($validated['service_id']);
        $startTime = \Carbon\Carbon::parse($validated['start_time']);
        $endTime   = $startTime->copy()->addMinutes($service->duration_minutes ?? 60);

        $appointment->update([
            'branch_id'   => $validated['branch_id'],
            'customer_id' => $validated['customer_id'],
            'staff_id'    => $authUser->role === 'firma_sahibi' ? $validated['staff_id'] : $authUser->id,
            'service_id'  => $validated['service_id'],
            'start_time'  => $startTime,
            'end_time'    => $endTime,
            'price'       => $service->price ?? $appointment->price,
            'notes'       => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('panel.appointments.show', ['tenant_slug' => $tenant->slug, 'id' => $id])
            ->with('success', 'Randevu güncellendi.');
    }

    public function updateStatus(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $appointment = Appointment::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled,no_show'],
        ]);

        $oldStatus = $appointment->status;
        $newStatus = $request->status;

        $appointment->update(['status' => $newStatus]);

        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            event(new AppointmentCompleted(
                appointmentId: $appointment->id,
                customerId: $appointment->customer_id,
                tenantId: $appointment->tenant_id,
                price: (float) $appointment->price
            ));
        }

        return back()->with('success', 'Randevu durumu guncellendi.');
    }

    public function cancelSeries(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $appointment = Appointment::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $parentId = $appointment->parent_appointment_id ?? $appointment->id;

        if ($request->cancel_type === 'from_date') {
            $count = $this->recurringService->cancelFromDate($parentId, $tenant->id, $appointment->start_time);
        } else {
            $count = $this->recurringService->cancelSeries($parentId, $tenant->id);
        }

        return redirect()
            ->route('panel.appointments.index', ['tenant_slug' => $tenant->slug])
            ->with('success', "{$count} randevu iptal edildi.");
    }

    public function destroy(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        $appointment = Appointment::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $appointment->delete();

        return redirect()
            ->route('panel.appointments.index', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Randevu silindi.');
    }
}
