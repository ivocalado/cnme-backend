<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Etapa;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\EtapaResource;
use App\Models\ProjetoCnme;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Models\Tarefa;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\EquipamentoProjetoResource;

class EtapaController extends Controller
{

    public function status(){
        return Etapa::status();
    }

    public function tipos(){
        return Etapa::tipos();
    }
    
    public function index()
    {
        return EtapaResource::collection(Etapa::paginate(25));  
    }

    public function store(Request $request)
    {
        $etapa = new Etapa();
        $etapaData = $request->all();

        $validator = Validator::make($etapaData, $etapa->rules, $etapa->messages);

        if ($validator->fails()) {
            return response()->json(
                array(
                "messages" => $validator->errors()
                ), 422); 
        }

        $projeto = ProjetoCnme::find($request->projeto_cnme_id);
        $usuario = User::find($request->usuario_id);

        $etapa->fill($etapaData);
        $etapa->projetoCnme()->associate($projeto);
        $etapa->usuario()->associate($usuario);

        $etapa->save();
        
        return new EtapaResource($etapa);
    }

    
    public function show($id)
    {
        $etapa = Etapa::find($id);
        if(!isset($etapa)){
            return response()->json(
                array('message' => 'Etapa do cronograma não encontrada.') , 404);
        }

        return new EtapaResource($etapa);
    }

   
    public function update(Request $request, $id)
    {
        $etapa = Etapa::find($id);
        if(!isset($etapa)){
            return response()->json(
                array('message' => 'Etapa não encontrada.') , 404);
        }

        $etapaData = $request->all();

        $etapa->fill($etapaData);
        $etapa->save();

        return new EtapaResource($etapa);
    }

    public function destroy($id)
    {
        $etapa = Etapa::find($id);

        if(!isset($etapa)){
            return response()->json(
                array('message' => 'Etapa do cronograma não encontrada.') , 404);
        }

        //$ok = Unidade::where('tipo_unidade_id', $id)->get()->isEmpty();

        $ok = true;
        if($ok){

            $etapa->delete();
            return response(null, 204);

        }else{
            return response()->json(
                array('message' => 'Etapa não pode ser excluída por ter tarefas relacionadas. Não pode ser removida') , 422);
        }
        
    }

    public function addTarefa(Request $request, $id){
        DB::beginTransaction();


        try{
            $etapa = Etapa::find($id);
            $tarefaData = $request->all();

            $tarefa = new Tarefa();
            $validator = Validator::make($tarefaData, $tarefa->rules, $tarefa->messages);

            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }

            $tarefa->fill($tarefaData);

            $etapa->tarefas()->save($tarefa);
            
            DB::commit();

            return response()->json(
                array(
                    "data" => $tarefa
                )
            );
        }catch(\Exception $e){
            DB::rollback();

            Log::error('EtapaController::addTarefa - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
        
    }

    public function tarefas(Request $request, $id){
        $etapa = Etapa::find($id);

        if(!isset($etapa)){
            return response()->json(
                array('message' => 'A tarefa não existe.') , 500);
        }

        return response()->json(
            Tarefa::where('etapa_id', $etapa->id)->get()
        );
    }

    public function removeTarefa(Request $request, $idEtapa, $idTarefa){
        $etapa = Etapa::find($idEtapa);

        if(!isset($etapa)){
            return response()->json(
                array('message' => 'A etapa não existe.') , 404);
        }

        $tarefa = Tarefa::find($idTarefa);

        if(!isset($tarefa)){
            return response()->json(
                array('message' => 'A tarefa não existe.') , 500);
        }else{
            $tarefa->delete();
            return response(null, 204);
        }

    }

    public function updateTarefa(Request $request, $idEtapa, $idTarefa){
        $etapa = Etapa::find($idEtapa);

        if(!isset($etapa)){
            return response()->json(
                array('message' => 'A etapa não existe.') , 404);
        }

        $tarefa = Tarefa::find($idTarefa);

        if(!isset($tarefa)){
            return response()->json(
                array('message' => 'A tarefa não existe.') , 404);
        }

        $tarefaData = $request->all();

        $tarefa->fill($tarefaData);

        $tarefa->save();
        return response()->json(
            array(
                "data" => $tarefa
            )
        );

    }

    public function equipamentos(Request $request, $etapaId){
        $etapa = Etapa::find($etapaId);

        if(!isset($etapa)){
            return response()->json(
                array('message' => 'A etapa não existe.') , 404);
        }

        return EquipamentoProjetoResource::collection($etapa->equipamentos());

    }

}
