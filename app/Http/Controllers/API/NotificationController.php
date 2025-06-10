<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'subscription' => 'required|string',
        ]);

        $subscription = json_decode($request->subscription, true);

        PushSubscription::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'endpoint' => $subscription['endpoint'],
            ],
            [
                'p256dh_key' => $subscription['keys']['p256dh'],
                'auth_token' => $subscription['keys']['auth'],
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Push notification subscription saved',
        ]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        PushSubscription::where('user_id', auth()->id())->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Push notification subscription removed',
        ]);
    }
}
