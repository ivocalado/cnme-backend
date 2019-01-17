<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    
    public function index()
    {
        return UserResource::collection(User::paginate(25));
    }
    
    public function store(Request $request)
    {
        $usuario = $request->has('id') ? User::findOrFail($request->id) : new User();

        $usuarioData = $request->all();

        $validator = Validator::make($usuarioData, $usuario->rules, $usuario->messages);

        if ($validator->fails()) {
            return response()->json(
                array(
                "messages" => $validator->errors()
                ), 422); 
        }


        $usuario->fill($usuarioData);
		$usuario->save();
        
        return new UserResource($usuario);
    }

   
    public function show($id)
    {
        $usuario = User::find($id);

        if(!isset($usuario)){
            return response()->json(
                array('message' => 'Usuário não encontrado.') , 404);
        }

        return new UserResource($usuario);
    }

    
    public function update(Request $request, $id)
    {
        $usuario = User::find($id);

        if(!isset($usuario)){
            return response()->json(
                array('message' => 'Usuário não encontrado.') , 404);
        }

        $usuarioData = $request->all();

        $validator = Validator::make($usuarioData, $usuario->rules, $usuario->messages);

        if ($validator->fails()) {
            return response()->json(
                array(
                "messages" => $validator->errors()
                ), 422); 
        }

        $usuario->fill($usuarioData);
        $usuario->save();


        return new UserResource($usuario);
    }

    
    public function destroy($id)
    {
        $usuario = User::find($id);
        if(isset($usuario)){
            $usuario->delete();
            return response(null,204);
        }

        return response()->json(
            array('message' => 'Usuário não encontrado.'), 404);
    }
}
