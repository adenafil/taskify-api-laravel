<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testRequestPasswordResetSuccessfully()
    {
        // Create a user
        $this->postJson('/api/register', [
            'name' => 'Wildan',
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        // Request password reset
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'wildan@gmail.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Password reset link sent to your email',
        ]);
    }

    public function testRequestPasswordResetUnsuccessfullyBecauseEmailIsNotRegistered()
    {
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'wildan.isnot.registered.maam@gmail.com',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'We could not find a user with that email address',
        ]);
    }

    public function testResetPasswordSuccessfully()
    {
        // Create a user
        $this->postJson('/api/register', [
            'name' => 'Wildan',
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        // Simulate the password reset process
        $user = \App\Models\User::where('email', 'wildan@gmail.com')->first();
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'wildan@gmail.com',
            'token' => $token,
            'password' => 'rahasia baru',
            'password_confirmation' => 'rahasia baru',
        ]);


        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Password has been reset successfully',
        ]);

        // Verify the password has been updated
        $this->assertTrue(Hash::check('rahasia baru', \App\Models\User::where('email', 'wildan@gmail.com')->first()->password));
    }

    public function testResetPasswordUnsuccessfullyBecauseTokenIsInvalid()
    {
        // Create a user
        $this->postJson('/api/register', [
            'name' => 'Wildan',
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'wildan@gmail.com',
            'token' => 'invalid-token',
            'password' => 'rahasia baru',
            'password_confirmation' => 'rahasia baru',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'This password reset token is invalid.',
        ]);
    }

    public function testResetPasswordUnsuccessfullyBecauseNewPasswordIsTooShort()
    {
        // Create a user
        $this->postJson('/api/register', [
            'name' => 'Wildan',
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $user = \App\Models\User::where('email', 'wildan@gmail.com')->first();
        $token = Password::broker()->createToken($user);
        // reset password with too short new password
        $response = $this->postJson('/api/reset-password', [
            'email' => 'wildan@gmail.com',
            'token' => $token,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The password field must be at least 8 characters.',
        ]);

    }

    public function testResetPasswordUnsuccessfullyBecauseNewPasswordConfirmationDoesNotMatch()
    {
        // Create a user
        $this->postJson('/api/register', [
            'name' => 'Wildan',
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $user = \App\Models\User::where('email', 'wildan@gmail.com')->first();
        $token = Password::broker()->createToken($user);
        // reset password with too short new password
        $response = $this->postJson('/api/reset-password', [
            'email' => 'wildan@gmail.com',
            'token' => $token,
            'password' => 'password asli',
            'password_confirmation' => 'password_asli',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The password field confirmation does not match.',
        ]);

    }
}
