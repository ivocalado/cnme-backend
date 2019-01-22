<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TipoEquipamento;
use App\Models\Equipamento;
use Illuminate\Support\Facades\Validator;

class TipoEquipamentoController extends Controller
{
    public function index()
    {
        return response()->json(
            TipoEquipamento::paginate(25)
        );
    }

    public function store(Request $request)
    {
        $tipoEquipamento = new TipoEquipamento();
        $tipoEquipamentoData = $request->all();

        $validator = Validator::make($tipoEquipamentoData, $tipoEquipamento->rules, $tipoEquipamento->messages);

        if ($validator->fails()) {
            return response()->json(
                array(
                "messages" => $validator->errors()
                ), 422); 
       }

        $tipoEquipamento->fill($tipoEquipamentoData);
        $tipoEquipamento->save();
        
        return response()->json(
            array(
                "data" => $tipoEquipamento
            )
        );
    }

   
    public function show($id)
    {
        $tipoEquipamento = TipoEquipamento::find($id);
        if(!isset($tipoEquipamento)){
            return response()->json(
                array('message' => 'Tipo de equipamento não encontrado.') , 404);
        }

        return response()->json(
            array(
                "data" => $tipoEquipamento
            )
        );
    }

    public function update(Request $request, $id)
    {
        $tipoEquipamento= TipoEquipamento::find($id);
        if(!isset($tipoEquipamento)){
            return response()->json(
                array('message' => 'Tipo de equipamento não encontrado.') , 404);
        }

        $tipoEquipamentoData = $request->all();

        $validator = Validator::make($tipoEquipamentoData, $tipoEquipamento->rules, $tipoEquipamento->messages);

        if ($validator->fails()) {
            return response()->json(
                array(
                "messages" => $validator->errors()
                ), 422); 
        }

        $tipoEquipamento->fill($tipoEquipamentoData);
        $tipoEquipamento->save();

        return response()->json(
            array(
                "data" => $tipoEquipamento
            )
        );

    }


    public function destroy($id)
    {
        $tipoEquipamento = TipoEquipamento::find($id);
        if(!isset($tipoEquipamento)){
            return response()->json(
                array('message' => 'Tipo de equipamento não encontrado.') , 404);
        }

        $ok = Equipamento::where('tipo_equipamento_id', $id)->get()->isEmpty();

        if($ok){
            $tipoEquipamento->delete();
            return response(null, 204);
        }else{
            return response()->json(
                array('message' => 'Tipo equipamento possui equipamentos relacionados. Não pode ser removido.') , 422);
        }
        
    }


}
