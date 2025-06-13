<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class AuthSocialiteTest extends TestCase
{
    use RefreshDatabase;

    public function testGoogleRedirect()
    {
        $response = $this->get('/login/google');
        $response->assertStatus(302);
        $response->assertRedirect();
    }

    public function testRedirectIfProviderNotSupported()
    {
        $response = $this->get('/login/x');
        $response->assertStatus(400);
        $response->assertSee('Service not supported');
    }

    public function testGoogleCallbackSuccess()
    {
        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();

        Socialite::shouldReceive('stateless')
            ->andReturnSelf();

        Socialite::shouldReceive('user')
            ->andReturn((object) [
                'id' => '1234567890',
                'name' => 'Test User',
                'email' => 'wildan@gmail.com',
            ]);

        $response = $this->get('/callback/google');

        // Should redirect to FE APP taskify
        $response->assertStatus(302);

        // Check user was created
        $this->assertDatabaseHas('users', [
            'email' => 'wildan@gmail.com',
            'name' => 'Test User',
            'social_id' => '1234567890',
            'social_type' => 'google'
        ]);

        // Check user activity was logged
        $user = User::where('email', 'wildan@gmail.com')->first();
        $this->assertDatabaseHas('user_activities', [
            'user_id' => $user->id,
            'action' => 'register'
        ]);
    }


    public function testGoogleCallbackFailure()
    {
        Socialite::shouldReceive('driver')
            ->with('google')
            ->andThrow(new \Exception('Error retrieving user'));

        $response = $this->get('/callback/google');
        $response->assertStatus(302);
    }
}
