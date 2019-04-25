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
    protected $unidadeResponsavelId;

    public function status(){
        return ProjetoCnme::status();
    }

    public function index(Request $request)
    {
        $per_page = $request->per_page ? $request->per_page : 25;
        return ProjetoResource::collection(ProjetoCnme::paginate( $per_page ));   
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
            $projeto->status = ProjetoCnme::STATUS_PLANEJAMENTO;
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

    public function cancelar(Request $request, $projetoId){
        $projeto = ProjetoCnme::find($projetoId);

        $projeto->status = ProjetoCnme::STATUS_CANCELADO;
        $projeto->descricao = $request->has('descricao') ? $request->descricao: "::CANCELADO::".$projeto->descricao;
        $projeto->save();
        $projeto->notificar();

        return new ProjetoResource($projeto);
    }

    public function recuperar(Request $request, $projetoId){
        $projeto = ProjetoCnme::find($projetoId);

        if(!isset($projeto)){
            return response()->json(array('message' => "Projeto não encontrado.") , 404);
        }

        if(!$request->has('status') || 
            !ProjetoCnme::checkStatus($request->status) || 
            $request->status === ProjetoCnme::STATUS_CANCELADO){
            
                return response()->json(array('message' => "Status não foi enviado corretamente. Status:(".implode("|",ProjetoCnme::status()).")") , 422);
        }

        if($projeto->status === ProjetoCnme::STATUS_CANCELADO){
            
            $projeto->status = $request->status;
            $projeto->descricao = $request->has('descricao') ? $request->descricao : $projeto->descricao;
            $projeto->save();
            $projeto->recuperar();

        }else
            return response()->json(array('message' => "Projeto não se encontra cancelado.") , 422);

      

        return new ProjetoResource($projeto);
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


        if($request->has('unidade_id')){
            $list->where('unidade_id', $request->unidade_id);
        }

        if($request->has('status')){

            $arrayStatus =  ProjetoCnme::status();

           
            if(is_array($request->status)){
                $arrayStatus = explode(';', $request->status[0]);

                $list = $list->whereIn('status', $arrayStatus);
            }else{
                $status =  strtoupper($request->status);
                if(!in_array($status, $arrayStatus)){
                    return response()->json(
                        array('message' => "Consulta por status desconhecido. Status:(".implode("|",$arrayStatus).")") , 422);
                }
    
                $list = $list->where('status',$status);
            }

            

        }

        if($request->has('q')){
            $this->q = $request->q; 

            // $list->where('descricao','ilike','%'.$request->q.'%')
            // ->orWhereHas('unidade', function ($query) {
            //     $query->where('nome', 'ilike', '%'.$this->q.'%')
            //             ->orWhere('codigo_inep', $this->q);
            // });  

            $list->where(function ($query){
                $query->orWhere('descricao','ilike','%'.$this->q.'%')
                ->orWhereHas('unidade', function ($query2) {
                        $query2->where('nome', 'ilike', '%'.$this->q.'%')
                        ->orWhere('codigo_inep', $this->q);
                    });
                
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

        //dd($list->toSql());
        
        $per_page = $request->per_page ? $request->per_page : 25;
        return ProjetoResource::collection($list->paginate( $per_page ));
    }

    public function andamento(Request $request){
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

        $list->whereIn('status',
                                [ProjetoCnme::STATUS_ENVIADO, 
                                ProjetoCnme::STATUS_ENTREGUE, 
                                ProjetoCnme::STATUS_INSTALADO, ]);
                                
        $per_page = $request->per_page ? $request->per_page : 25;
        return ProjetoResource::collection($list->paginate($per_page));

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
        if($request->has('responsavel_id'))
            $this->unidadeResponsavelId = $request->responsavel_id;

        $list = $list->whereHas('etapas.tarefas', function ($query) {
            $query->where('status', Tarefa::STATUS_ANDAMENTO)
                ->whereNull('data_fim')
                ->where('data_fim_prevista','<=',\DB::raw('NOW()'));

            if(isset($this->unidadeResponsavelId))
                $query->where('unidade_responsavel_id','=',$this->unidadeResponsavelId);
        });

        $per_page = $request->per_page ? $request->per_page : 25;
        return ProjetoResource::collection($list->paginate( $per_page ));
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

    protected $empresaId;
    public function projetosPorResponsavel(Request $request, $empresaId){
        $list = ProjetoCnme::query();

        $this->empresaId = $empresaId;
        $list->whereHas('etapas', function($query1){
            $query1->whereHas('tarefas',function ($query2) {
                $query2->where('unidade_responsavel_id','=',$this->empresaId);
            });
        });

        return ProjetoResource::collection( $list->get());
    }

    public function validar($projetoId){
        $projeto = ProjetoCnme::find($projetoId);
        $messages = $projeto->validate();

        return response()->json(
           
           $messages
        );  

    }

}
