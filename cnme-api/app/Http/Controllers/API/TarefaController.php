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
use App\Http\Resources\EquipamentoResource;

class TarefaController extends Controller
{

    private $qTipoEtapa;
   
    public function tarefasPorResponsavel(Request $request, $empresaId){
        $query = Tarefa::where('unidade_responsavel_id',$empresaId);

        if($request->has('etapa')){
            $this->qTipoEtapa = $request->etapa;
            $query->whereHas('etapa', function($query1){
                $query1->where('tipo','=',strtoupper($this->qTipoEtapa));
            });
        }

        if($request->has('status')){
            $query->where('status','=', strtoupper($request->status));
        }


        return TarefaResource::collection( $query->get());

    }

    public function equipamentosDisponiveisEnvio(Request $request, $projetoId){

        $projeto = ProjetoCnme::find($projetoId);

        if(!isset($projeto)){
            return response()->json(
                array('message' => "Projeto não encontrado.") , 422);
        }

        $result = DB::select(
            "select ep.id from equipamento_projetos ep
                where ep.id not in (
                select tep.equipamento_projeto_id from tarefa_equipamento_projeto tep
                    inner join tarefas t on t.id = tep.tarefa_id
                    inner join equipamento_projetos ep1 on ep1.id = tep.equipamento_projeto_id 
                    inner join etapas e on e.id = t.etapa_id
                    where e.tipo = 'ENVIO'
            ) and ep.projeto_cnme_id = ?", [$projetoId]);
        $disponiveisId = array_column($result,'id');
        
        //dd($disponiveisId);
        $ePs = EquipamentoProjeto::whereIn('id', $disponiveisId)->get();
        //dd($ePs);
        return EquipamentoProjetoResource::collection($ePs);

    
    }

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

    public function destroy($tarefaId){
        
        $tarefa = Tarefa::find($tarefaId);

        if(!isset($tarefa)){
            return response()->json(
                array('message' => 'Tarefa não encontrada.') , 404);
        }

        try{
            $etapa = $tarefa->etapa;
            $projeto = $etapa->projetoCnme;
    
            if($projeto->status ===  ProjetoCnme::STATUS_PLANEJAMENTO){
                DB::beginTransaction();
                $tarefa->delete();
    
                if($etapa->tarefas->isEmpty())
                    $etapa->delete();

                DB::commit();
            }else{
                return response()->json(
                    array('message' => "O projeto não se encontra em planejamento. Não pode ter tarefa removida") , 422);
            }
        }catch(\Exception $e){
            DB::rollback();

            throw $e;
        }

        
        

        
        return response(null, 204);
      
    }

}
