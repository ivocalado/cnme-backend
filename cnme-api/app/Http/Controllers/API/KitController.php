<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Kit;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\KitResource;
use App\Models\Equipamento;
use Illuminate\Support\Facades\DB;

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

    public function addEquipamento(Request $request, $kitId, $equipamentoId){


        $kit = Kit::find($kitId);
        $equipamento = Equipamento::find($equipamentoId);

        if($kit && $equipamento){
            DB::beginTransaction();
            if(!$kit->equipamentos->contains($equipamento)){
                $kit->equipamentos()->attach($equipamento);
                DB::commit();


                $kit = Kit::find($kitId);
                return new KitResource($kit);
            }else{
                return response()->json(
                    array('message' => 'Equipamento já está inserido no kit') , 422);
            }
            
        }else{
            return response()->json(
                array('message' => 'Referencias incorretas') , 422);
        }

       
    }

    public function addEquipamentoList(Request $request, $kitId){
        $kit = Kit::find($kitId);

        if(!isset($kit)){
            return response()->json(
                array('message' => "Referência de kit(".$kitId.") não encontrada") , 422);
        }
        if(!$request->has('ids')){
            return response()->json(
                array('message' => "Campo ids é obrigatório") , 422);
        }
        $ids = $request->ids;
        $kit->equipamentos()->attach($ids);

        $kit->save();
        
        return new KitResource($kit);


    }

    public function removeEquipamento(Request $request, $kitId, $equipamentoId){


        $kit = Kit::find($kitId);
        $equipamento = Equipamento::find($equipamentoId);

        DB::beginTransaction();
        if($kit->equipamentos->contains($equipamento)){
            $kit->equipamentos()->detach($equipamento);
            DB::commit();
            $kit = Kit::find($kitId);
            return new KitResource($kit);
        }else{
            return response()->json(
                array('message' => 'Equipamento não está inserido no kit') , 422);
        }
    }
}
