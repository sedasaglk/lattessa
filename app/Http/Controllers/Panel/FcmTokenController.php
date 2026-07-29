<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function store(Request $request, FcmService $fcm, string $tenant_slug): JsonResponse
    {
        $request->validate(['token' => 'required|string']);
        $fcm->saveToken(auth()->id(), $request->token);
        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, FcmService $fcm, string $tenant_slug): JsonResponse
    {
        $request->validate(['token' => 'required|string']);
        $fcm->deleteToken($request->token);
        return response()->json(['ok' => true]);
    }
}
