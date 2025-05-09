<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserActivityCollection;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    /**
     * Get authenticated user's activity history
     *
     * @param Request $request
     * @return UserActivityCollection
     */
    public function getUserActivity(Request $request): UserActivityCollection
    {
        $user = auth()->user();

        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        $activities = UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return new UserActivityCollection($activities);
    }

}
