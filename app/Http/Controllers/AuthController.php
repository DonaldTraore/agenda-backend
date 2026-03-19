<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->userRepository->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'timezone' => $request->timezone ?? 'UTC',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(['user' => auth()->user(), 'token' => $token]);
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    public function refresh(): JsonResponse
    {
        return response()->json(['token' => JWTAuth::refresh(JWTAuth::getToken())]);
    }
}