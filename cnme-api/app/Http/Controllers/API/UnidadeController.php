<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UnidadeResource;
use App\Models\Unidade;

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
            $unidade->delete();
            return '204';
        }

        return response('Unidade não encontrada.', 404);
        
    }
}
