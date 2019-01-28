<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    
    public function index()
    {
        return UserResource::collection(User::paginate(25));
    }
    
    public function store(Request $request)
    {

        DB::beginTransaction();

        try {
                
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

            DB::commit();
            
            return new UserResource($usuario);
            
        }catch(\Exception $e){
            DB::rollback();

            Log::error('UsuarioController::store - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
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

        DB::beginTransaction();

        try {

            $usuario = User::find($id);

            if(!isset($usuario)){
                return response()->json(
                    array('message' => 'Usuário não encontrado.') , 404);
            }

            $usuarioData = $request->all();

            $usuario->fill($usuarioData);
            $usuario->save();
            DB::commit();

            return new UserResource($usuario);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('UsuarioController::update - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    
    public function destroy($id)
    {

        DB::beginTransaction();

        try {

            $usuario = User::find($id);
            if(isset($usuario)){
                $usuario->delete();
                DB::commit();
                return response(null,204);
            }


            return response()->json(
                array('message' => 'Usuário não encontrado.'), 404);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('UsuarioController::destroy - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }
}
