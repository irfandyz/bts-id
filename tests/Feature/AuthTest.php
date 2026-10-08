<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => 'jhon_doe',
            'password' => 'supersecret',
            'password_confirmation' => 'supersecret',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'username',
                    'name',
                    'email',
                    'created_at',
                ],
            ])
            ->assertJson([
                'message' => 'User registered successfully',
                'data' => [
                    'username' => 'jhon_doe',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'username' => 'jhon_doe',
        ]);
    }

    public function test_register_returns_400_on_validation_failure(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => 'jh', // too short
            'password' => 'supersecret',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'username',
                    'password',
                ],
            ]);
    }

    public function test_user_can_login_and_receive_tokens(): void
    {
        User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
            'name' => 'Jhon Doe',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'jhon_doe',
            'password' => 'supersecret',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'username',
                        'name',
                    ],
                    'authentication_token',
                    'refresh_token',
                    'token_type',
                ],
            ])
            ->assertJson([
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'username' => 'jhon_doe',
                    ],
                    'token_type' => 'Bearer',
                ],
            ]);
    }

    public function test_login_returns_401_with_invalid_credentials(): void
    {
        User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'jhon_doe',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid username or password',
            ]);
    }

    public function test_user_can_refresh_token(): void
    {
        $user = User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'authentication_token',
                    'refresh_token',
                    'token_type',
                ],
            ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);
    }
}
