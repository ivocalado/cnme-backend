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

            if($usuario->tipo === User::TIPO_GESTOR){
                $countGestoresUnidade =  DB::table('users')->where([
                    ['unidade_id',$usuario->unidade_id],
                    ['tipo', User::TIPO_GESTOR]
                    ])->count();
                    
                if($countGestoresUnidade === 1)
                    return response()->json(
                        array('message' => 'A unidade '.$usuario->unidade->nome.' possui apenas esse usuário como gestor. Indique um novo responsável pela unidade.'), 422);
            }

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

    public function checkEmail(Request $request, $email){
        $emailValido = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        return response()->json($emailValido && User::where("email",$email)->count() == 0,200) ;
    }

    public function checkCpf(Request $request, $cpf){
        return response()->json(User::where("cpf",$cpf)->count() == 0, 200);
    }


    public function search(Request $request){
        $list = User::query();
        if($request->has('q')){       
            $list->orWhere("name", 'ILIKE', '%'.$request->q.'%');
            $list->orWhere("email", 'ILIKE', '%'.$request->q.'%');
            $list->orWhere("cpf",$request->q);
        }

        return UserResource::collection($list->orderBy('name')->paginate(25));

    }

}
