<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use JWTAuth;

class AuthController extends Controller
{

    public function login() {
        $credentials = request(['email', 'password']);
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Usuário não autorizado'], 401);
        }
        return response()->json([
            'token' => $token,
            'expires' => auth('api')->factory()->getTTL() * 600,
        ]);
    }

    public function logout(Request $request)
    {
        auth()->logout();

        $token = $request->header('Authorization');

        JWTAuth::invalidate($token);
        return response()->json(['message' => 'logout realizado com sucesso']);
    }
}
