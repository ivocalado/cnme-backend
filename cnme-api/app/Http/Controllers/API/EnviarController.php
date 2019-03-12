<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProjetoCnme;
use App\Models\Tarefa;
use App\Models\EquipamentoProjeto;
use App\Http\Resources\ProjetoResource;
use Illuminate\Support\Facades\DB;
use App\Models\Etapa;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\EtapaResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TarefaResource;

class EnviarController extends Controller
{

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
                    array('message' => 'Não há equipamentos disponíveis para planejamento de envio. ') , 422);
            }

            $equipamentosProjetoIds = $request["equipamentos_projeto_ids"];

            if(!isset($equipamentosProjetoIds)){
                
                $equipamentosProjetoIds  = EquipamentoProjeto::where([
                    ['projeto_cnme_id',$projetoId],
                    ['status',EquipamentoProjeto::STATUS_PLANEJADO]
                ])->pluck('id')->toArray();
                    
            }

            /**Verifica se todos os Ids enviados estão disponíveis*/
            $diffCount = count(array_diff($equipamentosProjetoIds, $disponiveisId));
            if($diffCount>0){
                return response()->json(
                    array('message' => 'Verifique se todos os equipamentos enviados estão disponíveis no projeto') , 422);
            }

            $etapa = $projeto->getEtapaEnvio();

            if($etapa === null){
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
                DB::rollback();
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }



            $tarefa->fill($tarefaData);
            $etapa->tarefas()->save($tarefa);
            
            $tarefa->equipamentosProjetos()->attach($equipamentosProjetoIds);
            $tarefa->save();

            $errorsDatas = $projeto->validarDatasPrevistas();

           
            if(!empty($errorsDatas)){
                DB::rollback();
                return response()->json(
                    array(
                    "messages" => $errorsDatas
                    ), 422); 
            }

            DB::commit();

            $etapa = Etapa::find($tarefa->etapa_id);
            return new EtapaResource($etapa);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('EnviarController::addTarefaEnvio - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
        
    }

    public function enviar(Request $request, $projetoId, $tarefaId){
        DB::beginTransaction();
        try{
            $projeto = ProjetoCnme::findOrFail($projetoId);
            $tarefaEnvio = Tarefa::findOrFail($tarefaId);

            if($tarefaEnvio->etapa->projeto_cnme_id !== $projeto->id ||
                $tarefaEnvio->etapa->tipo !== Etapa::TIPO_ENVIO){
                return response()->json(
                    array('message' => "Projeto/Tarefa não correspondentes a ação de envio." , 422));
            }

            $tarefaEnvio->fill($request->all());
            
            $tarefaEnvio->enviar();
            DB::commit();

            $etapa = Etapa::find($tarefaEnvio->etapa_id);

            if($request->notificar)
                $tarefaEnvio->notificar();
                
            return new EtapaResource($etapa);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('EnviarController::enviar - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()." File: ".$e->getFile()."(". $e->getLine().")") , 500);

        }
    }

    public function entregar(Request $request, $projetoId, $tarefaId){
        DB::beginTransaction();
        try{
            $projeto = ProjetoCnme::find($projetoId);
            $tarefaEnvio = Tarefa::find($tarefaId);

            if(!isset($projeto) || !isset($tarefaEnvio) ||
                $tarefaEnvio->etapa->projeto_cnme_id !== $projeto->id ||
                $tarefaEnvio->etapa->tipo !== Etapa::TIPO_ENVIO
                ){
                return response()->json(
                    array('message' => "Projeto/Tarefa não correspondentes a ação de entrega."),422);
            }

            $tarefaEnvio->fill($request->all());
            
            $tarefaEnvio->entregar();
            DB::commit();

            if($request->notificar)
                $tarefaEnvio->notificar();

            $etapa = Etapa::find($tarefaEnvio->etapa_id);

            return new EtapaResource($etapa);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('EnviarController::entregar - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }
    /**
     * Faz o envio de todas as tarefas
     */

    public function enviarAll(Request $request, $projetoId){
        DB::beginTransaction();
        try{
            $projeto = ProjetoCnme::findOrFail($projetoId);
            $etapaEnvio = $projeto->getEtapaEnvio();

            if(!$etapaEnvio)
                return response()->json(
                    array('message' => "Envio não foi planejado." , 422));
           
            $tarefasEnvio = $etapaEnvio->tarefas;
            
            foreach($tarefasEnvio as $t){
                $t->enviar();
            }     
           
            if($request->notificar){
                $tarefasEnvio->first()->notificar();
            }
                
            DB::commit();
            return new EtapaResource($etapaEnvio);


        }catch(\Exception $e){
            DB::rollback();

            Log::error('EnviarController::enviarAll - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()." File: ".$e->getFile()."(". $e->getLine().")") , 500);

        }
    }
}
