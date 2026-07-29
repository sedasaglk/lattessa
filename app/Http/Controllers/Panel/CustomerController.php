<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $search = request('search');

        $customers = Customer::when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(20);

        return view('panel.customers.index', compact('tenant', 'customers', 'search'));
    }

    public function create(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        return view('panel.customers.create', compact('tenant'));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Musteri adi zorunludur.',
            'phone.required' => 'Telefon numarasi zorunludur.',
        ]);

        $validated['tenant_id'] = $tenant->id;

        Customer::create($validated);

        return redirect()
            ->route('panel.customers.index', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Musteri basariyla eklendi.');
    }

    public function show(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();
        $customer = Customer::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $appointments = \App\Models\Appointment::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->with(['service', 'staff'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('start_time')
            ->get();

        return view('panel.customers.show', compact('tenant', 'customer', 'appointments'));
    }

    public function edit(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();
        $customer = Customer::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        return view('panel.customers.edit', compact('tenant', 'customer'));
    }

    public function update(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        $customer = Customer::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer->update($validated);

        return redirect()
            ->route('panel.customers.show', ['tenant_slug' => $tenant->slug, 'id' => $customer->id])
            ->with('success', 'Musteri bilgileri guncellendi.');
    }


    public function downloadTemplate(string $tenant_slug): \Illuminate\Http\Response
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="musteri_sablonu.csv"',
        ];

        $rows = [
            ['Ad Soyad', 'Telefon', 'E-posta', 'Dogum Tarihi (YYYY-MM-DD)', 'Cinsiyet (male/female/other)', 'Notlar'],
            ['Ahmet Yilmaz', '05001234567', 'ahmet@mail.com', '1990-05-15', 'male', 'VIP musteri'],
            ['Ayse Kaya', '05321234567', '', '', 'female', ''],
        ];

        $output = fopen('php://temp', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, $headers);
    }

    public function import(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'file.required' => 'Dosya secmelisiniz.',
            'file.mimes' => 'Sadece CSV dosyasi yukleyebilirsiniz.',
            'file.max' => 'Dosya en fazla 2MB olabilir.',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        // UTF-8 BOM temizle
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
            rewind($handle);
        }

        $header = fgetcsv($handle); // ilk satır başlık
        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0]) || empty($row[1])) {
                $skipped++;
                continue;
            }

            $name  = trim($row[0]);
            $phone = trim($row[1]);
            $email = !empty($row[2]) ? trim($row[2]) : null;
            $birth = !empty($row[3]) ? trim($row[3]) : null;
            $gender = in_array(trim($row[4] ?? ''), ['male','female','other']) ? trim($row[4]) : null;
            $notes = !empty($row[5]) ? trim($row[5]) : null;

            // Aynı tenant'ta aynı telefon varsa atla
            $exists = \Illuminate\Support\Facades\DB::table('customers')
                ->where('tenant_id', $tenant->id)
                ->where('phone', $phone)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) { $skipped++; continue; }

            \Illuminate\Support\Facades\DB::table('customers')->insert([
                'tenant_id'  => $tenant->id,
                'name'       => $name,
                'phone'      => $phone,
                'email'      => $email,
                'birth_date' => $birth,
                'gender'     => $gender,
                'notes'      => $notes,
                'source'     => 'import',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $imported++;
        }

        fclose($handle);

        return redirect()
            ->route('panel.customers.index', ['tenant_slug' => $tenant->slug])
            ->with('success', "{$imported} musteri eklendi, {$skipped} atlandı.");
    }
    public function destroy(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        $customer = Customer::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $customer->delete();

        return redirect()
            ->route('panel.customers.index', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Musteri silindi.');
    }
}
