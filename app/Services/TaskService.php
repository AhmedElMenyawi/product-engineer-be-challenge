<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\TaskHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Jobs\TaskHistoryCreation;

class TaskService
{
    public function create(array $data, int $userId): Task
    {
        try {
            $data['created_by'] = $userId;
            $data['task_token'] = $this->generateToken();
            $task = Task::create($data);
            $task->refresh();
            $this->logTaskHistory($task, ['action' => 'created', 'user_id' => $userId]);
            // TODO: Send email to assigned user through queue so it doesn't block the request
            return $task;
        } catch (\Exception $e) {
            Log::error('Failed to create task: ' . $e->getMessage());
            throw new \Exception('Failed to create task');
        }
    }

    public function findByToken(string $task_token): ?Task
    {
        try {
            return Task::where('task_token', $task_token)->first();
        } catch (\Exception $e) {
            Log::error('Failed to find task by token: ' . $e->getMessage());
            throw new \Exception('Failed to find task by token');
        }
    }

    public function findOrFailByToken(string $task_token): Task
    {
        $task = $this->findByToken($task_token);
        
        if (!$task) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Task not found');
        }
        
        return $task;
    }

    public function update(Task $task, array $data): ?Task
    {
        try {
            $filteredData = collect($data)
                ->only(Task::updatableFields())
                ->toArray();
            $assignedChanged = false;
            foreach ($filteredData as $key => $value) {
                if ($task->{$key} != $value) {
                    $this->logTaskHistory($task, [
                        'action' => 'updated',
                        'field_changed' => $key,
                        'old_value' => $task->{$key},
                        'new_value' => $value,
                        'changed_at' => now(),
                        'user_id' => Auth::user()->id
                    ]);
                    $task->{$key} = $value;
                    if ($key == 'assigned_to') {
                        $assignedChanged = true;
                    }
                }
            }
            $task->save();
            if ($assignedChanged) {
                // TODO: Send email to assigned user to notify that they are assigned to the task
                // TODO: Send email to old assigned user to notify that they are no longer assigned to the task
            }
            return $task;
        } catch (\Exception $e) {
            Log::error('Failed to update task: ' . $e->getMessage());
            throw new \Exception('Failed to update task');
        }
    }

    public function delete(Task $task): void
    {
        try {
            $task->delete();
            $this->logTaskHistory($task, ['action' => 'deleted', 'user_id' => Auth::user()->id]);
        } catch (\Exception $e) {
            Log::error('Failed to delete task: ' . $e->getMessage());
            throw new \Exception('Failed to delete task');
        }
    }

    public function getAllTasks(array $data) : ?\Illuminate\Pagination\LengthAwarePaginator
    {
        try {
            $query = Task::query();

            if (!empty($data['team_id'])) {
                $query->where('team_id', $data['team_id']);
            }

            if (!empty($data['assigned_to'])) {
                $query->where('assigned_to', $data['assigned_to']);
            }

            if (!empty($data['status'])) {
                $query->where('status', $data['status']);
            }

            if (!empty($data['priority'])) {
                $query->where('priority', $data['priority']);
            }

            if (!empty($data['start_time'])) {
                $query->where('start_time', $data['start_time']);
            }

            if (!empty($data['end_time'])) {
                $query->where('end_time', $data['end_time']);
            }

            if (!empty($data['created_by'])) {
                $query->where('created_by', $data['created_by']);
            }

            if (!empty($data['updated_by'])) {
                $query->where('updated_by', $data['updated_by']);
            }

            if (!empty($data['sort_by'])) {
                $query->orderBy($data['sort_by'], $data['sort_direction'] ?? 'asc');
            }

            if (!empty($data['page'])) {
                $query->paginate($data['per_page'] ?? 10, ['*'], 'page', $data['page']);
            }

            $sortBy = $data['sort_by'] ?? 'created_at';
            $sortOrder = $data['sort_order'] ?? 'desc';
            $perPage = $data['per_page'] ?? 10;
            $page = $data['page'] ?? 1;
            return $query->orderBy($sortBy, $sortOrder)->paginate($perPage, ['*'], 'page', $page);
        } catch (\Exception $e) {
            Log::error('Failed to get all tasks: ' . $e->getMessage());
            throw new \Exception('Failed to get all tasks');
        }
    }

    public function bulkCreate(array $data) : ?Array
    {
        try {
            $createdTasks = [];
            $userId = Auth::id();
            foreach ($data['tasks'] as $entry) {
                $task = $this->create($entry, $userId);
                $createdTasks[] = $task;
            }

            return $createdTasks;
        } catch (\Exception $e) {
            Log::error('Failed to bulk create tasks: ' . $e->getMessage());
            throw new \Exception('Failed to bulk create tasks');
        }
    }

    public function getStatusSummary(array $data) : ?Array
    {
        try {
            $from = Carbon::parse($data['from'])->startOfDay();
            $to = Carbon::parse($data['to'])->endOfDay();
            return Task::select('status', DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$from, $to])
                ->groupBy('status')
                ->orderBy('count', 'desc')
                ->pluck('count', 'status')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve status summary: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve status summary');
        }
    }
    private function generateToken(): ?string
    {
        try {
            do {
                $token = 'HC-' . strtoupper(Str::random(6));
            } while (Task::where('task_token', $token)->exists());

            return $token;
        } catch (\Exception $e) {
            Log::error('Failed to generate token: ' . $e->getMessage());
            throw new \Exception('Failed to generate token');
        }
    }

    private function logTaskHistory(Task $task, array $data): Void
    {
        try {
            TaskHistoryCreation::dispatch(
                $task->id,
                $data['user_id'],
                $data['action'],
                $data['field_changed'] ?? null,
                $data['old_value'] ?? null,
                $data['new_value'] ?? null,
                $data['changed_at'] ?? null
            );
        } catch (\Exception $e) {
            Log::error('Failed to dispatch task history job: ' . $e->getMessage());
        }
    }
}
