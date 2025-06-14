<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadAvatarControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUploadAvatarSuccessfully()
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

        // Upload avatar
        $response = $this->postJson('/api/user/upload-avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Avatar uploaded successfully',
        ]);
    }

    public function testUploadAvatarUnsuccessfullyBecauseFileIsNotAnImage()
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

        // Upload avatar
        $response = $this->postJson('/api/user/upload-avatar', [
            'avatar' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The avatar field must be an image. (and 1 more error)',
            'errors' => [
                'avatar' => ['The avatar field must be an image.', 'The avatar field must be a file of type: jpeg, png, jpg, gif, webp, svg.'],
            ],
        ]);
    }

    public function testUploadAvatarUnsuccesfullyBecauseItIsUnauthenticated()
    {
        // Upload avatar without authentication
        $response = $this->postJson('/api/user/upload-avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
