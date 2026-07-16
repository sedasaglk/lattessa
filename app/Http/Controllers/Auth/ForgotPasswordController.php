<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ForgotPasswordController extends Controller
{
    public function showForgotForm(string $tenant_slug): View
    {
        return view('auth.forgot-password', compact('tenant_slug'));
    }

    public function sendResetLink(Request $request, string $tenant_slug): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $tenant = DB::table('tenants')->where('slug', $tenant_slug)->first();
        if (!$tenant) abort(404);

        $user = DB::table('users')
            ->where('tenant_id', $tenant->id)
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return back()->with('success', 'Eğer bu e-posta kayıtlıysa sıfırlama linki gönderildi.');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->upsert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ], ['email'], ['token', 'created_at']);

        $resetUrl = route('password.reset.form', [
            'tenant_slug' => $tenant_slug,
            'token' => $token,
            'email' => $request->email,
        ]);

        Mail::to($request->email)->send(new ResetPasswordMail(
            name: $user->name,
            resetUrl: $resetUrl,
        ));

        return back()->with('success', 'Şifre sıfırlama linki e-posta adresinize gönderildi.');
    }

    public function showResetForm(Request $request, string $tenant_slug, string $token): View
    {
        return view('auth.reset-password', [
            'tenant_slug' => $tenant_slug,
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request, string $tenant_slug): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required'],
            'password' => ['required', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifreler eşleşmiyor.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Geçersiz veya süresi dolmuş link.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            return back()->withErrors(['token' => 'Bu link süresi dolmuş. Yeni link isteyin.']);
        }

        $tenant = DB::table('tenants')->where('slug', $tenant_slug)->first();

        DB::table('users')
            ->where('email', $request->email)
            ->where('tenant_id', $tenant->id)
            ->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login.form', ['tenant_slug' => $tenant_slug])
            ->with('success', 'Şifreniz başarıyla güncellendi. Giriş yapabilirsiniz.');
    }
}
