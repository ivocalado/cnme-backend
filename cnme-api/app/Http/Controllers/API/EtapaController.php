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

        $projeto = $etapa->projetoCnme;
        $ok = $projeto->status === ProjetoCnme::STATUS_PLANEJAMENTO;

        try{
            
            if($ok){
                DB::beginTransaction();
                $instalacao = $projeto->getEtapaInstalacao();
                $ativacao = $projeto->getEtapaAtivacao();
                
                if($etapa->tipo === Etapa::TIPO_ENVIO){
                    if($instalacao)
                        $instalacao->delete();
    
                    if($ativacao)
                        $ativacao->delete();
                }else if($etapa->tipo === Etapa::TIPO_INSTALACAO){
                    if($ativacao)
                        $ativacao->delete();
                }
    
                $etapa->delete();
                DB::commit();

                return response(null, 204);
    
            }else{
                return response()->json(
                    array('message' => 'Projeto não está em planejamento. Não pode remover a etapas.') , 422);
            }
        }catch(\Exception $e){
            DB::rollback();

            throw $e;
        }

        
        
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
