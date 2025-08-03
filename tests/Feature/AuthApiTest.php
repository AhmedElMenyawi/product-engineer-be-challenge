<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AuthApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user' => [
                            'id',
                            'first_name',
                            'last_name',
                            'email',
                            'created_at',
                            'updated_at'
                        ],
                        'token'
                    ]
                ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_user_cannot_login_with_invalid_email()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ]);
    }

    public function test_user_cannot_login_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ]);
    }

    public function test_login_requires_email_and_password()
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_requires_valid_email_format()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_logout_with_valid_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Logged out successfully'
                ]);

        // Verify token is invalidated
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token)
        ]);
    }

    public function test_user_cannot_logout_without_token()
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }

    public function test_user_cannot_logout_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json'
        ])->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }

    public function test_user_can_access_protected_endpoint_with_valid_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/user');

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name
                ]);
    }

    public function test_user_cannot_access_protected_endpoint_without_token()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_user_cannot_access_protected_endpoint_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json'
        ])->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_multiple_tokens_can_be_created_for_same_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // First login
        $response1 = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        // Second login
        $response2 = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $token1 = $response1->json('data.token');
        $token2 = $response2->json('data.token');

        $this->assertNotEquals($token1, $token2);

        // Both tokens should work
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
            'Accept' => 'application/json'
        ])->getJson('/api/user')->assertStatus(200);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2,
            'Accept' => 'application/json'
        ])->getJson('/api/user')->assertStatus(200);
    }

    public function test_logout_invalidates_only_current_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Create two tokens
        $response1 = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response2 = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $token1 = $response1->json('data.token');
        $token2 = $response2->json('data.token');

        // Logout with first token
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/logout')->assertStatus(200);

        // First token should be invalid
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
            'Accept' => 'application/json'
        ])->getJson('/api/user')->assertStatus(401);

        // Second token should still work
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2,
            'Accept' => 'application/json'
        ])->getJson('/api/user')->assertStatus(200);
    }

    public function test_login_response_includes_user_data()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'email' => 'test@example.com',
                            'first_name' => 'John',
                            'last_name' => 'Doe'
                        ]
                    ]
                ]);

        // Verify password is not included in response
        $this->assertArrayNotHasKey('password', $response->json('data.user'));
    }
} 