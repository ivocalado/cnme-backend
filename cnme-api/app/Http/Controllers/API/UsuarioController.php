<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ProjetoCnme;
use App\Models\Tarefa;

class UsuarioController extends Controller
{
    
    public function tipos(){
        return User::tipos();
    }

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

            $arrayTipos =  User::tipos();

            if(!in_array($request['tipo'], $arrayTipos)){
                return response()->json(
                    array('message' => "Tipo desconhecido. Tipos:(".implode("|",$arrayTipos).")") , 422);
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

            $arrayTipos =  User::tipos();

            if(!in_array($request['tipo'], $arrayTipos)){
                return response()->json(
                    array('message' => "Tipo desconhecido. Tipos:(".implode("|",$arrayTipos).")") , 422);
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

            $usuario = User::findOrFail($id);

            if($usuario->isAdministrador())
            return response()->json(
                array('message' => 'O usuário é adminstrador. Não pode ser excluído.'), 422);

            if($usuario->id === $usuario->unidade->responsavel->id){
                return response()->json(
                    array('message' => 'O usuário é gestor da unidade '.$usuario->unidade->nome.'. Defina um novo responsável antes da remoção.' ), 422);

            }elseif($usuario->tipo === User::TIPO_GESTOR){
                $countGestoresUnidade =  DB::table('users')->where([
                    ['unidade_id',$usuario->unidade_id],
                    ['tipo', User::TIPO_GESTOR]
                    ])->count();
                    
                if($countGestoresUnidade === 1)
                    return response()->json(
                        array('message' => 'A unidade '.$usuario->unidade->nome.' possui apenas esse usuário como gestor. Indique um novo responsável pela unidade.'), 422);
            }

            $result = Tarefa::where('responsavel_id', $usuario->id)->whereIn('status',[Tarefa::STATUS_ABERTA,Tarefa::STATUS_EXECUCAO])->get();

            if($result->count()){
                return response()->json(
                    array('message' => 'O usuário possui tarefas em aberto ou em execução sob sua responsabilidade'), 422);
            
            }
            if(isset($usuario)){
                $usuario->ativo = false;
                $usuario->name = $usuario->name." (DELETADO)";
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

    public function searchNaoConfirmados(Request $request){
        $list = User::query();
        $list->whereNull('email_verified_at');
        return UserResource::collection($list->orderBy('created_at')->paginate(25));
    }


}
