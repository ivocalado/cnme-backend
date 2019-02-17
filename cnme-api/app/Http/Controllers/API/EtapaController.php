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
use App\Models\EquipamentoProjeto;
use App\Http\Resources\TarefaResource;

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

        if($request->has('status')){
            $status = $request['status'];
            $arrayStatus =  Etapa::status();

            if(!in_array($status, $arrayStatus)){
                return response()->json(
                    array('message' => "Status desconhecido. Status:(".implode("|",$arrayStatus).")") , 422);
            }
        }

        if($request->has('tipo')){
            $tipo = $request['tipo'];
            $arrayTipos =  Etapa::tipos();

            if(!in_array($tipo, $arrayTipos)){
                return response()->json(
                    array('message' => "Tipo desconhecido. Status:(".implode("|",$arrayTipos).")") , 422);
            }
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

    public function addTarefaEnvio(Request $request, $projetoId){
        DB::beginTransaction();

        try{
            
            $projeto = ProjetoCnme::find($projetoId);

            if(!isset($projeto)){
                return response()->json(
                    array('message' => 'O projeto não existe.') , 422);
            }

            $disponiveisId = DB::select(
                "select ep.id from equipamento_projetos ep
                    where ep.id not in (
                    select tep.equipamento_projeto_id from tarefa_equipamento_projeto tep
                        inner join tarefas t on t.id = tep.tarefa_id
                        inner join equipamento_projetos ep1 on ep1.id = tep.equipamento_projeto_id 
                        inner join etapas e on e.id = t.etapa_id
                        where e.tipo = 'ENVIO'
                ) and ep.projeto_cnme_id = ?", [$projetoId]);
            
            $disponiveisId = array_column($disponiveisId,'id');
            if(empty($disponiveisId)){
                return response()->json(
                    array('message' => 'Não há equipamentos disponíveis para planejamento de entrega. ') , 422);
            }

            if(!isset($request["equipamentos_projeto_ids"])){
                return response()->json(
                    array('message' => 'Defina os equipamentos do projeto que serão adicionados.') , 422);
            }

            /**Verifica se todos os Ids enviados estão disponíveis*/
            $diffCount = count(array_diff($request["equipamentos_projeto_ids"], $disponiveisId));
            if($diffCount>0){
                return response()->json(
                    array('message' => 'Verifique se todos os equipamentos enviados estão disponíveis no projeto') , 422);
            }

            
            if(isset($request['etapa_id'])){
                $etapa = Etapa::find($request['etapa_id']);
            }else{
                $etapa = new Etapa();
                $etapa->projetoCnme()->associate($projeto);
                $etapa->usuario()->associate($projeto->usuario);
                $etapa->status = Etapa::STATUS_ABERTA;
                $etapa->tipo = Etapa::TIPO_ENVIO;
                $etapa->descricao = Etapa::DESC_ETAPA_ENVIO;
    
                $etapa->save();
            }
            
            $tarefaData = $request->all();
            
            if(!isset($tarefaData['nome']))
                $tarefaData["nome"] = Tarefa::DESC_TAREFA_ENVIO;

            $tarefaData["status"] = Tarefa::STATUS_ABERTA;
            $tarefaData["etapa_id"] = $etapa->id;

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

            $equipamentosProjetoIds = $request["equipamentos_projeto_ids"];

            
            $tarefa->equipamentosProjetos()->attach($equipamentosProjetoIds);
            $tarefa->save();

            DB::commit();

            return new TarefaResource($tarefa);
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
                array('message' => 'A tarefa não existe.') , 422);
        }

        return TarefaResource::collection(Tarefa::where('etapa_id', $etapa->id)->get());
        
    }

    public function removeTarefa(Request $request, $idEtapa, $idTarefa){
        $etapa = Etapa::find($idEtapa);

        if(!isset($etapa)){
            return response()->json(
                array('message' => 'A etapa não existe.') , 422);
        }

        $tarefa = Tarefa::find($idTarefa);

        if(!isset($tarefa)){
            return response()->json(
                array('message' => 'A tarefa não existe.') , 422);
        }else{
            $tarefa->delete();
            return response(null, 204);
        }

    }

    public function updateTarefa(Request $request, $idEtapa, $idTarefa){
        $etapa = Etapa::find($idEtapa);

        if(!isset($etapa)){
            return response()->json(
                array('message' => 'A etapa não existe.') , 422);
        }

        $tarefa = Tarefa::find($idTarefa);

        if(!isset($tarefa)){
            return response()->json(
                array('message' => 'A tarefa não existe.') , 422);
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
                array('message' => 'A etapa não existe.') , 422);
        }

        return EquipamentoProjetoResource::collection($etapa->equipamentos());

    }

}
