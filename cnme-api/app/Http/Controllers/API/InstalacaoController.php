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
use App\Http\Resources\ProjetoResource;

class InstalacaoController extends Controller
{
    public function addTarefaInstalacao(Request $request, $projetoId){
        DB::beginTransaction();

        try{
            $projeto = ProjetoCnme::find($projetoId);

            if(!isset($projeto)){
                return response()->json(
                    array('message' => 'O projeto não existe.') , 422);
            }
            
            $etapaInstalacao = $projeto->firstOrCreateEtapa(Etapa::TIPO_INSTALACAO);
            $tarefaInstalacao = $etapaInstalacao->firstOrCreateTarefa();

            $tarefaData = $request->all();
            $tarefaData['etapa_id'] = $etapaInstalacao->id;
            $tarefaData['nome'] = Tarefa::DESC_TAREFA_INSTALACAO;
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

            Log::error('InstalacaoController::addTarefaInstalacao - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function updateTarefaInstalacao(Request $request, $projetoId){
        DB::beginTransaction();

        try{
            $projeto = ProjetoCnme::find($projetoId);

            if(!isset($projeto)){
                return response()->json(
                    array('message' => 'O projeto não existe.') , 422);
            }
            $etapaInstalacao =  Etapa::where([
                ['projeto_cnme_id', $projeto->id],
                ['tipo', Etapa::TIPO_INSTALACAO]
                ])->first();
            
            if(!isset($etapaInstalacao)){
                return response()->json(
                    array('message' => 'Não existe etapa de Instalação.') , 422);
            }

            $tarefa =  Tarefa::where([
                ['etapa_id', $etapaInstalacao->id],
                ])->first();
            
            if(!isset($tarefa)){
                return response()->json(
                    array('message' => 'Não existe a tarefa de Instalação.') , 422);
            }

            $tarefaData = $request->all();
            $tarefa->fill($tarefaData);
    
            $tarefa->save();

            //return EtapaResource::collection($projeto->etapas);

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
    
            Log::error('InstalacaoController::updateTarefaAtivacao - message: '. $e->getMessage());
    
            return response()->json(
                    array('message' => $e->getMessage()) , 500);
    
            }

    }
}
