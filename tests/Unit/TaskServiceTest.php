<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TaskService;
use App\Models\Task;
use App\Models\User;
use App\Models\Team;
use App\Models\TaskHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $taskService;
    private User $user;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->taskService = new TaskService();
        
        // Create test user and team
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User'
        ]);
        
        $this->team = Team::factory()->create([
            'name' => 'Test Team'
        ]);
        
        Auth::login($this->user);
    }

    public function test_can_create_task()
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'team_id' => $this->team->id,
            'assigned_to' => $this->user->id,
            'start_time' => '2025-08-01 10:00:00',
            'end_time' => '2025-08-01 18:00:00'
        ];

        $task = $this->taskService->create($taskData, $this->user->id);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Test Task', $task->title);
        $this->assertEquals('Test Description', $task->description);
        $this->assertEquals('high', $task->priority);
        $this->assertEquals($this->team->id, $task->team_id);
        $this->assertEquals($this->user->id, $task->assigned_to);
        $this->assertEquals($this->user->id, $task->created_by);
        $this->assertNotNull($task->task_token);
        $this->assertStringStartsWith('HC-', $task->task_token);
        $this->assertEquals('pending', $task->status);
    }

    public function test_can_find_task_by_token()
    {
        $task = Task::factory()->create([
            'task_token' => 'HC-TEST123',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $foundTask = $this->taskService->findByToken('HC-TEST123');

        $this->assertInstanceOf(Task::class, $foundTask);
        $this->assertEquals($task->id, $foundTask->id);
    }

    public function test_returns_null_for_invalid_token()
    {
        $foundTask = $this->taskService->findByToken('HC-INVALID');

        $this->assertNull($foundTask);
    }

    public function test_can_update_task()
    {
        $task = Task::factory()->create([
            'title' => 'Original Title',
            'description' => 'Original Description',
            'priority' => 'low',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'priority' => 'high',
            'status' => 'completed'
        ];

        $updatedTask = $this->taskService->update($task, $updateData);

        $this->assertEquals('Updated Title', $updatedTask->title);
        $this->assertEquals('Updated Description', $updatedTask->description);
        $this->assertEquals('high', $updatedTask->priority);
        $this->assertEquals('completed', $updatedTask->status);
    }

    public function test_update_logs_history()
    {
        $task = Task::factory()->create([
            'title' => 'Original Title',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $updateData = ['title' => 'Updated Title'];

        $this->taskService->update($task, $updateData);

        $history = TaskHistory::where('task_id', $task->id)->first();
        
        $this->assertNotNull($history);
        $this->assertEquals('updated', $history->action);
        $this->assertEquals('title', $history->field_changed);
        $this->assertEquals('Original Title', $history->old_value);
        $this->assertEquals('Updated Title', $history->new_value);
        $this->assertEquals($this->user->id, $history->user_id);
    }

    public function test_can_delete_task()
    {
        $task = Task::factory()->create([
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $this->taskService->delete($task);

        $this->assertSoftDeleted($task);
    }

    public function test_delete_logs_history()
    {
        $task = Task::factory()->create([
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $this->taskService->delete($task);

        $history = TaskHistory::where('task_id', $task->id)->first();
        
        $this->assertNotNull($history);
        $this->assertEquals('deleted', $history->action);
        $this->assertEquals($this->user->id, $history->user_id);
    }

    public function test_can_get_all_tasks_with_filters()
    {
        // Create multiple tasks
        Task::factory()->create([
            'title' => 'Task 1',
            'status' => 'pending',
            'priority' => 'high',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        Task::factory()->create([
            'title' => 'Task 2',
            'status' => 'completed',
            'priority' => 'medium',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        // Test filtering by status
        $pendingTasks = $this->taskService->getAllTasks(['status' => 'pending']);
        $this->assertEquals(1, $pendingTasks->count());

        // Test filtering by priority
        $highPriorityTasks = $this->taskService->getAllTasks(['priority' => 'high']);
        $this->assertEquals(1, $highPriorityTasks->count());

        // Test filtering by team
        $teamTasks = $this->taskService->getAllTasks(['team_id' => $this->team->id]);
        $this->assertEquals(2, $teamTasks->count());
    }

    public function test_can_get_status_summary()
    {
        // Create tasks with different statuses
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

        $summary = $this->taskService->getStatusSummary([
            'from' => '2025-08-01',
            'to' => '2025-08-03'
        ]);

        $this->assertArrayHasKey('pending', $summary);
        $this->assertArrayHasKey('completed', $summary);
        $this->assertEquals(1, $summary['pending']);
        $this->assertEquals(2, $summary['completed']);
    }

    public function test_bulk_create_tasks()
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

        $createdTasks = $this->taskService->bulkCreate($tasksData);

        $this->assertCount(2, $createdTasks);
        $this->assertEquals('Bulk Task 1', $createdTasks[0]->title);
        $this->assertEquals('Bulk Task 2', $createdTasks[1]->title);
        $this->assertEquals($this->user->id, $createdTasks[0]->created_by);
        $this->assertEquals($this->user->id, $createdTasks[1]->created_by);
    }

    public function test_generates_unique_tokens()
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'team_id' => $this->team->id,
            'end_time' => '2025-08-01 18:00:00'
        ];

        $task1 = $this->taskService->create($taskData, $this->user->id);
        $task2 = $this->taskService->create($taskData, $this->user->id);

        $this->assertNotEquals($task1->task_token, $task2->task_token);
        $this->assertStringStartsWith('HC-', $task1->task_token);
        $this->assertStringStartsWith('HC-', $task2->task_token);
    }

    public function test_throws_exception_on_create_failure()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to create task');

        // Try to create task with invalid data (missing required fields)
        $this->taskService->create([], $this->user->id);
    }
} 