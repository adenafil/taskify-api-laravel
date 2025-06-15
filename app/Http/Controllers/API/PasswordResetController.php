<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Send a reset link to the given user.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'We could not find a user with that email address',
            ], 404);
        }

        // Send the password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset link sent to your email',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Log the password reset activity
                UserActivity::create([
                    'user_id' => $user->id,
                    'action' => 'password_reset',
                    'ip_address' => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $request->ip(),
                    'device' => $request->header('User-Agent')
                ]);

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password has been reset successfully',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => trans($status),
        ], 400);
    }

    public function isTokenValid(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        $status = Password::tokenExists($user, $request->token);

        if ($status) {
            return response()->json([
                'status' => 'success',
                'message' => 'Token is valid',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Token is invalid or expired',
        ], 400);
    }
}
