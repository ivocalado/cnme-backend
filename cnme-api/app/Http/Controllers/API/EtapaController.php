<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Etapa;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\EtapaResource;
use App\Models\ProjetoCnme;
use App\User;

class EtapaController extends Controller
{
    
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

        $etapaData = $request->all();

        $validator = Validator::make($etapaData, $etapa->rules, $etapa->messages);

        if ($validator->fails()) {
            return response()->json(
                array(
                "messages" => $validator->errors()
                ), 422); 
        }

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

        //$ok = Unidade::where('tipo_unidade_id', $id)->get()->isEmpty();

        $ok = true;
        if($ok){
            $etapa->delete();
            return response(null, 204);
        }else{
            return response()->json(
                array('message' => 'Etapa não pode ser excluída por ter tarefas relacionadas. Não pode ser removida') , 422);
        }
        
    }
}
