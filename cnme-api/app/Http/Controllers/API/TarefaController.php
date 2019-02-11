<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProjetoCnme;
use Illuminate\Support\Facades\DB;
use App\Models\Tarefa;
use Illuminate\Support\Facades\Log;
use App\Models\EquipamentoProjeto;
use App\Http\Resources\TarefaResource;
use App\Http\Resources\EquipamentoProjetoResource;

class TarefaController extends Controller
{
   
    public function addEquipamentosAll(Request $request, $projetoId, $tarefaId)
    {

        try{
            DB::beginTransaction();
        
            $projeto = ProjetoCnme::find($projetoId);
            $tarefa = Tarefa::find($tarefaId);
    
            $equipamentoProjetos =  $projeto->equipamentoProjetos;
            
            foreach ($equipamentoProjetos as $ep) {
                $tarefa->equipamentosProjetos()->attach($ep);
            }
    
            $tarefa->save();
            DB::commit();

            return new TarefaResource($tarefa);
    
        }catch(\Exception $e){
            DB::rollback();

            Log::error('TarefaController::addKitAll - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    public function addEquipamentoProjeto(Request $request, $projetoId, $tarefaId, $equipamentoProjetoId)
    {

        try{
            DB::beginTransaction();
        
            $projeto = ProjetoCnme::find($projetoId);
            $tarefa = Tarefa::find($tarefaId);
            $equipamentoProjeto = EquipamentoProjeto::find($equipamentoProjetoId);

            if(!isset($projeto) || !isset($tarefa) || !isset($equipamentoProjeto)){
                return response()->json(
                    array('message' => "Referências inválidas.") , 422);
            }

            if($equipamentoProjeto->projetoCnme->id != $projetoId){
                return response()->json(
                    array('message' => "Equipamento não faz parte do projeto. Adicione ao projeto 
                    antes de realizar qualquer tarefa") , 422);
            }
    
            $tarefa->equipamentosProjetos()->attach($equipamentoProjeto);
            
            $tarefa->save();
            DB::commit();

            return new TarefaResource($tarefa);
    
        }catch(\Exception $e){
            DB::rollback();

            Log::error('TarefaController::addKitAll - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    public function removeEquipamentoProjeto(Request $request, $projetoId, $tarefaId, $equipamentoProjetoId)
    {

        try{
            DB::beginTransaction();
        
            $projeto = ProjetoCnme::find($projetoId);
            $tarefa = Tarefa::find($tarefaId);
            $equipamentoProjeto = EquipamentoProjeto::find($equipamentoProjetoId);

            if(!isset($projeto) || !isset($tarefa) || !isset($equipamentoProjeto)){
                return response()->json(
                    array('message' => "Referências inválidas.") , 422);
            }

            if($equipamentoProjeto->projetoCnme->id != $projetoId){
                return response()->json(
                    array('message' => "Equipamento não faz parte do projeto.") , 422);
            }
    
            $tarefa->equipamentosProjetos()->detach($equipamentoProjeto->id);
            
            $tarefa->save();
            DB::commit();

            return new TarefaResource($tarefa);
    
        }catch(\Exception $e){
            DB::rollback();

            Log::error('TarefaController::addKitAll - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    public function clearEquipamentoProjeto(Request $request, $projetoId, $tarefaId){
        try{
            DB::beginTransaction();
        
            $projeto = ProjetoCnme::find($projetoId);
            $tarefa = Tarefa::find($tarefaId);

            if(!isset($projeto) || !isset($tarefa)){
                return response()->json(
                    array('message' => "Referências inválidas.") , 422);
            }
    
            $tarefa->equipamentosProjetos()->detach();
            
            $tarefa->save();
            DB::commit();

            return new TarefaResource($tarefa);
    
        }catch(\Exception $e){
            DB::rollback();

            Log::error('TarefaController::addKitAll - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    public function syncEquipamentosProjeto(Request $request, $projetoId, $tarefaId){
        try{
            DB::beginTransaction();
        
            $projeto = ProjetoCnme::find($projetoId);
            $tarefa = Tarefa::find($tarefaId);

            if(!isset($projeto) || !isset($tarefa)){
                return response()->json(
                    array('message' => "Referências inválidas.") , 422);
            }
    
            if($request->has('ids')){
                $tarefa->equipamentosProjetos()->attach($request->ids);
            }else{
                return response()->json(
                    array('message' => "Parâmetros(ids) não enviados.") , 422);
            }
            
            $tarefa->save();
            DB::commit();

            return new TarefaResource($tarefa);
    
        }catch(\Exception $e){
            DB::rollback();

            Log::error('TarefaController::addKitAll - message: '. $e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

}
