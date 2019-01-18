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

class UnidadeController extends Controller
{
    public function index()
    {
        return UnidadeResource::collection(Unidade::paginate(5));
    }

    
    public function store(Request $request)
    {

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
        
        return new UnidadeResource($unidade);
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


        return new UnidadeResource($unidade);
    }

    
    public function destroy($id)
    {
        $unidade = Unidade::find($id);

        if(isset($unidade)){

            $localidade = Localidade::find($unidade->localidade_id);

            if(isset($localidade)){

                $unidade->localidade()->dissociate();
                $unidade->save();
                $localidade->delete();
            }

            $unidade->delete();
            return response(null,204);
        }

        return response()->json(
            array('message' => 'Unidade não encontrada.') , 404);
        
    }

    public function addLocalidade(Request $request, $idUnidade){
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

        return new UnidadeResource($unidade);
      
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
