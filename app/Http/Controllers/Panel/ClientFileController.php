<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ClientFileController extends Controller
{
    public function show(TenantContext $ctx, string $tenant_slug, string $customerId): View
    {
        $tenant = $ctx->get();

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$customer) abort(404);

        // Sadece klinik/saglik sektoru icin
        $tenant = $ctx->get();
        $allowedTypes = ['klinik', 'diyetisyen', 'psikolog', 'estetik'];
        if (!in_array($tenant->business_type, $allowedTypes)) {
            abort(403, 'Bu ozellik isletme turunuz icin aktif degil.');
        }

        $clientFile = DB::table('client_files')
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customerId)
            ->first();

        $sessionNotes = DB::table('session_notes')
            ->join('users', 'session_notes.staff_id', '=', 'users.id')
            ->leftJoin('appointments', 'session_notes.appointment_id', '=', 'appointments.id')
            ->where('session_notes.tenant_id', $tenant->id)
            ->where('session_notes.customer_id', $customerId)
            ->select(
                'session_notes.*',
                'users.name as staff_name',
                'appointments.start_time as appointment_time'
            )
            ->orderByDesc('session_notes.session_date')
            ->get();

        $sessionCount = $sessionNotes->count();

        // Son randevular
        $appointments = DB::table('appointments')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('users', 'appointments.staff_id', '=', 'users.id')
            ->where('appointments.tenant_id', $tenant->id)
            ->where('appointments.customer_id', $customerId)
            ->whereNull('appointments.deleted_at')
            ->select('appointments.*', 'services.name as service_name', 'users.name as staff_name')
            ->orderByDesc('appointments.start_time')
            ->limit(10)
            ->get();

        $staff = DB::table('users')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->whereIn('role', ['firma_sahibi', 'sube_muduru', 'personel'])
            ->get();

        return view('panel.client-files.show', compact(
            'tenant', 'customer', 'clientFile', 'sessionNotes',
            'sessionCount', 'appointments', 'staff'
        ));
    }

    public function updateFile(Request $request, TenantContext $ctx, string $tenant_slug, string $customerId): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'complaint' => ['nullable', 'string', 'max:500'],
            'anamnesis' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'treatment_plan' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'medical_history' => ['nullable', 'string'],
            'family_history' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        // Alerjiler ve ilaclar JSON olarak sakla
        $allergies = $request->allergies
            ? array_map('trim', explode(',', $request->allergies))
            : null;

        $medications = $request->medications
            ? array_map('trim', explode(',', $request->medications))
            : null;

        $data = [
            'tenant_id' => $tenant->id,
            'customer_id' => $customerId,
            'complaint' => $validated['complaint'] ?? null,
            'anamnesis' => $validated['anamnesis'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'treatment_plan' => $validated['treatment_plan'] ?? null,
            'allergies' => $allergies ? json_encode($allergies) : null,
            'medications' => $medications ? json_encode($medications) : null,
            'height' => $validated['height'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'blood_type' => $validated['blood_type'] ?? null,
            'medical_history' => $validated['medical_history'] ?? null,
            'family_history' => $validated['family_history'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'updated_at' => now(),
        ];

        $existing = DB::table('client_files')
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customerId)
            ->first();

        if ($existing) {
            DB::table('client_files')
                ->where('id', $existing->id)
                ->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('client_files')->insert($data);
        }

        return back()->with('success', 'Danisan dosyasi guncellendi.');
    }

    public function storeNote(Request $request, TenantContext $ctx, string $tenant_slug, string $customerId): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'session_date' => ['required', 'date'],
            'staff_id' => ['required', 'integer'],
            'appointment_id' => ['nullable', 'integer'],
            'subjective' => ['nullable', 'string'],
            'objective' => ['nullable', 'string'],
            'assessment' => ['nullable', 'string'],
            'plan' => ['nullable', 'string'],
            'weight' => ['nullable', 'numeric'],
            'mood_score' => ['nullable', 'integer', 'min:1', 'max:10'],
            'homework' => ['nullable', 'string'],
            'next_session_plan' => ['nullable', 'string'],
            'is_private' => ['nullable', 'boolean'],
        ]);

        // Seans numarasi
        $sessionCount = DB::table('session_notes')
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customerId)
            ->count();

        DB::table('session_notes')->insert([
            'tenant_id' => $tenant->id,
            'customer_id' => $customerId,
            'appointment_id' => $validated['appointment_id'] ?? null,
            'staff_id' => $validated['staff_id'],
            'session_number' => $sessionCount + 1,
            'session_date' => $validated['session_date'],
            'subjective' => $validated['subjective'] ?? null,
            'objective' => $validated['objective'] ?? null,
            'assessment' => $validated['assessment'] ?? null,
            'plan' => $validated['plan'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'mood_score' => $validated['mood_score'] ?? null,
            'homework' => $validated['homework'] ?? null,
            'next_session_plan' => $validated['next_session_plan'] ?? null,
            'is_private' => $request->boolean('is_private'),
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Seans notu eklendi.');
    }

    public function destroyNote(TenantContext $ctx, string $tenant_slug, string $customerId, string $noteId): RedirectResponse
    {
        $tenant = $ctx->get();

        DB::table('session_notes')
            ->where('id', $noteId)
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customerId)
            ->delete();

        return back()->with('success', 'Seans notu silindi.');
    }
}
