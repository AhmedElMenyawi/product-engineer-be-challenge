<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request): ?JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ],
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to login: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => "Please try again later. or contact support.",
            ], 500);
        }
    }

    public function logout(Request $request): ?JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to logout: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => "Please try again later. or contact support.",
            ], 500);
        }
    }
}
