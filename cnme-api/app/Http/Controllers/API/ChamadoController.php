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
use App\Models\Unidade;
use App\Jobs\SendEmailChamado;

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

        /**Alteração de responsavel */
        if(array_key_exists('unidade_responsavel_id',$chamadaData) && 
            $chamadaData['unidade_responsavel_id'] != $chamado->unidade_responsavel_id &&
            !array_key_exists('usuario_responsavel_id', $chamadaData)){
            
            $novaUnidadeResponsavel = Unidade::find($chamadaData['unidade_responsavel_id']);
            $chamadaData['usuario_responsavel_id'] = $novaUnidadeResponsavel->usuario_chamados_id;
        }
        
        $chamado->fill($chamadaData);

        if($chamado->unidade_responsavel_id != $chamado->usuarioResponsavel->unidade_id){
            return response()->json(
                array('message' => 'Usuário responsável pelo chamado não pertence a unidade responsável.') , 422);
        }
        
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

        SendEmailChamado::dispatch($chamado)
            ->delay(now()->addMinutes(1));
        
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

            SendEmailChamado::dispatch($chamado, $comment)
                ->delay(now()->addMinutes(1));

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
       
       
    }

    private $uf;
    private $unidadeId;
   
    public function search(Request $request){
        $list = Chamado::query();

        if($request->has('polos') && $request->polos){
            $list->whereHas('usuario',function ($query) {
                $query->whereHas('unidade',function ($query1) {
                    $query1->where('classe',['polo']);
                });
            });
        }
        
        if($request->has('gestoras') && $request->gestoras){
            $list->whereHas('usuario',function ($query) {
                $query->whereHas('unidade',function ($query1) {
                    $query1->whereIn('classe',['tvescola','mec']);
                });
            });
        }

        if($request->has('empresas') && $request->gestoras){
            $list->whereHas('usuario',function ($query) {
                $query->whereHas('unidade',function ($query1) {
                    $query1->where('classe','empresa');
                });
            });
        }

        if($request->has('responsavelPolos') && $request->responsavelPolos){
            $list->whereHas('unidadeResponsavel',function ($query) {
                $query->where('classe',['polo']);
            });
        }
        
        if($request->has('responsavelGestoras') && $request->responsavelGestoras){
            $list->whereHas('unidadeResponsavel',function ($query) {
                    $query->whereIn('classe',['tvescola','mec']);
            });
        }

        if($request->has('responsavelEmpresas') && $request->responsavelEmpresas){
            $list->whereHas('unidadeResponsavel',function ($query) {
                    $query->where('classe','empresa');
            });
        }


        if($request->has('status_id')){
            $list->where('status_id', $request->status_id);
        }

        if($request->has('tipo_id')){    
            $list->where('tipo_id', $request->tipo_id);
        }

        if($request->has('q')){
            $this->q = $request->q;
            $list->where('descricao','ilike','%'.$request->q.'%')->orWhere('assunto', 'ilike','%'.$request->q.'%');

            $list->orWhereHas('comments', function ($query) {
                $query->where('content', 'ilike', '%'.$this->q.'%');
            });  

        }

        if($request->has('unidade_responsavel_id')){
            $list->where('unidade_responsavel_id', $request->unidade_responsavel_id);
        }

        if($request->has('unidade_id')){
            $this->unidadeId = $request->unidade_id;
            $list->whereHas('usuario', function($query1){
                $query1->where('unidade_id', $this->unidadeId);
            });
        }

        if($request->has('uf')){
            $this->uf = $request->uf;
            $list->whereHas('usuario', function($query1){
                $query1->whereHas('unidade',function ($query2) {
                    $query2->whereHas('localidade', function ($query3){
                        $query3->whereHas('estado', function ($query4){
                            $query4->where('sigla','=',$this->uf);
                        });
                    });
                });
            });

        }

        $per_page = $request->per_page ? $request->per_page : 25;
        return ChamadoResource::collection($list->paginate( $per_page ));
    }

}
