<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CronjobControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUpdateExpiredTask()
    {

        // create wildan the majesty user
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

        // Create a task that is in progress for wildan the majesty
        $task = \App\Models\Task::create([
            'title' => 'Test Expired Progress Task',
            'description' => 'This task is progresss',
            'user_id' => 1, // wildan the majesty 😱
            'status' => 'in_progress',
            'due_date' => now()->subDays(1),
        ]);

        // Call the updateExpiredTask method
        $response = $this->getJson('/api/cron/update-expired-task');

        // Assert the response status
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Expired tasks updated successfully',
        ]);

        // Assert the task status has been updated to expired
        $task->refresh();
        $this->assertEquals('expired', $task->status);
    }
}
