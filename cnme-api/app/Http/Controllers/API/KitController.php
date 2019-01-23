<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Kit;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\KitResource;

class KitController extends Controller
{
   
    public function index()
    {
        return response()->json(
            Kit::paginate(25)
        );
    }

    
    public function store(Request $request)
    {
        $kit = new Kit();
        $kitData = $request->all();

        $validator = Validator::make($kitData, $kit->rules, $kit->messages);

        if ($validator->fails()) {
            return response()->json(
                array(
                "messages" => $validator->errors()
                ), 422); 
       }

        $kit->fill($kitData);
        $kit->save();
        
        return response()->json(
            array(
                "data" => $kit
            )
        );
    }

    
    public function show($id)
    {
        $kit = Kit::find($id);
        if(!isset($kit)){
            return response()->json(
                array('message' => 'Kit não encontrado.') , 404);
        }

        return new KitResource($kit);
    }

    
    public function update(Request $request, $id)
    {
        $kit = Kit::find($id);
        if(!isset($kit)){
            return response()->json(
                array('message' => 'Kit não encontrada.') , 404);
        }

        $kitData = $request->all();

        $kit->fill($kitData);
        $kit->save();

        return response()->json(
            array(
                "data" => $kit
            )
        );
    }

    
    public function destroy($id)
    {
        $kit = Kit::find($id);

        if(!isset($kit)){
            return response()->json(
                array('message' => 'Kit não encontrada.') , 404);
        }

        //$ok = Unidade::where('tipo_unidade_id', $id)->get()->isEmpty();

        $ok = true;

        if($ok){
            $kit->delete();
            return response(null, 204);
        }else{
            return response()->json(
                array('message' => 'Não pode ser removida') , 422);
        }
    }
}
