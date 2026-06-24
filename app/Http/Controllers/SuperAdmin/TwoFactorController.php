<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function setup(): View
    {
        $user = Auth::guard('super_admin')->user();

        if (!$user->two_factor_secret) {
            $secret = $this->google2fa->generateSecretKey();
            DB::table('users')->where('id', $user->id)->update([
                'two_factor_secret' => $secret,
                'updated_at' => now(),
            ]);
            $user->two_factor_secret = $secret;
        }

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            'Lattessa',
            $user->email,
            $user->two_factor_secret
        );

        // QR kod SVG olustur
        $qrCode = null;
        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $qrCode = base64_encode($writer->writeString($qrCodeUrl));
        } catch (\Exception $e) {
            $qrCode = null;
        }

        return view('super-admin.two-factor.setup', compact('user', 'qrCode', 'qrCodeUrl'));
    }

    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Dogrulama kodu zorunludur.',
            'code.size' => 'Kod 6 haneli olmalidir.',
        ]);

        $user = Auth::guard('super_admin')->user();

        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'Gecersiz dogrulama kodu. Tekrar deneyin.']);
        }

        DB::table('users')->where('id', $user->id)->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('super-admin.2fa.setup')
            ->with('success', '2FA basariyla aktiflestirildi.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::guard('super_admin')->user();

        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'Gecersiz dogrulama kodu.']);
        }

        DB::table('users')->where('id', $user->id)->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'updated_at' => now(),
        ]);

        return redirect()->route('super-admin.2fa.setup')
            ->with('success', '2FA devre disi birakildi.');
    }

    public function challenge(): View
    {
        return view('super-admin.two-factor.challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('super-admin.login');
        }

        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user || !$user->two_factor_secret) {
            return redirect()->route('super-admin.login');
        }

        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'Gecersiz dogrulama kodu.']);
        }

        // 2FA gecti, gercekten giris yap
        Auth::guard('super_admin')->loginUsingId($userId);
        session()->forget('2fa_user_id');
        $request->session()->regenerate();

        return redirect()->route('super-admin.dashboard');
    }
}
