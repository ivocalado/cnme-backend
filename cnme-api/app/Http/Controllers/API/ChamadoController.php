<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChamadoResource;
use App\Models\Chamado;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;

class ChamadoController extends Controller
{
    public function index(){
        return ChamadoResource::collection(Chamado::paginate(25));  
    }


    public function show($id){
        $chamado = Chamado::find($id);
        if(!isset($chamado)){
            return response()->json(
                array('message' => 'Chamado não encontrado.') , 404);
        }

        return new ChamadoResource($chamado);
    }

    public function update(Request $request, $id){
        $chamado = Chamado::find($id);

        if(!isset($chamado)){
            return response()->json(
                array('message' => 'Chamado não encontrado.') , 404);
        }

        $chamadaData = $request->all();
        
        $chamado->fill($chamadaData);
        
        $chamado->save();

        return new ChamadoResource($chamado);

    }

    public function store(Request $request){
        $chamado = new Chamado();
        $chamadoData = $request->all();

        $validator = Validator::make($chamadoData, $chamado->rules, $chamado->messages);

        if ($validator->fails()) {
            return response()->json(
                array(
                "messages" => $validator->errors()
                ), 422); 
        }

        $chamado->fill($chamadoData);
        $chamado->usuario()->associate(Auth::user());
        $chamado->save();
        
        return new ChamadoResource($chamado);

    }

    public function destroy($id){
        $chamado = Chamado::find($id);

        if(!isset($chamado)){
            return response()->json(
                array('message' => 'Chamado não encontrado.') , 404);
        }

        $chamado->delete();
        return response(null, 204); 
    }

    public function addComment(Request $request, $id){
        
        $chamado = Chamado::find($id);

        if($chamado && $request->content){
            $userId = Auth::user()->id;
            
            $comment = new Comment();
            $comment->usuario_id = $userId;
            
            $comment->content = $request->content;
            $comment->comment()->associate($chamado);

            $comment->save();

            return response()->json(
                array(
                    "data" => $comment
                )
            ); 

        }else{
            return response()->json(
                array('message' => 'Dados incompletos') , 422);
        
        }

    }
}
