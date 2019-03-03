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

class ProjetoController extends Controller
{
    

    protected $q;
    protected $uf;
    protected $etapa;

    public function status(){
        return ProjetoCnme::status();
    }

    public function index()
    {
        return ProjetoResource::collection(ProjetoCnme::paginate(25));   
    }

   
    public function store(Request $request)
    {

        try {
            DB::beginTransaction();

            $projeto = $request->has('id') ? ProjetoCnme::find($request->id) : new ProjetoCnme();
            $projetoData = $request->all();
    
            $validator = Validator::make($projetoData, $projeto->rules, $projeto->messages);
    
            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }
    
            $projeto->fill($projetoData);
            $projeto->status = ProjetoCnme::STATUS_CRIADO;
            $projeto->save();
            DB::commit();

            return new ProjetoResource($projeto);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ProjetoController::store - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    
    public function show($id)
    {
        $projeto = ProjetoCnme::find($id);
        if(!isset($projeto)){
            return response()->json(
                array('message' => 'Projeto não encontrado.') , 404);
        }

        return new ProjetoResource($projeto);
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $projeto = ProjetoCnme::find($id);
            $projetoData = $request->all();
    

            $projeto->fill($projetoData);
            $projeto->save();
            DB::commit();

            return new ProjetoResource($projeto);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ProjetoController::update - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }
    
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $projeto = ProjetoCnme::find($id);

            if(isset($projeto->solicitacao_cnme_id)){
                $solicitacao = SolicitacaoCnme::find($projeto->solicitacao_cnme_id);
                $solicitacao->status = SolicitacaoCnme::STATUS_CANCELADA;
                $solicitacao->save();
            }

            $projeto->delete();
            DB::commit();

            return response(null,204);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('ProjetoController::destroy - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function etapas(Request $request, $projetoId){
        $projeto = ProjetoCnme::find($projetoId);

        return EtapaResource::collection($projeto->etapas);
    }

    public function tarefas(Request $request, $projetoId){
        $projeto = ProjetoCnme::find($projetoId);

        if(!isset($projeto)){
            return response()->json(
                array('message' => "Projeto não encontrado.") , 422);
        }

        return TarefaResource::collection($projeto->tarefas);


    }

    public function equipamentosPorStatus(Request $request, $projetoId, $status){
        
        $status =  strtoupper($status);

        $arrayStatus =  EquipamentoProjeto::status();

        if(!in_array($status, $arrayStatus)){
            return response()->json(
                array('message' => "Consulta por status desconhecido. Status:(".implode("|",$arrayStatus).")") , 422);
        }

        $projeto = ProjetoCnme::find($projetoId);
        if(!isset($projeto)){
            return response()->json(
                array('message' => "Referência de projeto inválida.") , 422);
        }

        $lista = EquipamentoProjeto::where([
            ['projeto_cnme_id', $projetoId],
            ['status', $status]
        ])->get();

        return EquipamentoProjetoResource::collection($lista);  
        
    }

    public function search(Request $request){
        $list = ProjetoCnme::query();
        if($request->has('status')){

            $status =  strtoupper($request->status);

            $arrayStatus =  ProjetoCnme::status();

            if(!in_array($status, $arrayStatus)){
                return response()->json(
                    array('message' => "Consulta por status desconhecido. Status:(".implode("|",$arrayStatus).")") , 422);
            }

            $list = $list->where('status',$status);

        }

        if($request->has('q')){
            $this->q = $request->q; 

            $list->where('descricao','ilike','%'.$request->q.'%')
            ->orWhereHas('unidade', function ($query) {
                $query->where('nome', 'ilike', '%'.$this->q.'%')
                        ->orWhere('codigo_inep', $this->q);
            });  
        }

        if($request->has('uf')){
            $this->uf = $request->uf;
            $list->whereHas('unidade', function($query1){
                $query1->whereHas('localidade',function ($query2) {
                    $query2->whereHas('estado', function ($query3){
                        $query3->where('sigla','=',$this->uf);
                    });
                });
            });

        }

        return ProjetoResource::collection($list->paginate(25));
    }

    public function atrasados(Request $request){
        $list = ProjetoCnme::query();

        $list->whereNotNull('data_inicio');
        $list->whereNull('data_fim');

        if($request->has('uf')){
            $this->uf = $request->uf;
            $list->whereHas('unidade', function($query1){
                $query1->whereHas('localidade',function ($query2) {
                    $query2->whereHas('estado', function ($query3){
                        $query3->where('sigla','=',$this->uf);
                    });
                });
            });
        }

        if($request->has('etapa')){
            $this->etapa = $request->etapa;

            $list->whereHas('etapas', function($query1){
                $query1->where('tipo','=', strtoupper($this->etapa));
            });
        }

        $list = $list->whereHas('etapas.tarefas', function ($query) {
            $query->where('status', Tarefa::STATUS_ANDAMENTO)
                ->whereNull('data_fim')
                ->where('data_fim_prevista','<=',\DB::raw('NOW()'));
        });


        return ProjetoResource::collection($list->paginate(25));
    }

    public function getEtapaEnvio($projetoId){
        $projeto = ProjetoCnme::find($projetoId);
        if($projeto){
            $etapaEnvio = $projeto->getEtapaEnvio();
            if($etapaEnvio){
                return new EtapaResource($etapaEnvio);
            }else{
                return response()->json(
                    array('message' => 'Não há etapa de envio nesse projeto.') , 404);
            }
        }else{
            return response()->json(
                array('message' => 'Projeto não encontrado.') , 404);
        }        
    }

    public function getEtapaAtivacao($projetoId){
        $projeto = ProjetoCnme::find($projetoId);
        if($projeto){
            $etapaAtivacao = $projeto->getEtapaAtivacao();
            if($etapaAtivacao){
                return new EtapaResource($etapaAtivacao);
            }else{
                return response()->json(
                    array('message' => 'Não há etapa de ativação nesse projeto.') , 404);
            }
        }else{
            return response()->json(
                array('message' => 'Projeto não encontrado.') , 404);
        }        
    }

    public function getEtapaInstalacao($projetoId){
        $projeto = ProjetoCnme::find($projetoId);
        if($projeto){
            $etapa = $projeto->getEtapaInstalacao();
            if($etapa){
                return new EtapaResource($etapa);
            }else{
                return response()->json(
                    array('message' => 'Não há etapa de instalação nesse projeto.') , 404);
            }
        }else{
            return response()->json(
                array('message' => 'Projeto não encontrado.') , 404);
        }        
    }



    public function getEtapasPorTipo($projetoId, $tipo){
        $projeto = ProjetoCnme::find($projetoId);

        if(!Etapa::checkTipo($tipo)){
            return response()->json(
                array('message' => "Tipo desconhecido. Tipos:(".implode("|",Etapa::tipos()).")") , 422);
        }
        
        if($projeto){
            $etapas = $projeto->getEtapasPorTipo($tipo);
            
            if($etapas){
                return EtapaResource::collection($etapas);
            }else{
                return response()->json(
                    array('message' => 'Não há etapas nesse projeto.') , 404);
            }
        }else{
            return response()->json(
                array('message' => 'Projeto não encontrado.') , 404);
        }        
    }
}
