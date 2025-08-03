<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TaskService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\BulkStoreTaskRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StatusSummaryRequest;

class TaskController extends Controller
{
    private $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request) : ?JsonResponse
    {
        try {
            $tasks = $this->taskService->getAllTasks($request->all());
            return response()->json(['success' => true, 'data' => $tasks], 200);
        } catch (\Exception $e) {
            Log::error('Failed to get all tasks: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.', 'error' => "Please try again later. or contact support."], 500);
        }
    }

    public function store(StoreTaskRequest $request): ?JsonResponse
    {
        try {
            $data = $request->validated();
            $task = $this->taskService->create($data, Auth::user()->id);
            return response()->json(['success' => true, 'data' => $task], 200);
        } catch (\Exception $e) {
            Log::error('Failed to store task: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.', 'error' => "Please try again later. or contact support."], 500);
        }
    }

    public function update(UpdateTaskRequest $request, string $task_token): ?JsonResponse
    {
        try {
            $task = $this->taskService->findByToken($task_token);

            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            $data = $request->validated();
            if (empty($data)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No changes submitted. Task unchanged.',
                    'data' => $task
                ], 200);
            }

            $updatedTask = $this->taskService->update($task, $request->validated());

            return response()->json([
                'success' => true,
                'data' => $updatedTask
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update task: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.', 'error' => "Please try again later. or contact support."], 500);
        }
    }

    public function destroy(string $task_token): ?JsonResponse
    {
        try {
            $task = $this->taskService->findByToken($task_token);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found.'
                ], 404);
            }

            $this->authorize('delete', $task);
            $this->taskService->delete($task);

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully.'
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this task.'
            ], 403);
        } catch (\Exception $e) {
            Log::error('Failed to delete task: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.', 'error' => "Please try again later. or contact support."], 500);
        }
    }

    public function show(string $task_token): ?JsonResponse
    {
        try {
            $task = $this->taskService->findByToken($task_token);
            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found.'
                ], 404);
            }
            return response()->json(['success' => true, 'data' => $task], 200);
        } catch (\Exception $e) {
            Log::error('Failed to show task: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.', 'error' => "Please try again later. or contact support."], 500);
        }
    }

    public function bulkStore(BulkStoreTaskRequest $request) : ?JsonResponse
    {
        try {
            $data = $request->validated();
            $tasks = $this->taskService->bulkCreate($data);
            return response()->json([
                'success' => true,
                'message' => count($tasks) . ' tasks created.',
                'data' => $tasks
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to bulk create tasks: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.', 'error' => "Please try again later. or contact support."], 500);
        }
    }

    public function statusSummary(StatusSummaryRequest $request) : ?JsonResponse
    {
        try {
            $data = $request->validated();
            $summary = $this->taskService->getStatusSummary($data);
            return response()->json(['success' => true, 'data' => $summary], 200);
        } catch (\Exception $e) {
            Log::error('Failed to get status summary: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.', 'error' => "Please try again later. or contact support."], 500);
        }
    }
}
