<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

use App\User;
use Mail;
use App\Models\Tarefa;
use Illuminate\Support\Facades\Auth;

class MailSender{
    
    public static function convite($userNovo){
        $to_name    = $userNovo->name;
        $to_email   = (getenv('APP_ENV') === 'local') ? getenv('MAIL_USERNAME') : $userNovo->email;

        $data = array(
            'nome'      =>  $userNovo->name, 
            "email"     =>  $userNovo->email,
            "token"     =>  $userNovo->remember_token,
            "unidade"   =>  $userNovo->unidade->nome,
            "tipo"      =>  $userNovo->tipo,
            "APP_URL"   =>  getenv('APP_URL')  
            
        );


        Mail::send('emails.convite', $data, function($message) use ($to_name, $to_email) {
             $message->to($to_email, $to_name)
                 ->subject('Acesso a plataforma CNME');
                 $message->from(getenv('MAIL_USERNAME'),'CNME - Centro Nacional de Mídias da Educação');
        });
    }

    public static function notificacaoNovaSenha($user){
        $to_name    = $user->name;
        $to_email   = (getenv('APP_ENV') === 'local') ? getenv('MAIL_USERNAME') : $user->email;
    
        $data = array(
            'nome'      =>  $user->name, 
            "email"     =>  $user->email,
            "token"     =>  $user->remember_token,
            "unidade"   =>  $user->unidade->nome,
            "APP_URL"   =>  getenv('APP_URL')  
            
        );


        

        Mail::send('emails.recuperar-senha', $data, function($message) use ($to_name, $to_email) {
             $message->to($to_email, $to_name)
                 ->subject('Acesso a plataforma CNME');
                 $message->from(getenv('MAIL_USERNAME'),'CNME - Centro Nacional de Mídias da Educação');
        });
    }

    public static function notificar($projeto, $tarefa){
        $unidade = $projeto->unidade;
        $usuario =  $unidade->responsavel;

        $empresaResponsavel = $tarefa->unidadeResponsavel;
        $to_name    = $usuario->name;
        $to_email   = (getenv('APP_ENV') === 'local') ? getenv('MAIL_USERNAME') : $usuario->email;

        
        $equipamentos = ($tarefa->equipamentosProjetos->count() > 0) ? 
                            $tarefa->equipamentosProjetos->pluck('equipamento'):
                            $projeto->equipamentoProjetos->pluck('equipamento');

        $hasEnviosPendentes = $tarefa->etapa->hasTarefasAbertasAndamento();

        $data = array(
            'nome'      =>  $usuario->name, 
            "unidade"   =>  $unidade,
            "responsavel"      =>  $empresaResponsavel,
            "numero"            => $tarefa->numero,
            "link_externo"            => $tarefa->link_externo,
            "equipamentos"            => $equipamentos, 
            "data_inicio"     => date_format(new \DateTime($tarefa->data_inicio),"d/m/Y"),
            "data_fim"     => date_format(new \DateTime($tarefa->data_fim),"d/m/Y"),
            "data_fim_prevista"     => date_format(new \DateTime($tarefa->data_fim_prevista),"d/m/Y"),           
            "APP_URL"   =>  getenv('APP_URL'),
            "pendentes" => $hasEnviosPendentes
            
        );

        $template = "emails.".\strtolower($projeto->status);
        
        Mail::send( $template, $data, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                ->subject('Plataforma CNME - Processo de Implantação');
                $message->from(getenv('MAIL_USERNAME'),'CNME - Centro Nacional de Mídias da Educação');
        });
        

    }

    public static function cancelamento($projeto){
        $unidade = $projeto->unidade;
        $usuario = $unidade->responsavel;

        $to_name    = $usuario->name;
        $to_email   = (getenv('APP_ENV') === 'local') ? getenv('MAIL_USERNAME') : $usuario->email;
        
        $data = array(
            'usuario'   => $usuario,
            'unidade'   => $unidade,
            'projeto'   => $projeto,
            "APP_URL"   =>  getenv('APP_URL')
        );
        Mail::send( 'emails.cancelamento', $data, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                ->subject('Plataforma CNME - Processo de Implantação');
                $message->from(getenv('MAIL_USERNAME'),'CNME - Centro Nacional de Mídias da Educação');
        });
    }

    public static function recuperar($projeto){
        $unidade = $projeto->unidade;
        $usuario = $unidade->responsavel;

        $to_name    = $usuario->name;
        $to_email   = (getenv('APP_ENV') === 'local') ? getenv('MAIL_USERNAME') : $usuario->email;
        
        $data = array(
            'usuario'   => $usuario,
            'unidade'   => $unidade,
            'projeto'   => $projeto,
            "APP_URL"   =>  getenv('APP_URL')
        );

        Mail::send( 'emails.recuperar', $data, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                ->subject('Plataforma CNME - Processo de Implantação');
                $message->from(getenv('MAIL_USERNAME'),'CNME - Centro Nacional de Mídias da Educação');
        });
    }

    public static function notificarEnviarTodos($projeto){
        $unidade = $projeto->unidade;
        $usuario = $unidade->responsavel;

        $to_name    = $usuario->name;
        $to_email   = (getenv('APP_ENV') === 'local') ? getenv('MAIL_USERNAME') : $usuario->email;

        $etapaEnvio = $projeto->getEtapaEnvio();
        $tarefasEnvio = $etapaEnvio->tarefas;

        $tarefasEnvio->each(function ($t, $k) {
            $t->notificado_at = date('Y-m-d H:i:s');
            $t->save();
        });
        
        $data = array(
            'usuario'   => $usuario,
            'unidade'   => $unidade,
            'projeto'   => $projeto,
            'etapa'   => $etapaEnvio,
            'envios'   => $tarefasEnvio,
            "APP_URL"   =>  getenv('APP_URL')
        );

        Mail::send( 'emails.enviar-all', $data, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                ->subject('Plataforma CNME - Processo de Implantação');
                $message->from(getenv('MAIL_USERNAME'),'CNME - Centro Nacional de Mídias da Educação');
        });
    }

    public static function notificarChamadoCriado($chamado){
        $usuarioReponsavel = $chamado->usuarioResponsavel ? $chamado->usuarioResponsavel : $chamado->unidadeResponsavel->responsavel;

        $to_name    = $usuarioReponsavel->name;
        $to_email   = (getenv('APP_ENV') === 'local') ? getenv('MAIL_USERNAME') : $usuarioReponsavel->email;

        $data = array(
            'chamado'   => $chamado,
            'responsavel' => $usuarioReponsavel,
            "APP_URL"   =>  getenv('APP_URL')
        );

        Mail::send( 'emails.chamado-criado', $data, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                ->subject("Plataforma CNME - Abertura de Chamado");
                $message->from(getenv('MAIL_USERNAME'),'CNME - Centro Nacional de Mídias da Educação');
        });
    }

    public static function notificarChamadoAtualizado($chamado, $comment){
        $usuarioReponsavel = $chamado->usuarioResponsavel ? $chamado->usuarioResponsavel : $chamado->unidadeResponsavel->responsavel;

        $usuario = Auth::user();
        $to_name    = $usuarioReponsavel->name;
        $to_email   = (getenv('APP_ENV') === 'local') ? getenv('MAIL_USERNAME') : $usuarioReponsavel->email;

        $messagesArray = array();
        if( $comment->isAuto() ){
            $messagesArray = explode("\n", $comment->content);
            array_pop($messagesArray);
        }
        
        $data = array(
            'responsavel' => $usuarioReponsavel,
            'usuario'   => $comment->usuario,
            'comment'      => $comment,
            'chamado'   => $chamado,
            'messages'  =>  $messagesArray,
            "APP_URL"   =>  getenv('APP_URL')
        );

        Mail::send( 'emails.chamado-atualizado', $data, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                ->subject("Plataforma CNME - Chamado atualizado");
                $message->from(getenv('MAIL_USERNAME'),'CNME - Centro Nacional de Mídias da Educação');
        });
    }

}