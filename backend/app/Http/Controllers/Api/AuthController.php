<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Traits\ApiResponse;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AuditService $auditService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['username', 'password']);

        if (!auth()->attempt($credentials)) {
            return $this->error('Invalid credentials. Please check your username and password.', 401);
        }

        $user = auth()->user();
        
        $user->tokens()->delete();
        $token = $user->createToken('pharmacovigilance-session')->plainTextToken;

        $this->auditService->log($user, 'auth.login');

        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auditService->log($request->user(), 'auth.logout');
        
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Session closed successfully');
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);
    }
}
