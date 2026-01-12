<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user()
    {
        $user = auth()->user();
        return response()->json([
            "status" => true,
            "user" => $user
        ]);
    }
}
