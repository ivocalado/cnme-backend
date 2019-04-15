<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UnidadeResource;
use App\Models\Unidade;
use App\Models\Localidade;
use App\Http\Resources\LocalidadeResource;
use App\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\TipoUnidade;

class UnidadeController extends Controller
{

    /**
     * Retornar polos sem projeto
     */
    public function polosNovos(){
        return UnidadeResource::collection(Unidade::doesnthave('projetoCnme')->where('classe', Unidade::CLASSE_POLO)->paginate(25));
    }

    public function index()
    {
        return UnidadeResource::collection(Unidade::paginate(25));
    }
    
    public function store(Request $request)
    {

        DB::beginTransaction();

        try {
            $unidade = $request->has('id') ? Unidade::find($request->id) : new Unidade;
            $unidadeData = $request->all();
    
            $validator = Validator::make($unidadeData, $unidade->rules, $unidade->messages);
    
            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }

            if($request->has('localidade')){
                $localidadeData = $request->localidade;
                $localidade = new Localidade();

                $validatorLocal = Validator::make($localidadeData, $localidade->rules, $localidade->messages);

                if ($validatorLocal->fails()) {
                    return response()->json(
                        array(
                        "messages" => $validatorLocal->errors()
                        ), 422); 
                }else{
                    $localidade->fill($localidadeData);
                    $localidade->save();
                    $unidade->localidade()->associate($localidade);
                }
            }

            $tipoUnidade = TipoUnidade::find($request->tipo_unidade_id);
            $unidade->classe = $tipoUnidade->classe;
    
            $unidade->fill($unidadeData);
            $unidade->save();


            /**create usuario gestor */

            $usuarioGestor = new User();
            $usuarioGestor->name = $request->has('responsavel')?
                                                        $request->responsavel     
                                                        :'Gestor '. $unidade->nome;
            $usuarioGestor->email = $request->has('emailResponsavel')? 
                                                        $request->emailResponsavel    
                                                        :$unidade->email;
            $usuarioGestor->tipo = User::TIPO_GESTOR;
            $usuarioGestor->unidade()->associate($unidade);
            $passwordAleatorio = bin2hex(openssl_random_pseudo_bytes(4));
            $usuarioGestor->password = Hash::make($passwordAleatorio);
            $usuarioGestor->save();

            $unidade->responsavel_id = $usuarioGestor->id;
            $unidade->save();

            DB::commit();

            return new UnidadeResource($unidade);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('UnidadeController::store - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
       
    }

   
    public function show($id)
    {
        $unidade = Unidade::find($id);
        if(!isset($unidade)){
            return response()->json(
                array('message' => 'Unidade não encontrada.') , 404);
        }

        return new UnidadeResource($unidade);
    }

    
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $unidade = Unidade::find($id);
            if(!isset($unidade)){
                return response()->json(
                    array('message' => 'Unidade não encontrada.') , 404);
            }

            $unidadeData = $request->all();

            // $validator = Validator::make($unidadeData, $unidade->rules, $unidade->messages);

            // if ($validator->fails()) {
            //     return response()->json(
            //         array(
            //         "messages" => $validator->errors()
            //         ), 422); 
            // }

            if($request->has('responsavel_id')){
                $novoReponsavel = User::findOrFail($request['responsavel_id']);
                if(!$novoReponsavel->isGestor()){
                    $novoReponsavel->tipo = User::TIPO_GESTOR;
                    $novoReponsavel->save();
                }

            }

            $unidade->fill($unidadeData);
            $unidade->save();
            DB::commit();

            return new UnidadeResource($unidade);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('UnidadeController::update - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    
    public function destroy($id)
    {

        DB::beginTransaction();

        try {

            $unidade = Unidade::find($id);

            if($unidade->admin)
                return response()->json(
                    array('message' => 'Unidade '.$unidade->nome.' é gestora não pode ser removida.') , 422);

            if(isset($unidade)){

                $localidade = Localidade::find($unidade->localidade_id);

                if(isset($localidade)){

                    $unidade->localidade()->dissociate();
                    $unidade->save();
                    $localidade->delete();
                }

                $unidade->delete();
                DB::commit();
                return response(null,204);
            }

            return response()->json(
                array('message' => 'Unidade não encontrada.') , 404);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('UnidadeController::destroy - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function checkEmail(Request $request, $email){
        $emailValido = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

        return response()->json($emailValido && Unidade::where("email",$email)->count() == 0, 200);
    }

    public function checkInep(Request $request, $inep){
        return response()->json(Unidade::where("codigo_inep", $inep)->count() == 0, 200);
    }

    public function addLocalidade(Request $request, $idUnidade){

        DB::beginTransaction();

        try {
            $unidade = Unidade::find($idUnidade);
            $localidade = new Localidade();
            $localidadeData = $request->all();

            $validator = Validator::make($localidadeData, $localidade->rules, $localidade->messages);
            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }

            $localidade->fill($localidadeData);
            $localidade->save();

            $unidade->localidade()->associate($localidade);
            $unidade->save();
            DB::commit();

            return new UnidadeResource($unidade);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('UnidadeController::addLocalidade - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
      
    }

    public function updateLocalidade(Request $request, $idUnidade){
        $unidade = Unidade::find($idUnidade);
        $localidade = Localidade::find($unidade->localidade_id);
        $localidadeData = $request->all();

        $localidade->fill($localidadeData);
        $localidade->save();
        

        return new LocalidadeResource($localidade);
    }

    public function usuarios($idUnidade){
     
        return  UserResource::collection(User::where('unidade_id', $idUnidade)->withTrashed()->paginate(10));
    }

    public function search(Request $request){
        $list = Unidade::query();
        if($request->has('q')){       
            $list->orWhere("codigo_inep",$request->q);
            $list->orWhere("nome", 'ILIKE', '%'.$request->q.'%');
        }

        return UnidadeResource::collection($list->orderBy('nome')->paginate(25));

    }

    public function admin(Request $request){
        $unidade = Unidade::where('classe', Unidade::CLASSE_ADMIN)->first();
        return new UnidadeResource($unidade);
    }

    public function mec(Request $request){
        $unidade = Unidade::where('classe', Unidade::CLASSE_MEC)->first();
        return new UnidadeResource($unidade);
    }

    public function tvescola(Request $request){
        $unidade = Unidade::where('classe', Unidade::CLASSE_TVESCOLA)->first();
        return new UnidadeResource($unidade);
    }

    public function gestoras(Request $request){
        $unidades = Unidade::orWhere('classe', Unidade::CLASSE_MEC)
            ->orWhere('classe', Unidade::CLASSE_TVESCOLA)->get();

        return UnidadeResource::collection($unidades);
    }

    public function polos(Request $request){
        $unidades = Unidade::where('classe', Unidade::CLASSE_POLO)->paginate(25);

        return UnidadeResource::collection($unidades);
    }

    public function empresas(Request $request){
        $unidades = Unidade::where('classe', Unidade::CLASSE_EMPRESA)->paginate(25);

        return UnidadeResource::collection($unidades);
    }
}
