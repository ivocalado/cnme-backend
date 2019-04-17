<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SolicitacaoCnme;
use App\Http\Resources\SolicitacaoResource;
use Illuminate\Support\Facades\Validator;
use App\Models\ProjetoCnme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolicitacaoProjetoController extends Controller
{
  
    public function index(Request $request)
    {
        $per_page = $request->per_page ? $request->per_page : 25;
        return SolicitacaoResource::collection(SolicitacaoCnme::paginate( $per_page ));   
    }

   
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $solicitacao = $request->has('id') ? SolicitacaoCnme::find($request->id) : new SolicitacaoCnme();
            $solicitacaoData = $request->all();
    
            $validator = Validator::make($solicitacaoData, $solicitacao->rules, $solicitacao->messages);
    
            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }
    
            $solicitacao->fill($solicitacaoData);
            $solicitacao->save();
            DB::commit();

            return new SolicitacaoResource($solicitacao);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('SolicitacaoProjetoController::store - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    
    public function show($id)
    {
        $solicitacao = SolicitacaoCnme::find($id);
        if(!isset($solicitacao)){
            return response()->json(
                array('message' => 'Solicitação não encontrada.') , 404);
        }

        return new SolicitacaoResource($solicitacao);
    }

   
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
 
            $solicitacao = SolicitacaoCnme::find($id);
            $solicitacaoData = $request->all();
    
            $solicitacao->fill($solicitacaoData);
            $solicitacao->save();
            DB::commit();

            return new SolicitacaoResource($solicitacao);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('SolicitacaoProjetoController::update - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $solicitacao = SolicitacaoCnme::find($id);

            if(isset($solicitacao)){

                $projeto = ProjetoCnme::where('solicitacao_cnme_id',$solicitacao->id)->first();

                if(isset($projeto)){
                    return response()->json(
                        array('message' => 'Operação não pode ser realizada. Solicitação associada a um projeto: '.$projeto) , 422);
                }else{
                    $solicitacao->delete();
                    DB::commit();
                    return response(null,204);
                }

               
            }

            return response()->json(
                array('message' => 'Solicitação não encontrada.') , 404);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('SolicitacaoProjetoController::destroy - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }
}
