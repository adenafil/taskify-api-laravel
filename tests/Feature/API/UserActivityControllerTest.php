<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetUserActivitySuccessfully()
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

        // Get user activity
        $response = $this->getJson('/api/user/activity', [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('register');
        $response->assertSeeText('login');
    }

    public function testGetUserActivityUnsuccessfullyBecauseUserIsNotAuthenticated()
    {
        // Attempt to get user activity without authentication
        $response = $this->getJson('/api/user/activity');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
