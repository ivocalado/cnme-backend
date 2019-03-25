<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use JWTAuth;
use App\User;

class AuthController extends Controller
{

    public function login(Request $request) {
        $credentials = request(['email', 'password']);
        
       

        if (!$token = auth('api')->attempt($credentials)) {
           
            $userDeletado = User::onlyTrashed()->where('email',$request['email'])->first();
            if($userDeletado){
                return response()->json(['error' => 'Conta bloqueada. Contate o gestor da sua unidade.'], 401);
            }else{
                return response()->json(['error' => 'Login e/ou senha incorretos.'], 401);
            }
                
        }else{
            return response()->json([
                'token' => $token,
                'expires' => auth('api')->factory()->getTTL() * 60,
            ]);
        }
        
    }

    public function logout(Request $request)
    {
        auth()->logout();

        $token = $request->header('Authorization');

        JWTAuth::invalidate($token);
        return response()->json(['message' => 'logout realizado com sucesso']);
    }

    public function refresh()
    {
        $token = auth('api')->refresh();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

}
