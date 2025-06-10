<?php

use App\Http\Controllers\API\AuthSocialiteController;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    return "API is active on /api endpoint";
});

Route::get('/login/{service}', [AuthSocialiteController::class, 'redirect']);
Route::post('/login/{service}/callback', [AuthSocialiteController::class, 'callback']);


Route::get('/callback/{service}', [AuthSocialiteController::class, 'callback']);


Route::get('/test', function () {
    $now = Carbon::now();
    $fiveHoursLater = $now->copy()->addHours(5);

    echo "Waktu sekarang: " . $now->toDateTimeString() . "\n";
    echo "5 jam ke depan: " . $fiveHoursLater->toDateTimeString() . "\n";

    // Check if current time is between 19:00 - 23:59
    $eveningStart = $now->copy()->setTime(19, 0, 0);
    $eveningEnd = $now->copy()->setTime(23, 59, 59);
    $isEveningTime = $now->between($eveningStart, $eveningEnd);

    echo "Jam sekarang: " . $now->format('H:i') . "\n";
    echo "Rentang malam: 19:00 - 23:59\n";
    echo "Apakah di fase malam: " . ($isEveningTime ? 'Ya' : 'Tidak') . "\n";

    $dueTasks = Task::with('user')
        ->where('status', '!=', 'completed')
        ->where('status', '!=', 'expired')
        ->where(function ($query) use ($now, $fiveHoursLater) {
            if ($fiveHoursLater->isSameDay($now)) {
                $query->whereDate('due_date', $now->toDateString());
            } else {
                $query->whereDate('due_date', $now->toDateString())
                    ->orWhereDate('due_date', $fiveHoursLater->toDateString());
            }
        })
        ->get();

    echo "Due tasks dalam 5 jam ke depan: " . $dueTasks->count() . "\n";

    if ($isEveningTime) {
        echo "✅ Status: Waktu malam - notifikasi akan dikirim\n";
    } else {
        echo "❌ Status: Bukan waktu malam - notifikasi tidak dikirim\n";
    }
});
Route::get('/phpinfo', function () {
    // date_default_timezone_set('Asia/Jakarta');
    echo "Tanggal dan waktu sekarang: " . date('Y-m-d H:i:s');
    phpinfo();
});
