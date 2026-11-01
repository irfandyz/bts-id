<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'name' => $validated['name'] ?? $validated['username'],
            'email' => $validated['email'] ?? null,
        ]);

        return $this->success([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
        ], 'User registered successfully', 201);
    }

    /**
     * Login user and issue access & refresh tokens.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('username', $validated['username'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->error('Invalid username or password', 401);
        }

        $authToken = $user->createToken('auth_token', ['access-api']);
        $refreshToken = $user->createToken('refresh_token', ['issue-token']);

        return $this->success([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
            ],
            'authentication_token' => $authToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    /**
     * Refresh access and refresh tokens.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        $newAuthToken = $user->createToken('auth_token', ['access-api']);
        $newRefreshToken = $user->createToken('refresh_token', ['issue-token']);

        return $this->success([
            'authentication_token' => $newAuthToken->plainTextToken,
            'refresh_token' => $newRefreshToken->plainTextToken,
            'token_type' => 'Bearer',
        ], 'Token refreshed successfully');
    }

    /**
     * Logout user by revoking current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Successfully logged out');
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
        ]);
    }
}
