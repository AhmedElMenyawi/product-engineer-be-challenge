<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserService
{
    public function create(array $data): ?User
    {
        try {
            $data['password'] = $this->generateRandomPassword();
            $user = User::create($data);
            $user->auto_generated_password = $data['password'];
            // TODO: Send email to created user with random password (in future..reset password and start getting tasks) through queue so it doesn't block the request
            // AS : SendCreateUserEmailJob::dispatch($user);
            //Inisde it will be Mail::to($user->email)->send(new CreateUserMail($user));
            // For now I will return the password in response so it can be used in our case to test
            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to create user: ' . $e->getMessage());
            throw new \Exception('Failed to create user');
        }
    }

    public function bulkCreate(array $data): ?array
    {
        try {
            $users = [];
            foreach ($data['users'] as $userData) {
                $users[] = $this->create($userData);
            }
            return $users;
        } catch (\Exception $e) {
            Log::error('Failed to create users: ' . $e->getMessage());
            throw new \Exception('Failed to create users');
        }
    }

    private function generateRandomPassword(): ?String
    {
        return Str::random(10);
    }
}
