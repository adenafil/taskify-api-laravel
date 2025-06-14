<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateTaskSuccessfully()
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

        // Create a task
        $response = $this->postJson('/api/tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => now()->addDays(7),
            'status' => 'in_progress',
            'priority' => 'medium',
            'user_id' => 1, // wildan the majesty😱
            'category' => 'work',
            'categoryIcon' => 'work-icon',
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Task created successfully',
        ]);
        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'user_id' => 1, // wildan the majesty 😱
            'status' => 'in_progress',
            'priority' => 'medium',
            'category' => 'work',
            'categoryIcon' => 'work-icon',
        ]);
    }

    public function testCreateTaskUnsuccessfullyBecauseUserIsNotAuthenticated()
    {
        // Attempt to create a task without authentication
        $response = $this->postJson('/api/tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => now()->addDays(7),
            'status' => 'in_progress',
            'priority' => 'medium',
            'user_id' => 1, // wildan the majesty😱
            'category' => 'work',
            'categoryIcon' => 'work-icon',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testCreateTaskUnsuccessfullyBecauseTheMandatoryDataIsNull()
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

        // Attempt to create a task without mandatory data
        $response = $this->postJson('/api/tasks', [], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The title field is required. (and 2 more errors)',
            'errors' => [
                'title' => ['The title field is required.'],
                'description' => ['The description field is required.'],
                'due_date' => ['The due date field is required.'],
            ],
        ]);
        $this->assertDatabaseMissing('tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'user_id' => 1, // wildan the majesty 😱
            'status' => 'in_progress',
            'priority' => 'medium',
            'category' => 'work',
            'categoryIcon' => 'work-icon',
        ]);
    }

    public function testGetTasksSuccessfully()
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

        // create task for wildan the majesty
        $this->testCreateTaskSuccessfully();

        // Get tasks
        $response = $this->getJson('/api/tasks', [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'due_date',
                    'status',
                    'priority',
                    'user_id',
                    'category',
                    'categoryIcon',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
        $this->assertDatabaseHas('tasks', [
            'user_id' => 1, // wildan the majesty 😱
        ]);
    }

    public function testGetTasksUnsuccessfullyBecauseUserIsNotAuthenticated()
    {
        // Attempt to get tasks without authentication
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUpdateTaskSuccessfully()
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

        // create task for wildan the majesty
        $this->testCreateTaskSuccessfully();

        // Update the task
        $response = $this->patchJson('/api/tasks/1', [
            'title' => 'Updated Task',
            'description' => 'Updated Task Description',
            'due_date' => now()->addDays(7),
            'status' => 'completed',
            'priority' => 'high',
            'category' => 'personal',
            'categoryIcon' => 'personal-icon',
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Task updated successfully',
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => 'Updated Task',
            'description' => 'Updated Task Description',
            'status' => 'completed',
            'priority' => 'high',
            'category' => 'personal',
            'categoryIcon' => 'personal-icon',
        ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => 1,
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'in_progress',
            'priority' => 'medium',
            'category' => 'work',
            'categoryIcon' => 'work-icon',
        ]);
    }

    public function testUpdateTaskUnsuccessfullyBecauseUserIsNotAuthenticated()
    {
        // Attempt to update a task without authentication
        $response = $this->patchJson('/api/tasks/1', [
            'title' => 'Updated Task',
            'description' => 'Updated Task Description',
            'due_date' => now()->addDays(7),
            'status' => 'completed',
            'priority' => 'high',
            'category' => 'personal',
            'categoryIcon' => 'personal-icon',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUpdateTaskUnsuccessfullyBecauseTaskNotFound()
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

        // create task for wildan the majesty
        $this->testCreateTaskSuccessfully();

        // Update the task
        $response = $this->patchJson('/api/tasks/999', [
            'title' => 'Updated Task',
            'description' => 'Updated Task Description',
            'due_date' => now()->addDays(7),
            'status' => 'completed',
            'priority' => 'high',
            'category' => 'personal',
            'categoryIcon' => 'personal-icon',
        ], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Task not found',
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'in_progress',
            'priority' => 'medium',
            'category' => 'work',
            'categoryIcon' => 'work-icon',
        ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => 999,
            'title' => 'Updated Task',
            'description' => 'Updated Task Description',
            'status' => 'completed',
            'priority' => 'high',
            'category' => 'personal',
            'categoryIcon' => 'personal-icon',
        ]);
    }

    public function testDeleteTaskSuccessfully()
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

        // create task for wildan the majesty
        $this->testCreateTaskSuccessfully();

        // Delete the task
        $response = $this->deleteJson('/api/tasks/1', [], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Task deleted successfully',
        ]);
        $this->assertDatabaseMissing('tasks', [
            'id' => 1,
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'in_progress',
            'priority' => 'medium',
            'category' => 'work',
            'categoryIcon' => 'work-icon',
        ]);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function testDeleteTaskUnsuccessfullyBecauseUserIsNotAuthenticated()
    {
        // Attempt to delete a task without authentication
        $response = $this->deleteJson('/api/tasks/1');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testDeleteTaskUnsuccessfullyBecauseTaskNotFound()
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

        // create task for wildan the majesty
        $this->testCreateTaskSuccessfully();

        // Delete the task
        $response = $this->deleteJson('/api/tasks/999', [], [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Task not found',
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'in_progress',
            'priority' => 'medium',
            'category' => 'work',
            'categoryIcon' => 'work-icon',
        ]);
    }

    public function testExportTasksToExcelAndDownloadSuccessfully()
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

        // create task for wildan the majesty
        $this->testCreateTaskSuccessfully();

        // Export tasks to Excel
        $response = $this->getJson('/api/my-tasks/export', [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // how to assert header the name that is random
        $filenameHeader = $response->headers->get('Content-Disposition');
        $this->assertNotNull($filenameHeader);
        $this->assertMatchesRegularExpression(
            '/^attachment;\s+filename=tasks_Wildan_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.xlsx$/',
            $filenameHeader
        );
        $this->assertDatabaseHas('tasks', [
            'user_id' => 1, // wildan the majesty 😱
        ]);
        $this->assertDatabaseCount('tasks', 1);
    }

    public function testExportTasksToExcelAndDownloadUnsuccessfullyBecauseUserIsNotAuthenticated()
    {
        // Attempt to export tasks to Excel without authentication
        $response = $this->getJson('/api/my-tasks/export');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testExportTasksToExcelUnsuccessfullyBecauseThereAreNoTasks()
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

        // Attempt to export tasks to Excel
        $response = $this->getJson('/api/my-tasks/export', [
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'No tasks found for this user',
        ]);
        $this->assertDatabaseMissing('tasks', [
            'user_id' => 1, // wildan the majesty 😱
        ]);
        $this->assertDatabaseCount('tasks', 0);
    }
}

