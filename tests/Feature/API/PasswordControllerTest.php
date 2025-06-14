<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testResetPasswordSuccessfully()
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

        $response = $this->patchJson('/api/user/change-password', [
            'current_password' => 'rahasia dong',
            'password' => 'rahasia baru',
            'password_confirmation' => 'rahasia baru',
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Password changed successfully',
        ]);
        // Verify the password has been updated
        $this->assertTrue(Hash::check('rahasia baru', \App\Models\User::find(1)->password));

    }

    public function testResetPasswordUnsuccessfullyBecauseCurrentPasswordIsWrong()
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

        $response = $this->patchJson('/api/user/change-password', [
            'current_password' => 'wrong-password',
            'password' => 'rahasia baru',
            'password_confirmation' => 'rahasia baru',
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Current password is incorrect',
        ]);
        // Verify the password has not been updated
        $this->assertTrue(Hash::check('rahasia dong', \App\Models\User::find(1)->password));
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

        // login as wildan the majesty
        $response = $this->postJson('/api/login', [
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
        ]);
        $response->assertStatus(200);

        $response = $this->patchJson('/api/user/change-password', [
            'current_password' => 'rahasia dong',
            'password' => 'rahasia',
            'password_confirmation' => 'rahasia',
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The password field must be at least 8 characters.',
        ]);
        // Verify the password has not been updated
        $this->assertTrue(Hash::check('rahasia dong', \App\Models\User::find(1)->password));
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

        // login as wildan the majesty
        $response = $this->postJson('/api/login', [
            'email' => 'wildan@gmail.com',
            'password' => 'rahasia dong',
        ]);
        $response->assertStatus(200);

        $response = $this->patchJson('/api/user/change-password', [
            'current_password' => 'rahasia dong',
            'password' => 'rahasia baru',
            'password_confirmation' => 'rahasia_baru',
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The password field confirmation does not match.',
        ]);
        // Verify the password has not been updated
        $this->assertTrue(Hash::check('rahasia dong', \App\Models\User::find(1)->password));
    }

    public function testResetPasswordUnsuccessfullyBecauseUserIsUnauthenticated()
    {
        $response = $this->patchJson('/api/user/change-password', [
            'current_password' => 'rahasia dong',
            'password' => 'rahasia baru',
            'password_confirmation' => 'rahasia baru',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

}
