<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;

class UsuarioController extends Controller
{
    
    public function index()
    {
        return UserResource::collection(User::paginate(25));
    }
    
    public function store(Request $request)
    {
        $usuario = $request->has('id') ? User::findOrFail($request->id) : new User();

        $userData = $request->all();
        $usuario->fill($userData);
		$usuario->save();
        
        return new UserResource($usuario);
    }

   
    public function show($id)
    {
        return new UserResource(User::find($id));
    }

    
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        $usuarioData = $request->all();
        $usuario->fill($usuarioData);
        $usuario->save();


        return new UserResource($usuario);
    }

    
    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        if(isset($usuario)){
            $usuario->delete();
            return response(null,204);
        }

        return response('Usuário não encontrado.', 404);
    }
}
