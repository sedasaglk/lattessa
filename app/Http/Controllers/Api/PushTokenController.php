<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PushTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'platform' => ['nullable', 'in:ios,android'],
        ]);

        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        \Illuminate\Support\Facades\DB::table('push_tokens')->upsert([
            'user_id' => auth()->id(),
            'token' => $request->token,
            'platform' => $request->platform,
            'is_active' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ], ['user_id', 'token'], ['is_active', 'platform', 'updated_at']);

        return response()->json(['success' => true]);
    }
}
