<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    // Müşteriye gelen link: /SLUG/yorum/TOKEN
    public function show(TenantContext $ctx, string $tenant_slug, string $token)
    {
        $tenant = $ctx->get();

        $review = DB::table('reviews')
            ->where('token', $token)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$review) abort(404);

        // Zaten doldurulmuşsa teşekkür sayfası
        if ($review->rating) {
            return view('booking.review-done', compact('tenant'));
        }

        $appointment = DB::table('appointments')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('users', 'appointments.staff_id', '=', 'users.id')
            ->where('appointments.id', $review->appointment_id)
            ->select('appointments.*', 'services.name as service_name', 'users.name as staff_name')
            ->first();

        return view('booking.review', compact('tenant', 'review', 'appointment', 'token'));
    }

    public function store(TenantContext $ctx, string $tenant_slug, string $token, Request $request)
    {
        $tenant = $ctx->get();

        $review = DB::table('reviews')
            ->where('token', $token)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$review || $review->rating) abort(404);

        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        DB::table('reviews')->where('token', $token)->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_published' => true,
            'updated_at' => now(),
        ]);

        return view('booking.review-done', compact('tenant'));
    }
}
