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
                    
                //dd($equipamentosProjetoIds);
            }

            /**Verifica se todos os Ids enviados estão disponíveis*/
            $diffCount = count(array_diff($equipamentosProjetoIds, $disponiveisId));
            if($diffCount>0){
                return response()->json(
                    array('message' => 'Verifique se todos os equipamentos enviados estão disponíveis no projeto') , 422);
            }

            
            if(isset($request['etapa_id']) || $request['etapa_id'] !== null){
                $etapa = $projeto->getEtapaEnvio();

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


            
            $tarefa->equipamentosProjetos()->attach($equipamentosProjetoIds);
            $tarefa->save();

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
                    "Projeto/Tarefa não correspondentes a ação de envio." , 422);
            }
            
            if($projeto->status === ProjetoCnme::STATUS_PLANEJAMENTO){
                $projeto->status = ProjetoCnme::STATUS_ENVIADO;
                $projeto->save();
            }
            
            $tarefaEnvio->status = Tarefa::STATUS_ANDAMENTO;
            $tarefaEnvio->data_inicio = date("Y-m-d");
            $tarefaEnvio->save();

            $tarefaEnvio->etapa->status = Etapa::STATUS_ANDAMENTO;
            $tarefaEnvio->etapa->save();

            $tarefaEnvio->equipamentosProjetos->each(function ($eP, $key) {
                $eP->status = EquipamentoProjeto::STATUS_ENVIADO;
                $eP->save();
            });

            
            DB::commit();

            $etapa = Etapa::find($tarefaEnvio->etapa_id);
            return new EtapaResource($etapa);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('EnviarController::enviar - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function entregar(Request $request, $projetoId, $tarefaId){
        DB::beginTransaction();
        try{
            $projeto = ProjetoCnme::find($projetoId);
            $tarefaEnvio = Tarefa::find($tarefaId);

            if($tarefaEnvio->etapa->projeto_cnme_id !== $projeto->id ||
                $tarefaEnvio->etapa->tipo !== Etapa::TIPO_ENVIO
                || !isset($projeto) || !isset($tarefaEnvio)){
                return response()->json(
                    "Projeto/Tarefa não correspondentes a ação de entrega." , 422);
            }

            $tarefaEnvio->data_fim = date("Y-m-d");
            $tarefaEnvio->status = Tarefa::STATUS_CONCLUIDA;
            $tarefaEnvio->save();

            $tarefaEnvio->equipamentosProjetos->each(function($eP, $value){
                $eP->status = EquipamentoProjeto::STATUS_ENTREGUE;
                $eP->save();
            });

            $etapa = Etapa::find($tarefaEnvio->etapa_id);

            $entregasAndamento = $etapa->tarefas->contains('status',Tarefa::STATUS_ANDAMENTO);

            if(!$entregasAndamento){
                $etapa->status = Etapa::STATUS_CONCLUIDA;
                $etapa->save();

                $projeto->status = ProjetoCnme::STATUS_ENTREGUE;
                $projeto->save();
            }
            DB::commit();

            $etapa = Etapa::find($tarefaEnvio->etapa_id);
            return new EtapaResource($etapa);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('EnviarController::entregar - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function enviarAll(Request $request, $projetoId){
        DB::beginTransaction();
        try{
            $projeto = ProjetoCnme::findOrFail($projetoId);
            $projeto->status = ProjetoCnme::STATUS_ENVIADO;
            $projeto->save();

            $etapasEnvio = $projeto->etapas->filter(function ($e, $key) {
                return $e->tipo === Etapa::TIPO_ENVIO;
            });

            foreach($etapasEnvio as $etapa){
                $etapa->status = Etapa::STATUS_ANDAMENTO;
                $etapa->save();

                $tarefasEnvio = $etapa->tarefas;
                
                foreach($tarefasEnvio as $t){
                    $t->status= Tarefa::STATUS_ANDAMENTO;
                    $t->save();
                    $t->equipamentosProjetos->each(function ($eP, $key) {
                        $eP->status = EquipamentoProjeto::STATUS_ENVIADO;
                        $eP->save();
                    });
                }     
            }
            $projeto->save();
            DB::commit();

            return EtapaResource::collection($etapasEnvio);


        }catch(\Exception $e){
            DB::rollback();

            Log::error('EnviarController::enviarAll - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }
}