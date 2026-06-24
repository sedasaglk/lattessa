<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class OnlineBookingController extends Controller
{
    public function show(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $services = DB::table('services')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('is_online_bookable', 1)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('booking.show', compact('tenant', 'services'));
    }

    public function getStaff(Request $request, TenantContext $ctx, string $tenant_slug): JsonResponse
    {
        $tenant = $ctx->get();

        $staff = DB::table('users')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->whereIn('role', ['firma_sahibi', 'sube_muduru', 'personel'])
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->get();

        return response()->json($staff);
    }

    public function getAvailableSlots(Request $request, TenantContext $ctx, string $tenant_slug): JsonResponse
    {
        $tenant = $ctx->get();

        $service = DB::table('services')
            ->where('id', $request->service_id)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$service) {
            return response()->json(['slots' => []]);
        }

        $date = Carbon::parse($request->date);

        $schedule = DB::table('staff_schedules')
            ->where('user_id', $request->staff_id)
            ->where('tenant_id', $tenant->id)
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_day_off', 0)
            ->first();

        if (!$schedule) {
            return response()->json(['slots' => [], 'message' => 'Bu gun personel musait degil.']);
        }

        $workStart = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->start_time);
        $workEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->end_time);
        $duration = $service->duration_minutes;

        $existingAppointments = DB::table('appointments')
            ->where('staff_id', $request->staff_id)
            ->whereDate('start_time', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNull('deleted_at')
            ->get(['start_time', 'end_time']);

        $slots = [];
        $cursor = $workStart->copy();

        while ($cursor->copy()->addMinutes($duration)->lte($workEnd)) {
            $slotEnd = $cursor->copy()->addMinutes($duration);

            $conflict = false;
            foreach ($existingAppointments as $appt) {
                $apptStart = Carbon::parse($appt->start_time);
                $apptEnd = Carbon::parse($appt->end_time);
                if ($cursor->lt($apptEnd) && $slotEnd->gt($apptStart)) {
                    $conflict = true;
                    break;
                }
            }

            if (!$conflict && $cursor->gt(now())) {
                $slots[] = $cursor->format('H:i');
            }

            $cursor->addMinutes(15);
        }

        return response()->json(['slots' => $slots]);
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        // Rate limiting: IP basina dakikada 10 online randevu istegi
        $rateLimitKey = 'online-booking|' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors(['general' => "Cok fazla istek. {$seconds} saniye sonra tekrar deneyin."]);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, 60);

        $validated = $request->validate([
            'service_id' => ['required', 'integer'],
            'staff_id' => ['required', 'integer'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'string'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'service_id.required' => 'Hizmet secmelisiniz.',
            'staff_id.required' => 'Personel secmelisiniz.',
            'date.required' => 'Tarih secmelisiniz.',
            'time.required' => 'Saat secmelisiniz.',
            'customer_name.required' => 'Adinizi girin.',
            'customer_phone.required' => 'Telefon numaranizi girin.',
        ]);

        $service = DB::table('services')
            ->where('id', $validated['service_id'])
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$service) {
            return back()->with('error', 'Gecersiz hizmet.');
        }

        $startTime = Carbon::parse($validated['date'] . ' ' . $validated['time']);
        $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

        $conflict = DB::table('appointments')
            ->where('staff_id', $validated['staff_id'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNull('deleted_at')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($conflict) {
            return back()->with('error', 'Secilen saat dolu, lutfen baska bir saat secin.')->withInput();
        }

        $customer = DB::table('customers')
            ->where('tenant_id', $tenant->id)
            ->where('phone', $validated['customer_phone'])
            ->whereNull('deleted_at')
            ->first();

        if (!$customer) {
            $customerId = DB::table('customers')->insertGetId([
                'tenant_id' => $tenant->id,
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
                'source' => 'online',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $customerId = $customer->id;
        }

        $branch = DB::table('branches')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        DB::table('appointments')->insert([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'customer_id' => $customerId,
            'staff_id' => $validated['staff_id'],
            'service_id' => $validated['service_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
            'source' => 'online',
            'notes' => $validated['customer_notes'] ?? null,
            'price' => $service->price,
            'is_recurring' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('booking.success', ['tenant_slug' => $tenant->slug])
            ->with('booking_success', [
                'customer_name' => $validated['customer_name'],
                'service_name' => $service->name,
                'date' => $startTime->format('d.m.Y'),
                'time' => $startTime->format('H:i'),
            ]);
    }

    public function success(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $booking = session('booking_success');

        if (!$booking) {
            return redirect()->route('booking.show', ['tenant_slug' => $tenant->slug]);
        }

        return view('booking.success', compact('tenant', 'booking'));
    }
}
