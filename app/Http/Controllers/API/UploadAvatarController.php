<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadAvatarController extends Controller
{
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar'))
        {
            $imageData = file_get_contents($request->file('avatar'));
            $base64 = base64_encode($imageData);
            $user->avatar = $base64;
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Avatar uploaded successfully',
                'avatar' => $base64
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Avatar upload failed',
        ], 400);
    }
}
