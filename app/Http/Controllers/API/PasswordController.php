<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(8)]
        ]);

        $user = $request->user();

        // Cek apakah ini pengguna OAuth yang belum pernah mengatur password sendiri
        $isNewOauthUser = !empty($user->social_id) && !empty($user->social_type) &&
            $user->password_updated_at === null;

        // Check if current password is correct
        if (!$isNewOauthUser && !Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect',
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->password_updated_at = now();
        $user->save();

        // Log the password change activity
        UserActivity::create([
            'user_id' => $user->id,
            'action' => 'password_change',
            'ip_address' => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $request->ip(),
            'device' => $request->header('User-Agent')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully',
        ]);
    }
}
