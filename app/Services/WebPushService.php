<?php

namespace App\Services;

use App\Models\User;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use App\Models\PushSubscription;
use Carbon\Carbon;

class WebPushService
{
    private WebPush $webPush;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ]);
    }

    public function sendNotificationToUser(User $user, array $payload): bool
    {
        $subscriptions = $user->pushSubscriptions;

        if ($subscriptions->isEmpty()) {
            return false;
        }

        $success = false;

        foreach ($subscriptions as $subscription) {
            $pushSubscription = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'keys' => [
                    'p256dh' => $subscription->p256dh_key,
                    'auth' => $subscription->auth_token,
                ],
            ]);

            $result = $this->webPush->sendOneNotification(
                $pushSubscription,
                json_encode($payload)
            );

            if ($result->isSuccess()) {
                $success = true;
            } else {
                if ($result->getResponse() && $result->getResponse()->getStatusCode() === 410) {
                    $subscription->delete();
                }
            }
        }

        return $success;
    }

    public function sendTaskDueReminder(User $user, $task): bool
    {

        // Waktu sekarang
        $now = Carbon::now();
        $endOfDay = $now->copy()->endOfDay();
        $remainingTime = $now->diff($endOfDay);


        $payload = [
            'title' => 'Task Due Soon!',
            'body' => "Your task '{$task->title}' is going to expire in {$remainingTime->format('%H:%I:%S')} hours.",
            'taskId' => $task->id,
            'url' => '/dashboard?search=' . $task->title . '&page=1',
        ];

        return $this->sendNotificationToUser($user, $payload);
    }
}
