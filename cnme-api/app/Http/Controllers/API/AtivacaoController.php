<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

use App\Models\ProjetoCnme;
use App\Models\Tarefa;
use App\Models\Etapa;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\EtapaResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TarefaResource;
use App\Models\EquipamentoProjeto;


class AtivacaoController extends Controller
{
    public function addTarefAtivacao(Request $request, $projetoId){
        DB::beginTransaction();

        try{
            $projeto = ProjetoCnme::find($projetoId);

            if(!isset($projeto)){
                return response()->json(
                    array('message' => 'O projeto não existe.') , 422);
            }

            $etapaEnvio = $projeto->getEtapaEnvio();
            if(!isset($etapaEnvio)){
                return response()->json(
                    array('message' => 'O projeto não definiu a etapa de ENVIO. Antes de planejar a ativação, planeje o ENVIO e INSTALAÇÃO.') , 422);
            }


            $etapaInstalacao = $projeto->getEtapaInstalacao();
            if(!isset($etapaInstalacao)){
                return response()->json(
                    array('message' => 'O projeto não definiu a etapa de INSTALAÇÃO. Antes de planejar a ativação, planeje a INSTALAÇÃO.') , 422);
            }
            
            $etapaInstalacao = $projeto->firstOrCreateEtapa(Etapa::TIPO_ATIVACAO);
            $tarefaInstalacao = $etapaInstalacao->firstOrCreateTarefa();

            $tarefaData = $request->all();
            $tarefaData['etapa_id'] = $etapaInstalacao->id;
            $tarefaData['nome'] = Tarefa::DESC_TAREFA_ATIVACAO;
            $tarefaData['status'] = $tarefaInstalacao->status;

            $validator = Validator::make($tarefaData, $tarefaInstalacao->rules, $tarefaInstalacao->messages);

            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }

            $tarefaInstalacao->fill($tarefaData);
            $etapaInstalacao->tarefas()->save($tarefaInstalacao);

            $errorsDatas = $projeto->validarDatasPrevistas();

           
            if(!empty($errorsDatas)){
                DB::rollback();
                return response()->json(
                    array(
                    "messages" => $errorsDatas
                    ), 422); 
            }

            DB::commit();

            $etapa = Etapa::find($tarefaInstalacao->etapa_id);
            return new EtapaResource($etapa);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('AtivacaoController::addTarefAtivacao - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function updateTarefaAtivacao(Request $request, $projetoId){
        DB::beginTransaction();

        try{
            $projeto = ProjetoCnme::find($projetoId);

            if(!isset($projeto)){
                return response()->json(
                    array('message' => 'O projeto não existe.') , 422);
            }
            $etapa =  Etapa::where([
                ['projeto_cnme_id', $projeto->id],
                ['tipo', Etapa::TIPO_ATIVACAO]
                ])->first();
            
            if(!isset($etapa)){
                return response()->json(
                    array('message' => 'Não existe etapa de ativação.') , 422);
            }

            $tarefa =  Tarefa::where([
                ['etapa_id', $etapa->id],
                ])->first();
            
            if(!isset($tarefa)){
                return response()->json(
                    array('message' => 'Não existe a tarefa de ativação.') , 422);
            }

            $tarefaData = $request->all();
            $tarefa->fill($tarefaData);
    
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
            return new TarefaResource($tarefa);
        }catch(\Exception $e){
            DB::rollback();
    
            Log::error('AtivacaoController::updateTarefaAtivacao - message: '. $e->getMessage());
    
            return response()->json(
                    array('message' => $e->getMessage()) , 500);
    
            }

    }

    public function ativar(Request $request, $projetoId){
        DB::beginTransaction();
        try{
            $projeto = ProjetoCnme::find($projetoId);
            if($projeto->status !== ProjetoCnme::STATUS_INSTALADO && $projeto->status !== ProjetoCnme::STATUS_ATIVADO){
                return response()->json(
                    array('message' => 'Não não concluiu a etapa de instalação.') , 422);
            }

            $etapaAtivacao = $projeto->getEtapaAtivacao();
            $tarefaAtivacao = $etapaAtivacao->getFirstTarefa();

            if($request->has('link_externo'))
                $tarefaAtivacao->link_externo = $request['link_externo'];

            if($request->has('numero'))
                $tarefaAtivacao->numero = $request['numero'];

            if($request->has('descricao'))
                $tarefaAtivacao->descricao = $request['descricao'];

            if(!isset($tarefaAtivacao->data_inicio))
                $tarefaAtivacao->data_inicio = ($request->has('data_inicio')) ? $request['data_inicio']: date("Y-m-d");

            $tarefaAtivacao->data_fim = ($request->has('data_fim')) ? $request['data_fim']: date("Y-m-d");
            $tarefaAtivacao->status = Tarefa::STATUS_CONCLUIDA;
            $tarefaAtivacao->save();

            $etapaAtivacao->status = Etapa::STATUS_CONCLUIDA;
            $etapaAtivacao->save();

            $projeto->status = ProjetoCnme::STATUS_ATIVADO;
            $projeto->save();

            $projeto->equipamentoProjetos->each(function($eP, $value){
                $eP->status = EquipamentoProjeto::STATUS_ATIVADO;
                $eP->save();
            });

            DB::commit();

            if($request->notificar)
                $tarefaAtivacao->notificar();

            return new EtapaResource($etapaAtivacao);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('AtivacaoController::ativar - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

}
