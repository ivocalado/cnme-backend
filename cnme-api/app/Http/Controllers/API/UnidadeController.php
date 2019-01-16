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

class UnidadeController extends Controller
{
   
    

    public function index()
    {
        return UnidadeResource::collection(Unidade::paginate(25));
    }

    
    public function store(Request $request)
    {

        $unidade = $request->has('id') ? Unidade::findOrFail($request->id) : new Unidade;

        $unidadeData = $request->all();
        $unidade->fill($unidadeData);
		$unidade->save();
        
        return new UnidadeResource($unidade);
    }

   
    public function show($id)
    {
        return new UnidadeResource(Unidade::find($id));
    }

    
    public function update(Request $request, $id)
    {
        $unidade = Unidade::findOrFail($id);
        $unidadeData = $request->all();
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
                $localidade->delete();
            }

            $unidade->delete();
            return response(null,204);
        }

        return response('Unidade não encontrada.', 404);
        
    }

    public function addLocalidade(Request $request, $idUnidade){
        $unidade = Unidade::find($idUnidade);
        $localidade = new Localidade();
        $localidadeData = $request->all();

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

        $localidade->fill($localidadeData);
        $localidade->save();

        return new LocalidadeResource($localidade);
    }

    public function usuarios($idUnidade){
        
        return UserResource::collection(User::where('unidade_id', $idUnidade)->paginate(25));
    }
}
