<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;
use App\Http\Requests\BulkStoreUserRequest;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(StoreUserRequest $request): ?JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $this->userService->create($data);
            return response()->json(['success' => true, 'data' => $user], 200);
        } catch (\Exception $e) {
            Log::error('Failed to create user: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.', 'error' => "Please try again later. or contact support."], 500);
        }
    }

    public function bulkStore(BulkStoreUserRequest $request): ?JsonResponse
    {
        try {
            $data = $request->validated();
            $users = $this->userService->bulkCreate($data);
            return response()->json(['success' => true, 'data' => $users], 200);
        } catch (\Exception $e) {
            Log::error('Failed to create users: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong.', 'error' => "Please try again later. or contact support."], 500);
        }
    }
}