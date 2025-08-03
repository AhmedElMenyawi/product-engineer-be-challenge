<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use App\Models\Team;
use App\Models\TaskHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $assignedUser;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'creator@example.com',
            'first_name' => 'Creator',
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
    }

    public function test_task_uses_soft_deletes()
    {
        $task = Task::factory()->create([
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $this->assertContains(SoftDeletes::class, class_uses($task));
    }

    public function test_task_has_correct_fillable_fields()
    {
        $task = new Task();
        
        $expectedFillable = [
            'task_token',
            'title',
            'description',
            'status',
            'priority',
            'team_id',
            'start_time',
            'end_time',
            'created_by',
            'assigned_to',
        ];

        $this->assertEquals($expectedFillable, $task->getFillable());
    }

    public function test_task_has_histories_relationship()
    {
        $task = Task::factory()->create([
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $history = TaskHistory::factory()->create([
            'task_id' => $task->id,
            'user_id' => $this->user->id,
            'action' => 'created'
        ]);

        $this->assertInstanceOf(TaskHistory::class, $task->histories->first());
        $this->assertEquals($history->id, $task->histories->first()->id);
    }

    public function test_task_can_have_multiple_histories()
    {
        $task = Task::factory()->create([
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        TaskHistory::factory()->create([
            'task_id' => $task->id,
            'user_id' => $this->user->id,
            'action' => 'created'
        ]);

        TaskHistory::factory()->create([
            'task_id' => $task->id,
            'user_id' => $this->user->id,
            'action' => 'updated',
            'field_changed' => 'title'
        ]);

        $this->assertEquals(2, $task->histories->count());
    }

    public function test_updatable_fields_method_returns_correct_fields()
    {
        $expectedFields = [
            'title',
            'description',
            'status',
            'assigned_to',
            'priority',
            'team_id',
            'start_time',
            'end_time'
        ];

        $this->assertEquals($expectedFields, Task::updatableFields());
    }

    public function test_task_can_be_created_with_minimal_data()
    {
        $task = Task::factory()->create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'medium',
            'team_id' => $this->team->id,
            'created_by' => $this->user->id,
            'end_time' => '2025-08-01 18:00:00'
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Test Task', $task->title);
        $this->assertEquals('Test Description', $task->description);
        $this->assertEquals('medium', $task->priority);
        $this->assertEquals($this->team->id, $task->team_id);
        $this->assertEquals($this->user->id, $task->created_by);
        $this->assertEquals('pending', $task->status); // Default status
    }

    public function test_task_can_be_assigned_to_user()
    {
        $task = Task::factory()->create([
            'title' => 'Assigned Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'team_id' => $this->team->id,
            'created_by' => $this->user->id,
            'assigned_to' => $this->assignedUser->id,
            'end_time' => '2025-08-01 18:00:00'
        ]);

        $this->assertEquals($this->assignedUser->id, $task->assigned_to);
    }

    public function test_task_can_have_different_statuses()
    {
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled', 'on_hold'];
        
        foreach ($statuses as $status) {
            $task = Task::factory()->create([
                'title' => "Task with status: {$status}",
                'description' => 'Test Description',
                'priority' => 'medium',
                'team_id' => $this->team->id,
                'created_by' => $this->user->id,
                'status' => $status,
                'end_time' => '2025-08-01 18:00:00'
            ]);

            $this->assertEquals($status, $task->status);
        }
    }

    public function test_task_can_have_different_priorities()
    {
        $priorities = ['low', 'medium', 'high'];
        
        foreach ($priorities as $priority) {
            $task = Task::factory()->create([
                'title' => "Task with priority: {$priority}",
                'description' => 'Test Description',
                'priority' => $priority,
                'team_id' => $this->team->id,
                'created_by' => $this->user->id,
                'end_time' => '2025-08-01 18:00:00'
            ]);

            $this->assertEquals($priority, $task->priority);
        }
    }

    public function test_task_has_timestamps()
    {
        $task = Task::factory()->create([
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $this->assertNotNull($task->created_at);
        $this->assertNotNull($task->updated_at);
    }

    public function test_task_can_have_start_and_end_times()
    {
        $startTime = '2025-08-01 10:00:00';
        $endTime = '2025-08-01 18:00:00';

        $task = Task::factory()->create([
            'title' => 'Task with times',
            'description' => 'Test Description',
            'priority' => 'medium',
            'team_id' => $this->team->id,
            'created_by' => $this->user->id,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);

        $this->assertEquals($startTime, $task->start_time);
        $this->assertEquals($endTime, $task->end_time);
    }

    public function test_task_token_is_unique()
    {
        $task1 = Task::factory()->create([
            'task_token' => 'HC-UNIQUE1',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $task2 = Task::factory()->create([
            'task_token' => 'HC-UNIQUE2',
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $this->assertNotEquals($task1->task_token, $task2->task_token);
    }

    public function test_task_can_be_soft_deleted()
    {
        $task = Task::factory()->create([
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $taskId = $task->id;
        $task->delete();

        $this->assertSoftDeleted($task);
        $this->assertDatabaseHas('tasks', [
            'id' => $taskId,
            'deleted_at' => $task->deleted_at
        ]);
    }

    public function test_task_can_be_restored()
    {
        $task = Task::factory()->create([
            'created_by' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $task->delete();
        $this->assertSoftDeleted($task);

        $task->restore();
        $this->assertNotSoftDeleted($task);
    }
} 