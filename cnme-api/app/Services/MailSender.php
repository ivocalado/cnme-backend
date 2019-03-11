<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

use App\User;
use Mail;
use App\Models\Tarefa;

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

}