<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChecklistResource;
use App\Models\Checklist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Equipamento;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjetoCnme;
use App\Http\Resources\ProjetoResource;

class ChecklistController extends Controller
{
    public function index(Request $request){
        $per_page = $request->per_page ? $request->per_page : 25;
        return ChecklistResource::collection(Checklist::paginate( $per_page ));
    }

    public function store(Request $request){
        DB::beginTransaction();

        try {
            $checklist = new Checklist();
            $checklistData = $request->all();
    
            $validator = Validator::make($checklistData, $checklist->rules, $checklist->messages);
    
            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }

            
            $checklist->fill($checklistData);
            $checklist->usuario()->associate(Auth::user());
            $checklist->save();
            DB::commit();

            return new ChecklistResource($checklist);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistController::store - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function show($id){
        $checklist = Checklist::find($id);
        if(!isset($checklist)){
            return response()->json(
                array('message' => 'Checklist não encontrado.') , 404);
        }

        return new ChecklistResource($checklist);
    }

    public function update(Request $request, $id){
        DB::beginTransaction();

        try {

            $checklist = Checklist::find($id);
            if(!isset($checklist)){
                return response()->json(
                    array('message' => 'Checklist não encontrado.') , 404);
            }

            $checklistData = $request->all();

            $checklist->fill($checklistData);
            $checklist->save();
            DB::commit();

            return new ChecklistResource($checklist);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistController::update - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    public function destroy($id){
        DB::beginTransaction();
        try {

            $checklist = Checklist::find($id);

            if(isset($checklist)){
                $checklist->delete();
                DB::commit();
                return response(null,204);
            }

            return response()->json(
                array('message' => 'Checklist não encontrado.') , 404);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistController::destroy - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function checklistProjeto(Request $request, $checklistId, $projetoId){
        $checklist = Checklist::find($checklistId);
        $projeto = ProjetoCnme::find($projetoId);

        if($checklist && $projeto){
            $projeto->checklist()->associate($checklist);
            $projeto->checklist_at = date("Y-m-d H:i:s");
            $projeto->usuarioChecklist()->associate(Auth::user());
            $projeto->save();

            return new ProjetoResource( $projeto );
        }else{
            return response()->json(
                array('message' => 'Projeto/Checklist não encontrados.') , 404);
        }



    }

    public function forceDelete($id){
        DB::beginTransaction();
        try {

            $checklist = Checklist::find($id);

            if(isset($checklist)){
                if((!$checklist->projetoCnmes || $checklist->projetoCnmes->isEmpty())){
                    $checklist->forceDelete();
                    DB::commit();
                    return response(null,204);
                }else{
                    return response()->json(array('message' => 'Checklist não pode ser removido pois já está associado a projetos.') , 422);
                }
                
            }else
                return response()->json(array('message' => 'Checklist não encontrado.') , 404);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistController::destroy - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

}
