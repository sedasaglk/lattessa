<?php

use App\Http\Controllers\Api\PushTokenController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/push-token', [PushTokenController::class, 'store']);
    Route::delete('/push-token', function(\Illuminate\Http\Request $request) {
        $request->validate(['token' => 'required|string']);
        \Illuminate\Support\Facades\DB::table('push_tokens')
            ->where('user_id', auth()->id())
            ->where('token', $request->token)
            ->update(['is_active' => false]);
        return response()->json(['success' => true]);
    });
});
