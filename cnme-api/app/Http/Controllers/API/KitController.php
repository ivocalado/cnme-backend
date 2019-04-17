<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Kit;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\KitResource;
use App\Models\Equipamento;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EquipamentoResource;

class KitController extends Controller
{

    public function removidos(Request $request)
    {
        $per_page = $request->per_page ? $request->per_page : 25;
        return KitResource::collection(Kit::onlyTrashed()->paginate( $per_page ));
    }

    public function all(Request $request)
    {
        $per_page = $request->per_page ? $request->per_page : 25;
        return KitResource::collection(Kit::withTrashed()->paginate( $per_page ));
    }
   
   
    public function index(Request $request)
    {
        $per_page = $request->per_page ? $request->per_page : 25;
        return KitResource::collection(Kit::paginate( $per_page ));
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
        
        return new KitResource($kit);
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

        return new KitResource($kit);
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

    public function forceDelete($id){
        try {
            DB::beginTransaction();

            $kit = Kit::withTrashed()->find($id);

            if(isset($kit)){
                $kit->forceDelete();

                DB::commit();
                return response(null,204);
            }else {
                return response()->json(
                    array('message' => "kit não encontrado.") , 422);
            }
            
        }catch(\Exception $e){
            DB::rollback();

            return response()->json(
                array('message' => "kit não pode ser removido. O kit está envolvido nos processos de implatanção.") , 422);

        }
    }

    public function restore($id){
       
        $kit = Kit::withTrashed()->find($id);

        if($kit){
            $kit->restore();
            return new KitResource($kit);
        }else {
            response()->json(
                array('message' => "Kit não encontrado.") , 404);
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

    public function removeEquipamentoList(Request $request, $kitId){


        $kit = Kit::find($kitId);
        if(!isset($kit)){
            return response()->json(
                array('message' => 'Kit não encontrado.') , 422);
        }

        if(isset($request['ids'])){
            $equipamentoIds = $request['ids'];
            $kit->equipamentos()->detach($equipamentoIds);

            $kit->save();
        }else{
            return response()->json(
                array('message' => 'Defina os equipamentos a serem removidos.') , 422);
        }

        return new KitResource($kit);     
    }

    /**
     * Retorna equipamentos que não fazem parte de um dado kit
     */
    public function diffKit(Request $request, $kitId){
        $kit = Kit::with('equipamentos')->find($kitId);

        if(isset($kit)){
            $equipamentosIds  = $kit->equipamentos->pluck('id');

            return response()->json(
                 EquipamentoResource::collection(Equipamento::whereNotIn('id',$equipamentosIds)->get())
            );
        }else {
            return response()->json(
                array('message' => 'Kit não encontrado.') , 422);
        }
       

    }
}
