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
use Illuminate\Support\Facades\Hash;
use App\Services\MailSender;
use App\Models\Unidade;
use App\Services\UsuarioService;

class UsuarioController extends Controller
{
    
    public function tipos(){
        return User::tipos();
    }

    public function removidos(Request $request)
    {
        $per_page = $request->per_page ? $request->per_page : 25;
        return UserResource::collection(User::onlyTrashed()->paginate($per_page));
    }

    public function all(Request $request)
    {
        $per_page = $request->per_page ? $request->per_page : 25;
        return UserResource::collection(User::withTrashed()->paginate($per_page));
    }

    public function index(Request $request)
    {
        $per_page = $request->per_page ? $request->per_page : 25;
        return UserResource::collection(User::paginate($per_page));
    }
    
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
                
            $usuario = new User();
            $usuarioData = $request->all();
            $usuarioData['password'] = Hash::make('#*cnme2019');

            $validator = Validator::make($usuarioData, $usuario->rules, $usuario->messages);

            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }

            $arrayTipos =  User::tipos();

            if($request->has('tipo') && !in_array($request['tipo'], $arrayTipos)){
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

    public function enviarConvite(Request $request, $usuarioId){

        try {
            
            $usuario = User::find($usuarioId);
            $usuario->remember_token = bin2hex(random_bytes(20));
            MailSender::convite($usuario);
            $usuario->convite_at = date('Y-m-d H:i:s');
            $usuario->save();

        }catch(\Exception $e){
            Log::error('UsuarioController::enviarConvite - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
        

        return new UserResource($usuario);
    }

    public function confirmar(Request $request){

        $token = $request->query('token1');
       
        if( $token ){
            $usarioConfirmado = User::where('remember_token', $token)->first();
           
            if(isset($usarioConfirmado)){
                
                if($usarioConfirmado->email === $request->email){
                    $usarioConfirmado->fill($request->all());
                    if($request->has('password'))
                        $usarioConfirmado->password = Hash::make($request['password']);
                    else
                        return response()->json(
                        array('message' => 'Informe a senha.') , 422);


                    $usarioConfirmado->email_verified_at = date('Y-m-d H:i:s');
                    $usarioConfirmado->save();

                    return new UserResource($usarioConfirmado);
                }else{//end if email
                    return response()->json(
                        array('message' => 'Email do usuário não confere com o cadastrado.') , 422); 
                }
            }else{//end if find usuario
                return response()->json(
                    array('message' => 'Usuário não encontrado') , 422); 
            }  
        }else{//end if token
            return response()->json(
                array('message' => 'Requisição inválida. Token não foi enviado') , 422); 
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

            if($request->has('tipo') && !in_array($request['tipo'], $arrayTipos)){
                return response()->json(
                    array('message' => "Tipo desconhecido. Tipos:(".implode("|",$arrayTipos).")") , 422);
            }

            $usuarioData = $request->all();

            $usuario->fill($usuarioData);
            if($request->has('password'))
                $usuario->password = Hash::make($request['password']);
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

            $result = Tarefa::where('responsavel_id', $usuario->id)->whereIn('status',[Tarefa::STATUS_ABERTA,Tarefa::STATUS_ANDAMENTO])->get();

            if($result->count()){
                return response()->json(
                    array('message' => 'O usuário possui tarefas em aberto ou em execução sob sua responsabilidade'), 422);
            
            }
            if(isset($usuario)){
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

    public function forceDelete($id){
        try {
            DB::beginTransaction();

            $user = User::withTrashed()->find($id);

            if(isset($user)){
                $user->forceDelete();

                DB::commit();
                return response(null,204);
            }else {
                return response()->json(
                    array('message' => "Usuário não encontrado.") , 422);
            }
            
        }catch(\Exception $e){
            DB::rollback();

            return response()->json(
                array('message' => "Usuário não pode ser removido. O usuário está envolvido nos processos de implatanção.") , 422);

        }
    }

    public function restore($id){
        $user = User::withTrashed()->find($id);
        if($user){
            $user->restore();
            return new UserResource($user);
        }else {
            response()->json(
                array('message' => "Usuário não encontrado.") , 404);
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
        $per_page = $request->per_page ? $request->per_page : 25;
        return UserResource::collection($list->orderBy('name')->paginate($per_page));
    }

    public function findByEmail(Request $request){
        $user = User::where('email', $request['email'])->first();

        return new UserResource($user);
    }

    public function getUsuariosNaoConfirmados(Request $request){
        $list = User::query();
        $list->whereNull('email_verified_at');
        $list->whereHas('unidade', function($query1){
            $query1->where('classe','=',Unidade::CLASSE_POLO);
        });

        $per_page = $request->per_page ? $request->per_page : 25;
        return UserResource::collection($list->orderBy('created_at')->paginate( $per_page ));
    }

    public function getGestoresNaoConfirmados(Request $request){
        $list = User::query();

        $list->where('tipo','=',User::TIPO_GESTOR);
        $list->whereHas('unidade', function($query1){
            $query1->where('classe','=',Unidade::CLASSE_POLO);
        });


        $list->whereNull('email_verified_at');

        $per_page = $request->per_page ? $request->per_page : 25;
        return UserResource::collection($list->orderBy('created_at')->paginate( $per_page ));
    }

    public function getUserPorToken(Request $request){
        if($request->has('token1')){
            $user = User::where('remember_token',$request->token1)->first();

            if($user)
                return new UserResource($user);
            else 
                return response()->json(array('message' => "Usuário não encontrado.") , 404);

        }else{
            return response()->json(
                array('message' => "Token não informado.") , 422);
        }        
    }

    public function admin(Request $request){
        $usuarioService = new UsuarioService();

        $admin = $usuarioService->admin();

        return new UserResource($admin);
    }
}
