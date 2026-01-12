<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\JwtService;
use App\Models\UserDevice;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class JwtAuth
{
    public function handle($request, Closure $next)
    {
        $auth = $request->header('Authorization');

        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = substr($auth, 7);

        try {
            $payload = app(JwtService::class)->decode($token);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        if ($payload['exp'] < time()) {
            return response()->json(['message' => 'Token expired'], 401);
        }

        $user = User::find($payload['id']);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        // ✅ attach user & payload
        Auth::setUser($user);
        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }
}
