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

        $branches = DB::table('branches')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $photos = DB::table('salon_photos')
            ->where('tenant_id', $tenant->id)
            ->orderBy('order')
            ->get();

        $reviews = DB::table('reviews')
            ->join('customers', 'reviews.customer_id', '=', 'customers.id')
            ->where('reviews.tenant_id', $tenant->id)
            ->where('reviews.is_published', true)
            ->where('reviews.rating', '>', 0)
            ->orderByDesc('reviews.created_at')
            ->limit(10)
            ->select('reviews.*', 'customers.name as customer_name')
            ->get();

        $avgRating = $reviews->count() ? round($reviews->avg('rating'), 1) : null;

        return view('booking.show', compact('tenant', 'services', 'branches', 'photos', 'reviews', 'avgRating'));
    }

    public function getStaff(Request $request, TenantContext $ctx, string $tenant_slug): JsonResponse
    {
        $tenant = $ctx->get();

        $branchId = $request->input('branch_id');

        // Tüm uygun personeli al
        $allStaff = DB::table('users')
            ->where('tenant_id', $tenant->id)
            ->whereIn('role', ['firma_sahibi', 'sube_muduru', 'personel'])
            ->whereNull('deleted_at')
            ->where(function($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId)->orWhere('role', 'firma_sahibi');
                }
            })
            ->select('id', 'name', 'role')
            ->get();

        // Eğer tarih seçildiyse staff_schedules ile filtrele
        $date = $request->input('date');
        if ($date && $branchId) {
            $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;
            $staffIds = $allStaff->pluck('id');

            // O gün için schedule kayıtları
            $schedules = DB::table('staff_schedules')
                ->whereIn('user_id', $staffIds)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_working', true)
                ->get()
                ->keyBy('user_id');

            $staff = $allStaff->filter(function($member) use ($schedules, $branchId, $dayOfWeek) {
                $schedule = $schedules[$member->id] ?? null;
                if (!$schedule) return false; // O gün çalışmıyor
                // Schedule'da branch_id varsa kontrol et
                if ($schedule->branch_id && $schedule->branch_id != $branchId) return false;
                return true;
            })->values();
        } else {
            $staff = $allStaff;
        }

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
            'branch_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
            'staff_id' => ['required', 'integer'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'string'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'branch_id.required' => 'Sube secmelisiniz.',
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
            ->where('id', $validated['branch_id'])
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$branch) {
            return back()->with('error', 'Gecersiz sube secimi.')->withInput();
        }

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

        // Email onay gonder
        try {
            if (!empty($validated['customer_email'])) {
                $startTime2 = \Carbon\Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time'] ?? $startTime);
                \Illuminate\Support\Facades\Mail::to($validated['customer_email'])->send(
                    new \App\Mail\AppointmentConfirmationMail(
                        customerName: $validated['customer_name'],
                        companyName: $tenant->company_name,
                        serviceName: $service->name,
                        staffName: $staff ? $staff->name : 'Belirlenecek',
                        date: $startTime->format('d.m.Y'),
                        time: $startTime->format('H:i'),
                        price: number_format($service->price, 0, ',', '.'),
                    )
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Randevu email gonderilemedi: ' . $e->getMessage());
        }

        // WhatsApp / SMS onay mesaji
        try {
            $message = "Sayin {$validated['customer_name']}, online randevunuz alinmistir.\n\n"
                . "Tarih: " . $startTime->format('d.m.Y') . "\n"
                . "Saat: " . $startTime->format('H:i') . "\n"
                . "Hizmet: {$service->name}\n\n"
                . "Randevunuz onaylandiginda bilgilendirileceksiniz.\n"
                . "- " . $tenant->company_name;

            $notificationService = app(\App\Services\Notification\NotificationService::class);
            $notificationService->notify(
                $tenant->id,
                $validated['customer_phone'],
                $message,
                'appointment_confirmation',
                $customerId,
                'auto'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Online randevu onay mesaji gonderilemedi: ' . $e->getMessage());
        }

        // FCM bildirimi gonder
        try {
            $staffName = DB::table('users')->where('id', $validated['staff_id'])->value('name') ?? 'Personel';
            app(\App\Services\FcmService::class)->sendToTenant(
                $tenant->id,
                '🌐 Online Randevu',
                $validated['customer_name'] . ' — ' . $service->name . ' ' . $startTime->format('d.m H:i'),
                ['type' => 'online_appointment']
            );
        } catch (\Throwable $e) {}

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
