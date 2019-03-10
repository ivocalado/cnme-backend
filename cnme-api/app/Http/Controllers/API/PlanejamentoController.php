<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjetoResource;
use App\Models\ProjetoCnme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SolicitacaoCnme;
use Illuminate\Support\Facades\Validator;
use App\Models\Equipamento;
use App\Models\EquipamentoProjeto;
use App\Models\Kit;
use App\Models\Etapa;
use App\Models\Tarefa;
use App\Http\Resources\EquipamentoProjetoResource;
use App\Http\Resources\EtapaResource;
use App\Http\Resources\TarefaResource;

class PlanejamentoController extends Controller
{
    public function addEquipamento(Request $request, $projetoId, $equipamentoId){
        $equipamento = Equipamento::find($equipamentoId);

        $projeto = ProjetoCnme::find($projetoId);

        if($equipamento && $projeto){
            $equipamentoProjeto = new EquipamentoProjeto();
            $equipamentoProjeto->equipamento()->associate($equipamento);
            $equipamentoProjeto->projetoCnme()->associate($projeto);
            $equipamentoProjeto->status = EquipamentoProjeto::STATUS_PLANEJADO;
            $equipamentoProjeto->observacao = $request->observacao;
            $equipamentoProjeto->detalhes = $request->detalhes;
    
            $equipamentoProjeto->save();

            if(!isset($projeto->status))
                $projeto->status = ProjetoCnme::STATUS_PLANEJAMENTO;
            
            $projeto->save();

            return new ProjetoResource($projeto);
        }else{
            return response()->json(
                array('message' => "Referência equipamento/projeto não encontrada") , 422);
        }
    }

    public function addEquipamentoList(Request $request, $projetoId){
        try{
            DB::beginTransaction();
            $projeto = ProjetoCnme::find($projetoId);

            if(!isset($projeto)){
                return response()->json(
                    array('message' => "Referência projeto não encontrada") , 422);
            }
            $ids = $request->ids;
    
            foreach($ids as $equipamentoId){
                $equipamento = Equipamento::find($equipamentoId);
    
                if(!isset($equipamento)){
                    DB::rollback();
                    return response()->json(
                        array('message' => "Referência de equipamento(".$equipamentoId.") não encontrada") , 422);
                }
    
                $equipamentoProjeto = new EquipamentoProjeto();
                $equipamentoProjeto->equipamento()->associate($equipamento);
                $equipamentoProjeto->projetoCnme()->associate($projeto);
                $equipamentoProjeto->status = EquipamentoProjeto::STATUS_PLANEJADO;
               
                $equipamentoProjeto->save();

                if(!isset($projeto->status))
                    $projeto->status = ProjetoCnme::STATUS_PLANEJAMENTO;
                
                $projeto->save();
            }

            DB::commit();
            return new ProjetoResource($projeto);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ProjetoController::addKit - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    public function removeEquipamentoList(Request $request, $projetoId){
        try{
            DB::beginTransaction();
            $projeto = ProjetoCnme::find($projetoId);

            if(!isset($projeto)){
                return response()->json(
                    array('message' => "Referência do projeto não encontrada.") , 422);
            }
            $ids = $request->ids;

            EquipamentoProjeto::
                where('projeto_cnme_id',$projetoId)
                ->whereIn('id', $ids)->delete();
            DB::commit(); 

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ProjetoController::addKit - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    public function removeEquipamento(Request $request,$projetoId, $equipamentoId){
        $equipamentoProjeto = EquipamentoProjeto::where([
            ['projeto_cnme_id', $projetoId],
            ['equipamento_id',  $equipamentoId]
        ])->first();

        if($equipamentoProjeto){
            $equipamentoProjeto->delete();
            return response(null,204);
        }else{
            return response()->json(
                array('message' => "Referência equipamento/projeto não encontrada") , 422);
        }
    }

    public function addKit(Request $request, $projetoId, $kitId){
        
        try{


            DB::beginTransaction();
            $kit = Kit::find($kitId);
            $projeto = ProjetoCnme::find($projetoId);

            if(!isset($projeto) || !isset($kit)){
                return response()->json(
                    array('message' => "Kit/Projeto com referências inválidas utilizadas.") , 422);
            }

            if(isset($projeto->kit)){
                return response()->json(
                    array('message' => "Já existe um kit associado. Remova o anterior antes de associar um novo.") , 422);
            }

            $equipamentos = $kit->equipamentos;

            foreach($equipamentos as $q){
                $equipamentoProjeto = new EquipamentoProjeto();
                $equipamentoProjeto->equipamento()->associate($q);
                $equipamentoProjeto->projetoCnme()->associate($projeto);
                $equipamentoProjeto->status = EquipamentoProjeto::STATUS_PLANEJADO;

                $equipamentoProjeto->save();
            }

            $projeto->kit()->associate($kit);
            if(!isset($projeto->status))
                $projeto->status = ProjetoCnme::STATUS_PLANEJAMENTO;

            $projeto->save();
            DB::commit();

            return new ProjetoResource($projeto);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('ProjetoController::addKit - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
        
    }

    public function removeKit(Request $request, $projetoId, $kitId){

        try{
            DB::beginTransaction();
            $projeto = ProjetoCnme::find($projetoId);

            $kit = Kit::find($kitId);

            $projeto->kit()->dissociate();
            $projeto->save();
            
            $ids = $kit->equipamentos->pluck('id')->all();

            EquipamentoProjeto::
                where('projeto_cnme_id',$projetoId)
                ->whereIn('equipamento_id', $ids)->delete(); 

            if(!isset($kit) || !isset($projeto)){
                return response()->json(
                    array('message' => "Referẽncias de kit/projeto inconsistentes") , 422); 
            }

            $projeto = ProjetoCnme::find($projetoId);
            
            DB::commit();
            return new ProjetoResource($projeto);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('ProjetoController::removeKit - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }

    }
}
