<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Services\JwtService;
use App\Models\UserDevice;
use App\Models\User;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'phone' => 'nullable|string|max:15|unique:users,phone',
                'password' => 'required|string|min:6',
                'role' => 'nullable|string|in:customer,seller,admin',
                'address' => 'nullable|string|',
                'status' => 'nullable|string',
            ]);

            $validatedData['password'] = bcrypt($validatedData['password']);
            User::create($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed or email already exists',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error'
            ], 500);
        }
    }


    public function login(Request $request, JwtService $jwt)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if($user->status == 0){
             return response()->json([
                'status' => false,
                'messsage' => 'This account is inactive, so please contact to the helpline!',
             ], 401);
        }

        $payload = [
            'id' => $user->id,
            'email' => $user->email,
            'exp' => time() + (config('app.jwt_expiry_days') * 86400),
        ];

        $token = $jwt->generate($payload);

        UserDevice::create([
            'user_id' => $user->id,
            'token' => $token,
        ]);

        return response()->json([
            'status' => true,
            'token' => $token,
        ]);
    }
}
