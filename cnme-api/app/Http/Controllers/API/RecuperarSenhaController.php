<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Services\MailSender;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;


class RecuperarSenhaController extends Controller
{
    public function solicitarNovaSenha($email){
        $usuario = User::where('email', $email)->first();

        if($usuario){
            $usuario->remember_token = bin2hex(random_bytes(8));
            $usuario->save();
            MailSender::notificacaoNovaSenha($usuario);

            return response()->json(
                array('message' => 'Email enviado ao usuário.') , 200); 
        }else{
            return response()->json(
                array('message' => "Email($email) do usuário não existe.") , 422); 
        }      
    }

    public function validarSolicitacao($email, $token){
        $usuario = User::where('email', $email)->where('remember_token',$token)->first();
        if($usuario)
            return new UserResource( $usuario );
        else 
        return response()->json(
                array('message' => "Requisição inválida.") , 422); 
    }

    public function atualizarSenha(Request $request, $usuarioId){
        $usuario = User::find($usuarioId);

        if($request->has('password') && $usuario){
            $usuario->password = Hash::make($request['password']);
            $usuario->email_verified_at = date('Y-m-d H:i:s');
            $usuario->save();
            return new UserResource( $usuario );
        }else 
            return response()->json( array('message' => "Requisição inválida.") , 422); 

    }
}
