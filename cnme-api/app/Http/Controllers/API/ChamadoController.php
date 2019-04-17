<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChamadoResource;
use App\Models\Chamado;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;
use App\Services\MailSender;
use App\Models\StatusChamado;
use App\Http\Resources\StatusChamadoResource;
use App\Http\Resources\TipoChamadoResource;
use App\Models\TipoChamado;
use App\Services\UnidadeService;

class ChamadoController extends Controller
{

    protected $unidadeService;

    function __construct() {
        $this->unidadeService = new UnidadeService();
    }

    public function status(){
        return StatusChamadoResource::collection(StatusChamado::all());
    }

    public function tipos(){
        return TipoChamadoResource::collection(TipoChamado::all());
    }

    public function index(Request $request){
        $per_page = $request->per_page ? $request->per_page : 25;
        return ChamadoResource::collection(Chamado::paginate($per_page));  
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
        if(!$request->has('unidade_responsavel_id')){
            $tvEscola = $this->unidadeService->tvescola();
            $chamado->unidadeResponsavel()->associate($tvEscola);
        }

        $chamado->fill($chamadoData);

        if($chamado->usuarioResponsavel){
            if($chamado->usuarioResponsavel->unidade_id != $chamado->unidadeResponsavel->id){
                return response()->json(
                    array(
                        "messages" => "O usuário responsável(".$chamado->usuarioResponsavel->name.") não  está associado a unidade ".$chamado->unidadeResponsavel->nome
                    ), 422); 
            }
        }else{
            $chamado->usuarioResponsavel()->associate($chamado->unidadeResponsavel->usuarioChamados);
        }

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

    public function notificar(Request $request, $chamadoId){
        $chamado = Chamado::find($chamadoId);
        if(!isset($chamado)){
            return response()->json(
                array('message' => 'Chamado não encontrado.') , 404);
        }

        MailSender::notificarChamadoCriado($chamado);
        $chamado->notificado_at = date('Y-m-d H:i:s');
        $chamado->save();
    }

    public function notificarComment(Request $request, $chamadoId, $commentId){
        $chamado = Chamado::find($chamadoId);

        if(!isset($chamado)){
            return response()->json(
                array('message' => 'Chamado não encontrado.') , 404);
        }

        $comment = Comment::find($commentId);

        if(!isset($comment) || $comment->comment_id != $chamadoId){
            return response()->json(
                array('message' => 'Comentário não encontrado.') , 404);
        }
        MailSender::notificarChamadoAtualizado($chamado, $comment);
        $chamado->notificado_at = date('Y-m-d H:i:s');
        $chamado->save();
    }
}
