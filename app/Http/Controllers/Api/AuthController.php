<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::query()->create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->accessToken;

        return success_response([
            'user'         => new UserResource($user),
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 'Registration successful', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($validated)) {
            return error_response('Invalid credentials', 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('auth_token')->accessToken;

        return success_response([
            'user'         => new UserResource($user),
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return success_response(message: 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return success_response(new UserResource($request->user()));
    }
}
