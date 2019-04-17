<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EquipamentoResource;
use App\Models\Equipamento;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Kit;

class EquipamentoController extends Controller
{

    protected $q;

    public function removidos(Request $request)
    {
        $per_page = $request->per_page ? $request->per_page : 25;
        return EquipamentoResource::collection(Equipamento::onlyTrashed()->paginate($per_page));
    }

    public function all()
    {
        return EquipamentoResource::collection(Equipamento::withTrashed()->get());
    }
   
    public function index(Request $request)
    {
        $per_page = $request->per_page ? $request->per_page : 25;
        return EquipamentoResource::collection(Equipamento::paginate($per_page));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $equipamento = new Equipamento();
            $equipamentoData = $request->all();
    
            $validator = Validator::make($equipamentoData, $equipamento->rules, $equipamento->messages);
    
            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }

            
            $equipamento->fill($equipamentoData);
            $equipamento->save();
            DB::commit();

            return new EquipamentoResource($equipamento);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('EquipamentoController::store - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function show($id)
    {
        $equipamento = Equipamento::find($id);
        if(!isset($equipamento)){
            return response()->json(
                array('message' => 'Equipamento não encontrado.') , 404);
        }

        return new EquipamentoResource($equipamento);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $equipamento = Equipamento::find($id);
            if(!isset($equipamento)){
                return response()->json(
                    array('message' => 'Equipamento não encontrado.') , 404);
            }

            $equipamentoData = $request->all();

            $equipamento->fill($equipamentoData);
            $equipamento->save();
            DB::commit();

            return new EquipamentoResource($equipamento);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('EquipamentoController::update - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $equipamento = Equipamento::find($id);

            if(isset($equipamento)){
                $equipamento->delete();
                DB::commit();
                return response(null,204);
            }else{
                return response()->json(
                    array('message' => 'Equipamento não encontrado.') , 404);
            }

        }catch(\Exception $e){
            DB::rollback();

            Log::error('EquipamentoController::destroy - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function forceDelete($id){
        try {
            DB::beginTransaction();

            $equipamento = Equipamento::withTrashed()->find($id);

            if(isset($equipamento)){
                $equipamento->forceDelete();

                DB::commit();
                return response(null,204);
            }else {
                return response()->json(
                    array('message' => "Equipamento não encontrado.") , 422);
            }
            
        }catch(\Exception $e){
            DB::rollback();

            return response()->json(
                array('message' => "Equipamento não pode ser removido. O equipamento está envolvido nos processos de implatanção.") , 422);

        }
    }

    public function restore($id){
       
        $equipamento = Equipamento::withTrashed()->find($id);

        if($equipamento){
            $equipamento->restore();
            return new EquipamentoResource($equipamento);
        }else {
            response()->json(
                array('message' => "Equipamento não encontrado.") , 404);
        }
    }

    public function search(Request $request){
        $list = Equipamento::query();
        $list->with('tipoEquipamento');
        if($request->has('q')){       
            $list->orWhere("nome", 'ILIKE', '%'.$request->q.'%');
        }

        if($request->has('tipo')){
            
            $this->q = $request->tipo;
            return $list->orWhereHas('tipoEquipamento', function($query) {
                $query->where('nome', $this->q);
            })->get();
            
            
        }
        $per_page = $request->per_page ? $request->per_page : 25;
        return EquipamentoResource::collection($list->orderBy('nome')->paginate($per_page));
    }

}
