<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ChecklistCnme;
use App\Http\Resources\ChecklistCnmeResource;
use App\Models\ItemChecklistCnme;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Checklist;
use App\Models\ProjetoCnme;

class ChecklistCnmeController extends Controller
{
    public function index(){
        return ChecklistCnme::collection(ChecklistCnme::paginate(25));
    }

    public function store(Request $request){
        DB::beginTransaction();

        try {
            $checklist = new ChecklistCnme();
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


            dd( $checklist);

            // DB::commit();

            // return new ChecklistCnmeResource($checklist);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistCnmeController::store - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function show($id){
        $checklist = ChecklistCnme::find($id);
        if(!isset($checklist)){
            return response()->json(
                array('message' => 'Checklist não encontrado.') , 404);
        }

        return new ChecklistCnmeResource($checklist);
    }

    public function update(Request $request, $id){

        DB::beginTransaction();

        try {

            $checklist = ChecklistCnme::find($id);
            if(!isset($checklist)){
                return response()->json(
                    array('message' => 'Checklist não encontrado.') , 404);
            }

            $checklistData = $request->all();

            $checklist->fill($checklistData);
            $checklist->save();
            DB::commit();

            return new ChecklistCnmeResource($checklist);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistCnmeController::update - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }

    }

    public function destroy($id){
        DB::beginTransaction();
        try {

            $checklist = ChecklistCnme::find($id);

            if(isset($checklist)){
                $checklist->delete();
                DB::commit();
                return response(null,204);
            }

            return response()->json(
                array('message' => 'Checklist não encontrado.') , 404);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistCnmeController::destroy - '.$e->getMessage());

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
     * 
     * 
     * 
     * 
     * COLD CODE - FUNCIONALIDADES FUTURAS
     */
    public function clearAndAddItemsAll(Request $request, $checklistCnmeId){
        DB::beginTransaction();
        try {
            $checklistCnme = ChecklistCnme::find($checklistCnmeId);
            $checklistCnme->itemChecklistCnmes()->delete();
            $checklistModelo = $checklistCnme->checklist;

            foreach($checklistModelo->itemChecklists as $item){
                $itemCnme = new ItemChecklistCnme();
                $itemCnme->status = ItemChecklistCnme::STATUS_PENDENTE;
                $itemCnme->checklistCnme()->associate($checklistCnme);
                $itemCnme->itemChecklist()->associate($item);


                $checklistCnme->itemChecklistCnmes->push($itemCnme);

                $itemCnme->save();
                $checklistCnme->save();
            }
            
            DB::commit();
            
            $checklistCnme = ChecklistCnme::find($checklistCnmeId);

            return new ChecklistCnmeResource($checklistCnme);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistCnmeController::addItemsAll - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

     /**
     * COLD CODE - FUNCIONALIDADES FUTURAS
     */
    public function addItemChecklist(Request $request, $checklistCnmeId){

        DB::beginTransaction();
        try {
            $checklist = ChecklistCnme::find($checklistCnmeId);
            $itemChecklist = new ItemChecklistCnme();

            $itemtData = $request->all();
            $itemtData["checklist_cnme_id"] = $checklistCnmeId;
            $validator = Validator::make($itemtData, $itemChecklist->rules, $itemChecklist->messages);
    
            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }


            dd($itemtData);

            $itemChecklist->fill($itemtData);
            $itemChecklist->checklistCnme()->associate($checklist);
            
            $itemChecklist->save();
            
            DB::commit();
            
            return new ChecklistCnmeResource($checklist);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistCnmeController::addItemChecklist - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }

    }

     /**
     * COLD CODE - FUNCIONALIDADES FUTURAS
     */
    public function removeItemChecklist(Request $request, $checklistCnmeId, $itemChecklistCnmeId){
        DB::beginTransaction();
        try {
            $checklist = ChecklistCnme::find($checklistCnmeId);
            $itemChecklist = ItemChecklistCnme::find($itemChecklistCnmeId);

            if($checklist->itemChecklistCnmes->contains($itemChecklist)){
                
                $itemChecklist->delete();
                DB::commit();

                $checklist = ChecklistCnme::find($checklistCnmeId);

                return new ChecklistCnmeResource($checklist);
            }


        }catch(\Exception $e){
            DB::rollback();

            Log::error('ChecklistCnmeController::removeItemChecklist - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }
}
