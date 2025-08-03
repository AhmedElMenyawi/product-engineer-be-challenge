<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Jobs\TaskHistoryCreation;
use App\Models\Task;
use App\Models\User;
use App\Models\Team;
use App\Models\TaskHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;

class TaskHistoryJobTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->task = Task::factory()->create([
            'created_by' => $this->user->id
        ]);
    }

    public function test_task_history_job_creates_history_record()
    {
        // Arrange
        $job = new TaskHistoryCreation(
            $this->task->id,
            $this->user->id,
            'created',
            null,
            null,
            null,
            now()
        );

        // Act
        $job->handle();

        // Assert
        $this->assertDatabaseHas('task_histories', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'action' => 'created'
        ]);
    }

    public function test_task_history_job_handles_field_changes()
    {
        // Arrange
        $job = new TaskHistoryCreation(
            $this->task->id,
            $this->user->id,
            'updated',
            'title',
            'Old Title',
            'New Title',
            now()
        );

        // Act
        $job->handle();

        // Assert
        $this->assertDatabaseHas('task_histories', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'action' => 'updated',
            'field_changed' => 'title',
            'old_value' => 'Old Title',
            'new_value' => 'New Title'
        ]);
    }

    public function test_task_history_job_handles_deletion()
    {
        // Arrange
        $job = new TaskHistoryCreation(
            $this->task->id,
            $this->user->id,
            'deleted'
        );

        // Act
        $job->handle();

        // Assert
        $this->assertDatabaseHas('task_histories', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'action' => 'deleted'
        ]);
    }

    public function test_task_history_job_logs_success()
    {
        // Arrange
        Log::shouldReceive('info')->once()->with(
            'Task history created successfully',
            \Mockery::on(function ($args) {
                return $args['task_id'] === $this->task->id &&
                       $args['action'] === 'created' &&
                       $args['user_id'] === $this->user->id;
            })
        );

        $job = new TaskHistoryCreation(
            $this->task->id,
            $this->user->id,
            'created'
        );

        // Act
        $job->handle();
    }

    public function test_task_history_job_handles_exceptions()
    {
        // Arrange - Create a job with invalid task_id to trigger exception
        $job = new TaskHistoryCreation(
            99999, // Non-existent task ID
            $this->user->id,
            'created'
        );

        // Act & Assert
        $this->expectException(\Exception::class);
        $job->handle();
    }

    public function test_task_history_job_failed_method_logs_error()
    {
        // Arrange
        $exception = new \Exception('Database connection failed');
        Log::shouldReceive('error')->once()->with(
            'TaskHistoryCreation job failed permanently',
            \Mockery::on(function ($args) use ($exception) {
                return $args['task_id'] === $this->task->id &&
                       $args['action'] === 'created' &&
                       $args['user_id'] === $this->user->id &&
                       $args['error'] === $exception->getMessage();
            })
        );

        $job = new TaskHistoryCreation(
            $this->task->id,
            $this->user->id,
            'created'
        );

        // Act
        $job->failed($exception);
    }

    public function test_task_history_job_has_correct_retry_configuration()
    {
        // Arrange
        $job = new TaskHistoryCreation(
            $this->task->id,
            $this->user->id,
            'created'
        );

        // Assert
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(30, $job->timeout);
        $this->assertEquals([10, 30, 60], $job->backoff);
    }

    public function test_task_history_job_can_be_dispatched()
    {
        // Arrange
        Queue::fake();

        // Act
        TaskHistoryCreation::dispatch(
            $this->task->id,
            $this->user->id,
            'created'
        );

        // Assert
        Queue::assertPushed(TaskHistoryCreation::class);
    }
} 