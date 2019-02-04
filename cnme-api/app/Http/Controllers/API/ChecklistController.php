<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChecklistResource;
use App\Models\Checklist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Equipamento;
use App\Models\ItemChecklist;
use Illuminate\Support\Facades\Validator;

class ChecklistController extends Controller
{
    public function index(){
        return ChecklistResource::collection(Checklist::paginate(25));
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

    /**
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * COLD CODE - FUNCIONALIDADES FUTURAS
     */

    public function addItemChecklist(Request $request, $checklistId){
        DB::beginTransaction();
        try {
            $checklist = Checklist::find($checklistId);
            $itemChecklist = new ItemChecklist();

            if($request->has('equipamentoId')){
                $equipamento = Equipamento::find($request->equipamentoId);

                if($equipamento)
                    $itemChecklist->equipamento->associate($equipamento);
                
            }
            $itemtData = $request->all();
            $itemtData["checklist_id"] = $checklistId;
            $validator = Validator::make($itemtData, $itemChecklist->rules, $itemChecklist->messages);
    
            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }

            $itemChecklist->fill($itemtData);
            $itemChecklist->checklist()->associate($checklist);
            
            $itemChecklist->save();
            
            DB::commit();
            
            return new ChecklistResource($checklist);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistController::addItemChecklist - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    /**
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * COLD CODE - FUNCIONALIDADES FUTURAS
     */

    public function removeItemChecklist(Request $request, $checklistId, $itemChecklistId){
        DB::beginTransaction();
        try {
            $checklist = Checklist::find($checklistId);
            $itemChecklist = ItemChecklist::find($itemChecklistId);

            if($checklist->itemChecklists->contains($itemChecklist)){
                
                $itemChecklist->delete();
                DB::commit();

                $checklist = Checklist::find($checklistId);

                return new ChecklistResource($checklist);
            }


        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistController::removeItemChecklist - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }
}
