<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TaskService;

class TaskController extends Controller
{
    private $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index()
    {

    }

    public function store(Request $request)
    {
        $task = $this->taskService->create($request->validated());
        return response()->json(['success' => true, 'data' => $task], 200);
    }

    public function update(Request $request, $id)   
    {

    }

    public function destroy($id)    
    {

    }

    public function show($id)
    {

    }

    public function bulkStore(Request $request)
    {

    }
}
