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

class ProjetoController extends Controller
{
    

    protected $q;

    public function status(){
        return ProjetoCnme::status();
    }

    public function index()
    {
        return ProjetoResource::collection(ProjetoCnme::paginate(25));   
    }

   
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
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
        DB::beginTransaction();

        try {
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
        DB::beginTransaction();

        try {
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

    public function addKit(Request $request, $projetoId, $kitId){
        
        try{
            $kit = Kit::find($kitId);
            $equipamentos = $kit->equipamentos;
            $projeto = ProjetoCnme::find($projetoId);

            if(isset($projeto->kit)){
                return response()->json(
                    array('message' => "Já existe um kit associado. Remova o anterior antes de associar um novo.") , 422);
            }

            foreach($equipamentos as $q){
                $equipamentoProjeto = new EquipamentoProjeto();
                $equipamentoProjeto->equipamento()->associate($q);
                $equipamentoProjeto->projetoCnme()->associate($projeto);
                $equipamentoProjeto->status = EquipamentoProjeto::STATUS_PROJETO;

                $equipamentoProjeto->save();
            }

            $projeto->kit()->associate($kit);
            $projeto->save();
            
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
                    array('message' => "Referẽncias de kit/projeto inconsistentes") , 500); 
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

    public function addEquipamento(Request $request, $projetoId, $equipamentoId){
        $equipamento = Equipamento::find($equipamentoId);

        $projeto = ProjetoCnme::find($projetoId);

        if($equipamento && $projeto){
            $equipamentoProjeto = new EquipamentoProjeto();
            $equipamentoProjeto->equipamento()->associate($equipamento);
            $equipamentoProjeto->projetoCnme()->associate($projeto);
            $equipamentoProjeto->status = EquipamentoProjeto::STATUS_PROJETO;
            $equipamentoProjeto->observacao = $request->observacao;
            $equipamentoProjeto->detalhes = $request->detalhes;
    
            $equipamentoProjeto->save();

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
                $equipamentoProjeto->status = EquipamentoProjeto::STATUS_PROJETO;
               
        
                $equipamentoProjeto->save();
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

    public function search(Request $request){
        $list = ProjetoCnme::query();
        if($request->has('status')){
            $list = $list->orWhere('status',$request->status);
        }

        if($request->has('q')){
            $this->q = $request->q; 

            $list = $list->orWhere('descricao','ilike','%'.$request->q.'%');

            $list =  $list->orWhereHas('unidade', function ($query) {
                $query->orWhere('nome', 'ilike', '%'.$this->q.'%');
                $query->orWhere('codigo_inep', $this->q);
            });

           
        }

        return ProjetoResource::collection($list->paginate(25));
    }

    public function atrasados(){
        $list = ProjetoCnme::query();
        $list = $list->orWhereHas('etapas', function ($query) {
            $query->where('status',Etapa::STATUS_EXECUCAO)
                    ->whereNull('data_fim')
                    ->where('data_fim_prevista','<=',\DB::raw('NOW()'));
        });

        $list = $list->orWhereHas('etapas.tarefas', function ($query) {
            
            $query->where('status', Tarefa::STATUS_EXECUCAO)
                ->whereNull('data_fim')
                ->where('data_fim_prevista','<=',\DB::raw('NOW()'));

        });


        return ProjetoResource::collection($list->paginate(25));
    }
}
