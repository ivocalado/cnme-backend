<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UnidadeResource;
use App\Models\Unidade;
use App\Models\Localidade;
use App\Http\Resources\LocalidadeResource;
use App\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnidadeController extends Controller
{
    public function index()
    {
        return UnidadeResource::collection(Unidade::paginate(25));
    }

    
    public function store(Request $request)
    {

        DB::beginTransaction();

        try {
            $unidade = $request->has('id') ? Unidade::find($request->id) : new Unidade;
            $unidadeData = $request->all();
    
            $validator = Validator::make($unidadeData, $unidade->rules, $unidade->messages);
    
            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }
    
            $unidade->fill($unidadeData);
            $unidade->save();
            DB::commit();

            return new UnidadeResource($unidade);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('UnidadeController::store - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
       
    }

   
    public function show($id)
    {
        $unidade = Unidade::find($id);
        if(!isset($unidade)){
            return response()->json(
                array('message' => 'Unidade não encontrada.') , 404);
        }

        return new UnidadeResource($unidade);
    }

    
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $unidade = Unidade::find($id);
            if(!isset($unidade)){
                return response()->json(
                    array('message' => 'Unidade não encontrada.') , 404);
            }

            $unidadeData = $request->all();

            $validator = Validator::make($unidadeData, $unidade->rules, $unidade->messages);

            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }

            $unidade->fill($unidadeData);
            $unidade->save();
            DB::commit();

            return new UnidadeResource($unidade);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('UnidadeController::update - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    
    public function destroy($id)
    {

        DB::beginTransaction();

        try {

            $unidade = Unidade::find($id);

            if(isset($unidade)){

                $localidade = Localidade::find($unidade->localidade_id);

                if(isset($localidade)){

                    $unidade->localidade()->dissociate();
                    $unidade->save();
                    $localidade->delete();
                }

                $unidade->delete();
                DB::commit();
                return response(null,204);
            }

            return response()->json(
                array('message' => 'Unidade não encontrada.') , 404);

        }catch(\Exception $e){
            DB::rollback();

            Log::error('UnidadeController::destroy - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }

    public function addLocalidade(Request $request, $idUnidade){

        DB::beginTransaction();

        try {
            $unidade = Unidade::find($idUnidade);
            $localidade = new Localidade();
            $localidadeData = $request->all();

            $validator = Validator::make($localidadeData, $localidade->rules, $localidade->messages);
            if ($validator->fails()) {
                return response()->json(
                    array(
                    "messages" => $validator->errors()
                    ), 422); 
            }

            $localidade->fill($localidadeData);
            $localidade->save();

            $unidade->localidade()->associate($localidade);
            $unidade->save();
            DB::commit();

            return new UnidadeResource($unidade);
        }catch(\Exception $e){
            DB::rollback();

            Log::error('UnidadeController::addLocalidade - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
      
    }

    public function updateLocalidade(Request $request, $idUnidade){
        $unidade = Unidade::find($idUnidade);
        $localidade = Localidade::find($unidade->localidade_id);
        $localidadeData = $request->all();

        $validator = Validator::make($localidadeData, $localidade->rules, $localidade->messages);
        if ($validator->fails()) {
            return response()->json(
                array(
                "messages" => $validator->errors()
                ), 422); 
       }

        $localidade->fill($localidadeData);
        $localidade->save();
        

        return new LocalidadeResource($localidade);
    }

    public function usuarios($idUnidade){
     
        return response()->json(
            array(
            "data" => UserResource::collection(User::where('unidade_id', $idUnidade)->paginate(25))
            ), 200);
    }
}
