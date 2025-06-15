<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthSocialiteController extends Controller
{
    public function redirect($service)
    {
        $supportedServices = ['google', 'github'];
        if (!in_array($service, $supportedServices)) {
            abort(400, 'Service not supported');
        }

        return Socialite::driver($service)->stateless()->redirect();
    }

    public function callback(Request $request, $service)
    {
        try {
            $socialUser = Socialite::driver($service)->stateless()->user();

            $existingUser = User::where('social_id', $socialUser->id)
                ->where('social_type', $service)
                ->orWhere('email', $socialUser->email)
                ->first();

            if (!$existingUser) {
                $existingUser = User::create([
                    'name' => $socialUser->name,
                    'email' => $socialUser->email,
                    'social_id' => $socialUser->id,
                    'social_type' => $service,
                    'password' => bcrypt(Str::random(24)),
                ]);

                UserActivity::create([
                    'user_id' => $existingUser->id,
                    'action' => 'register',
                    'ip_address' => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $request->ip(),
                    'device' => $request->header('User-Agent')
                ]);
            } else {
                UserActivity::create([
                    'user_id' => $existingUser->id,
                    'action' => 'login',
                    'ip_address' => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $request->ip(),
                    'device' => $request->header('User-Agent')
                ]);
            }

            $token = $existingUser->createToken('auth_token')->plainTextToken;

            return redirect()->away(env('FE_APP_URL') . "/auth/callback?token=$token");
        } catch (\Exception $e) {
            Log::error($e); // Jangan dd(), gunakan log

            return redirect()->away(env('FE_APP_URL') . '/auth/error?message=' . urlencode('Authentication failed. Please try again ☺️.'));
        }
    }
}
