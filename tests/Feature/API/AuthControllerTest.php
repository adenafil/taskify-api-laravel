<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testRegisterUserSuccess()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'User registered successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
        ]);
    }

    public function testRegisterUserWithEmptyData()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
            ]);
        $this->assertDatabaseMissing('users', [
            'name' => '',
            'email' => '',
        ]);
    }

    public function testRegisterUserWithAlreadyRegisteredEmail()
    {
        // First, register a user 😎
        $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        // Attempt to register the same user again 😱
        $response = $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The email has already been taken.',
            ]);
    }

    public function testRegisterButWithPasswordLessThanEightCharacters()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia',
            'password_confirmation' => 'rahasia',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The password field must be at least 8 characters.',
            ]);
        $this->assertDatabaseMissing('users', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
        ]);
    }

    public function testRegisterButWithPasswordConfirmationNotMatch()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The password field confirmation does not match.',
            ]);
        $this->assertDatabaseMissing('users', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
        ]);
    }

    public function testRegisterButEmailNotValid()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'wildan#gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The email field must be a valid email address.',
            ]);
        $this->assertDatabaseMissing('users', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail',
        ]);
    }

    public function testRegisterButNameIsExceedingMaxLength()
    {
        $response = $this->postJson('/api/register', [
            'name' => str_repeat('A', 256),
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The name field must not be greater than 255 characters.',
            ]);
        $this->assertDatabaseMissing('users', [
            'name' => str_repeat('A', 256),
            'email' => 'achmad.wildan@gmail.com',
        ]);
    }

    public function testRegisterSuccessAndTokenIsInDatabase()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User registered successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => 1,
            'tokenable_type' => 'App\\Models\\User',
        ]);
        $this->assertDatabaseHas('user_activities', [
            'user_id' => 1,
            'action' => 'register',
        ]);
    }

    public function testLoginSuccess()
    {
        $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful',
            ]);
    }

    public function testLoginButHisEmailIsEmpty()
    {
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => 'rahasia dong',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }


    public function testLoginButHisPasswordIsEmpty()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'achmad.wildan@gmail.com',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function testLoginButHisEmailNotRegistered()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not.registered@gmail.com',
            'password' => 'rahasia dong',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
        $this->assertDatabaseMissing('users', [
            'email' => 'not.registered@gmail.com',
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => 1,
        ]);
        $this->assertDatabaseMissing('user_activities', [
            'user_id' => 1,
            'action' => 'login',
        ]);
    }

    public function testLoginButHisPasswordIsWrong()
    {
        $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
        $this->assertDatabaseHas('users', [
            'email' => 'achmad.wildan@gmail.com',
        ]);
    }

    public function testLogoutSuccess()
    {
        $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful',
            ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => 1,
        ]);
        $this->assertDatabaseHas('user_activities', [
            'user_id' => 1,
            'action' => 'login',
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => 1,
            'revoked' => false,
        ]);

        $logoutResponse = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);
        $logoutResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful',
            ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => 1,
            'revoked' => false,
        ]);
        $this->assertDatabaseHas('user_activities', [
            'user_id' => 1,
            'action' => 'logout',
        ]);
    }

    public function testLogoutIsNotAuthenticated()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function testGetUserDataSuccessAndAuthenticated()
    {
        $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful',
            ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => 1,
        ]);
        $this->assertDatabaseHas('user_activities', [
            'user_id' => 1,
            'action' => 'login',
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => 1,
            'revoked' => false,
        ]);

        $userResponse = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);
        $userResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'name' => 'Achmad Wildan',
                    'email' => 'achmad.wildan@gmail.com',
                ],
            ]);
    }

    public function testGetUserDataButNotAuthenticated()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function testPatchProfileSuccess()
    {
        $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful',
            ]);

        $patchResponse = $this->patchJson('/api/user/update-profile', [
            'name' => 'Achmad Wildan Updated',
            'email' => 'achmad.wildan.updated@gmail.com',
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $patchResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => 1,
                    'name' => 'Achmad Wildan Updated',
                    'email' => 'achmad.wildan.updated@gmail.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => 1,
            'name' => 'Achmad Wildan Updated',
            'email' => 'achmad.wildan.updated@gmail.com',
        ]);
    }

    public function testPatchProfileFailedBecauseEmailAlreadyExists()
    {
        $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $this->postJson('/api/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful',
            ]);

        $patchResponse = $this->patchJson('/api/user/update-profile', [
            'name' => 'Achmad Wildan Updated',
            'email' => 'budi.santoso@gmail.com',
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $patchResponse->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseHas('users', [
            'id' => 1,
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
        ]);
    }

    public function testPatchProfileFailedBecauseIsNotAuthenticated()
    {
        $response = $this->patchJson('/api/user/update-profile', [
            'name' => 'Achmad Wildan Updated',
            'email' => 'achmad.wildan.updated@gmail.com',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function testDeleteAccountSuccess()
    {
        $this->postJson('/api/register', [
            'name' => 'Achmad Wildan',
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
            'password_confirmation' => 'rahasia dong',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'achmad.wildan@gmail.com',
            'password' => 'rahasia dong',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful',
            ]);

        $deleteResponse = $this->deleteJson('/api/user/delete', [], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $deleteResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Account deleted successfully',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => 1,
            'email' => 'achmad.wildan@gmail.com',
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => 1,
        ]);
    }

    public function testDeleteAccountButNotAuthenticated()
    {
        $response = $this->deleteJson('/api/user/delete');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
