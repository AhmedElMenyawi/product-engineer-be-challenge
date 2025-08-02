<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\TaskController;
use App\Http\Controllers\API\V1\UserController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Tasks Routes
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::post('/', [TaskController::class, 'store']);
            Route::get('/{task_token}', [TaskController::class, 'show']);
            Route::put('/{task_token}', [TaskController::class, 'update']);
            Route::delete('/{task_token}', [TaskController::class, 'destroy']);
            Route::post('/bulk', [TaskController::class, 'bulkStore']);
        });

        // Users Routes
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{user_token}', [UserController::class, 'show']);
            Route::put('/{user_token}', [UserController::class, 'update']);
            Route::delete('/{user_token}', [UserController::class, 'destroy']);
        });
    });
});
