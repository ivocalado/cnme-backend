<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TipoUnidade;
use App\Models\Unidade;
use Illuminate\Support\Facades\Validator;

class TipoUnidadeController extends Controller
{
   
    public function index()
    {
        return response()->json(
            TipoUnidade::paginate(25)
        );
    }

  
    public function store(Request $request)
    {
        $tipoUnidade = new TipoUnidade();
        $tipoUnidadeData = $request->all();

        $validator = Validator::make($tipoUnidadeData, $tipoUnidade->rules, $tipoUnidade->messages);

        if ($validator->fails()) {
            return response()->json(
                array(
                "messages" => $validator->errors()
                ), 422); 
       }

        $tipoUnidade->fill($tipoUnidadeData);
        $tipoUnidade->save();
        
        return response()->json(
            array(
                "data" => $tipoUnidade
            )
        );
    }

   
    public function show($id)
    {
        $tipoUnidade = TipoUnidade::find($id);
        if(!isset($tipoUnidade)){
            return response()->json(
                array('message' => 'Tipo Unidade não encontrada.') , 404);
        }

        return response()->json(
            array(
                "data" => $tipoUnidade
            )
        );
    }

    
    public function update(Request $request, $id)
    {
        $tipoUnidade = TipoUnidade::find($id);
        if(!isset($tipoUnidade)){
            return response()->json(
                array('message' => 'Tipo Unidade não encontrada.') , 404);
        }

        $tipoUnidadeData = $request->all();

        $tipoUnidade->fill($tipoUnidadeData);
        $tipoUnidade->save();

        return response()->json(
            array(
                "data" => $tipoUnidade
            )
        );

    }

    
    public function destroy($id)
    {
        $tipoUnidade = TipoUnidade::find($id);

        if(!isset($tipoUnidade)){
            return response()->json(
                array('message' => 'Tipo Unidade não encontrada.') , 404);
        }

        $ok = Unidade::where('tipo_unidade_id', $id)->get()->isEmpty();

        if($ok){
            $tipoUnidade->delete();
            return response(null, 204);
        }else{
            return response()->json(
                array('message' => 'Tipo Unidade possui unidades relacionadas. Não pode ser removida') , 422);
        }
        
    }
}
