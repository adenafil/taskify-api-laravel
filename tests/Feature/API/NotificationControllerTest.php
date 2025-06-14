<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSaveNewSubscriptionSuccessfully()
    {
        // Create a user
        $this->postJson('/api/register', [
            'name' => 'Wildan',
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        // login as wildan the majesty
        $response = $this->postJson('/api/login', [
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
        ]);
        $response->assertStatus(200);

        $subscriptionData = [
            'user_id' => 1, // Assuming the user ID is 1 for Wildan the Majesty
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/1234567890',
            'keys' => [
                'p256dh' => 'BJe7c5l5sFZfD8cqHghZ5Kjh99bNdqFNCb078QLc7juQN_0L0YjaP90KIS77FRW3RHWWu-Ms5ySZDDlX-38vZqc',
                'auth' => '-gaoWkacCeDC1carT5aZhA',
            ],
        ];

        $response = $this->postJson('/api/notifications/subscribe', [
            'subscription' => json_encode($subscriptionData),
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Push notification subscription saved',
        ]);
    }

    public function testSaveNewSubscriptionUnsuccessfullyBecauseTheSubscriptionDataIsEmpty()
    {
        // Create a user
        $this->postJson('/api/register', [
            'name' => 'Wildan',
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        // login as wildan the majesty
        $response = $this->postJson('/api/login', [
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
        ]);
        $response->assertStatus(200);


        $response = $this->postJson('/api/notifications/subscribe', [
            'subscription' => '',
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The subscription field is required.',
        ]);
    }

    public function testSaveNewSubscriptionUnsuccessfullyBecauseItIsUnathenticated()
    {
        $response = $this->postJson('/api/notifications/subscribe', [
            'subscription' => json_encode([
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/1234567890',
                'keys' => [
                    'p256dh' => 'BJe7c5l5sFZfD8cqHghZ5Kjh99bNdqFNCb078QLc7juQN_0L0YjaP90KIS77FRW3RHWWu-Ms5ySZDDlX-38vZqc',
                    'auth' => '-gaoWkacCeDC1carT5aZhA',
                ],
            ]),
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUnsubscribeSuccessfully()
    {
        // Create a user
        $this->postJson('/api/register', [
            'name' => 'Wildan',
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        // login as wildan the majesty
        $response = $this->postJson('/api/login', [
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
        ]);
        $response->assertStatus(200);

        $this->testSaveNewSubscriptionSuccessfully(); // wildan the majesty has subscribed to push notifications

        $response = $this->postJson('/api/notifications/unsubscribe', [], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);


        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Push notification subscription removed',
        ]);
        // Assert that the subscription has been deleted
        $this->assertDatabaseMissing('push_subscriptions', [
            'user_id' => 1,
        ]);
    }

    public function testUnsubscribeUnsuccessfullyBecauseItIsUnauthenticated()
    {
        $response = $this->postJson('/api/notifications/unsubscribe');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
