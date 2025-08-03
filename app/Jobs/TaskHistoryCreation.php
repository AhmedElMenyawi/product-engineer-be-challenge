<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TaskHistory;
use Illuminate\Support\Facades\Log;

class TaskHistoryCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Retry up to 3 times
    public $timeout = 30; // 30 seconds timeout
    public $backoff = [10, 30, 60]; // Wait 10s, 30s, 60s between retries

    private int $taskId;
    private int $userId;
    private string $action;
    private ?string $fieldChanged;
    private $oldValue;
    private $newValue;
    private $changedAt;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $taskId,
        int $userId,
        string $action,
        ?string $fieldChanged = null,
        $oldValue = null,
        $newValue = null,
        $changedAt = null
    ) {
        $this->taskId = $taskId;
        $this->userId = $userId;
        $this->action = $action;
        $this->fieldChanged = $fieldChanged;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
        $this->changedAt = $changedAt;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            TaskHistory::create([
                'task_id' => $this->taskId,
                'user_id' => $this->userId,
                'action' => $this->action,
                'field_changed' => $this->fieldChanged,
                'old_value' => $this->oldValue,
                'new_value' => $this->newValue,
                'changed_at' => $this->changedAt ?? now(),
            ]);

            Log::info('Task history created successfully', [
                'task_id' => $this->taskId,
                'action' => $this->action,
                'user_id' => $this->userId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create task history', [
                'task_id' => $this->taskId,
                'action' => $this->action,
                'user_id' => $this->userId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('TaskHistoryCreation job failed permanently', [
            'task_id' => $this->taskId,
            'action' => $this->action,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}
