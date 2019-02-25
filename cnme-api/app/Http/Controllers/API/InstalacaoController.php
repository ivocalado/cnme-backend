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
}
