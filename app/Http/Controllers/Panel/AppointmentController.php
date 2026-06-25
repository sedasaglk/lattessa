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
        $view = request('view', 'calendar');
        $date = request('date', today()->format('Y-m-d'));

        if ($view === 'list') {
            $appointments = Appointment::with(['customer', 'service', 'staff', 'branch'])
                ->whereDate('start_time', $date)
                ->orderBy('start_time')
                ->get();
        } else {
            $appointments = collect();
        }

        $branches = Branch::where('status', 'active')->get();

        return view('panel.appointments.index', compact(
            'tenant', 'appointments', 'date', 'branches', 'view'
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
        $customers = Customer::orderBy('name')->get();
        $services = Service::where('status', 'active')->orderBy('name')->get();
        $staff = User::whereIn('role', ['personel', 'firma_sahibi', 'sube_muduru'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        $branches = Branch::where('status', 'active')->get();
        $defaultDate = request('date', now()->format('Y-m-d\TH:i'));

        return view('panel.appointments.create', compact(
            'tenant', 'customers', 'services', 'staff', 'branches', 'defaultDate'
        ));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'branch_id' => ['required'],
            'customer_id' => ['required'],
            'staff_id' => ['required'],
            'service_id' => ['required'],
            'start_time' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_rule' => ['nullable', 'in:daily,weekly,biweekly,monthly'],
            'recurrence_count' => ['nullable', 'integer', 'min:2', 'max:52'],
        ], [
            'branch_id.required' => 'Sube secmelisiniz.',
            'customer_id.required' => 'Musteri secmelisiniz.',
            'staff_id.required' => 'Personel secmelisiniz.',
            'service_id.required' => 'Hizmet secmelisiniz.',
            'start_time.required' => 'Tarih ve saat secmelisiniz.',
        ]);

        try {
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
