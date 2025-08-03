<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use App\Models\Team;
use App\Models\TaskHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class TaskApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private User $assignedUser;
    private Team $team;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User'
        ]);
        
        $this->assignedUser = User::factory()->create([
            'email' => 'assigned@example.com',
            'first_name' => 'Assigned',
            'last_name' => 'User'
        ]);
        
        $this->team = Team::factory()->create([
            'name' => 'Test Team'
        ]);

        // Create token for authenticated tests
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_create_task()
    {
        Sanctum::actingAs($this->user);
        
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'team_id' => $this->team->id,
            'assigned_to' => $this->assignedUser->id,
            'start_time' => '2025-08-01 10:00:00',
            'end_time' => '2025-08-01 18:00:00'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'task_token',
                        'title',
                        'description',
                        'status',
                        'priority',
                        'team_id',
                        'assigned_to',
                        'created_by',
                        'start_time',
                        'end_time',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'team_id' => $this->team->id,
            'assigned_to' => $this->assignedUser->id,
            'created_by' => $this->user->id
        ]);
    }

    public function test_cannot_create_task_without_authentication()
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'team_id' => $this->team->id,
            'end_time' => '2025-08-01 18:00:00'
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(401);
    }

    public function test_cannot_create_task_with_invalid_data()
    {
        $taskData = [
            'title' => '', // Invalid: empty title
            'description' => 'Test Description',
            'priority' => 'invalid_priority', // Invalid priority
            'team_id' => 999, // Non-existent team
            'end_time' => '2025-08-01 18:00:00'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'priority', 'team_id']);
    }

    public function test_can_get_task_by_token()
    {
        $task = Task::factory()->create([
            'task_token' => 'HC-TEST123',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson("/api/v1/tasks/{$task->task_token}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'task_token' => 'HC-TEST123',
                        'title' => $task->title
                    ]
                ]);
    }

    public function test_returns_404_for_invalid_task_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/tasks/HC-INVALID');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Task not found.'
                ]);
    }

    public function test_can_update_task()
    {
        $task = Task::factory()->create([
            'title' => 'Original Title',
            'description' => 'Original Description',
            'priority' => 'low',
            'status' => 'pending',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'priority' => 'high',
            'status' => 'completed'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->putJson("/api/v1/tasks/{$task->task_token}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'title' => 'Updated Title',
                        'description' => 'Updated Description',
                        'priority' => 'high',
                        'status' => 'completed'
                    ]
                ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'status' => 'completed'
        ]);
    }

    public function test_can_mark_task_as_completed()
    {
        $task = Task::factory()->create([
            'status' => 'pending',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->putJson("/api/v1/tasks/{$task->task_token}", [
            'status' => 'completed'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed'
        ]);
    }

    public function test_cannot_update_nonexistent_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->putJson('/api/v1/tasks/HC-NONEXISTENT', [
            'title' => 'Updated Title'
        ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_task()
    {
        $task = Task::factory()->create([
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->deleteJson("/api/v1/tasks/{$task->task_token}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Task deleted successfully.'
                ]);

        $this->assertSoftDeleted($task);
    }

    public function test_cannot_delete_task_created_by_other_user()
    {
        Sanctum::actingAs($this->user);
        
        $otherUser = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $otherUser->id,
            'team_id' => $this->team->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->deleteJson("/api/v1/tasks/{$task->task_token}");

        $response->assertStatus(403);
    }

    public function test_can_get_all_tasks()
    {
        Task::factory()->create([
            'title' => 'Task 1',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        Task::factory()->create([
            'title' => 'Task 2',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/tasks');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'task_token',
                                'title',
                                'description',
                                'status',
                                'priority',
                                'created_at'
                            ]
                        ],
                        'current_page',
                        'per_page',
                        'total'
                    ]
                ]);

        $this->assertEquals(2, $response->json('data.total'));
    }

    public function test_can_filter_tasks_by_status()
    {
        Task::factory()->create([
            'title' => 'Pending Task',
            'status' => 'pending',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        Task::factory()->create([
            'title' => 'Completed Task',
            'status' => 'completed',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/tasks?status=completed');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.total'));
        $this->assertEquals('Completed Task', $response->json('data.data.0.title'));
    }

    public function test_can_filter_tasks_by_priority()
    {
        Task::factory()->create([
            'title' => 'High Priority Task',
            'priority' => 'high',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        Task::factory()->create([
            'title' => 'Low Priority Task',
            'priority' => 'low',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/tasks?priority=high');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.total'));
        $this->assertEquals('High Priority Task', $response->json('data.data.0.title'));
    }

    public function test_can_bulk_create_tasks()
    {
        $tasksData = [
            'tasks' => [
                [
                    'title' => 'Bulk Task 1',
                    'description' => 'Description 1',
                    'priority' => 'high',
                    'team_id' => $this->team->id,
                    'end_time' => '2025-08-01 18:00:00'
                ],
                [
                    'title' => 'Bulk Task 2',
                    'description' => 'Description 2',
                    'priority' => 'medium',
                    'team_id' => $this->team->id,
                    'end_time' => '2025-08-02 18:00:00'
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/tasks/bulk-create', $tasksData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => '2 tasks created.'
                ]);

        $this->assertDatabaseCount('tasks', 2);
        $this->assertDatabaseHas('tasks', ['title' => 'Bulk Task 1']);
        $this->assertDatabaseHas('tasks', ['title' => 'Bulk Task 2']);
    }

    public function test_can_get_status_summary()
    {
        Task::factory()->create([
            'status' => 'pending',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id,
            'created_at' => '2025-08-01 10:00:00'
        ]);

        Task::factory()->create([
            'status' => 'completed',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id,
            'created_at' => '2025-08-02 10:00:00'
        ]);

        Task::factory()->create([
            'status' => 'completed',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id,
            'created_at' => '2025-08-03 10:00:00'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/tasks/status-summary?from=2025-08-01&to=2025-08-03');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'pending' => 1,
                        'completed' => 2
                    ]
                ]);
    }

    public function test_task_history_is_logged_on_update()
    {
        $task = Task::factory()->create([
            'title' => 'Original Title',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->putJson("/api/v1/tasks/{$task->task_token}", [
            'title' => 'Updated Title'
        ]);

        $this->assertDatabaseHas('task_histories', [
            'task_id' => $task->id,
            'action' => 'updated',
            'field_changed' => 'title',
            'old_value' => 'Original Title',
            'new_value' => 'Updated Title',
            'user_id' => $this->user->id
        ]);
    }

    public function test_task_history_is_logged_on_delete()
    {
        $task = Task::factory()->create([
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->deleteJson("/api/v1/tasks/{$task->task_token}");

        $this->assertDatabaseHas('task_histories', [
            'task_id' => $task->id,
            'action' => 'deleted',
            'user_id' => $this->user->id
        ]);
    }
} 