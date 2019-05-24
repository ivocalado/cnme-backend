<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\MailSender;

class SendEmailProjeto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $projetoCnme;
    private $tarefa;

    private $tipo = SendEmailProjeto::TIPO_NOTIFICACAO_ENVIADO; //RECUPERAR, CANCELAR


    public const TIPO_NOTIFICACAO_ENVIADO = 'ENVIADO';
    public const TIPO_NOTIFICACAO_CANCELAR = 'CANCELAR';
    public const TIPO_NOTIFICACAO_RECUPERAR = 'RECUPERAR';
    public const TIPO_NOTIFICACAO_ETAPA = 'ETAPA';

    /**
     * Create a new job instance.
     * tipo: ENVIADO(padrão), RECUPERAR ou CANCELAR
     * @return void
     */
    public function __construct($projetoCnme, 
                        $tipo = SendEmailProjeto::TIPO_NOTIFICACAO_ENVIADO, 
                        $tarefa = null)
    {
        $this->projetoCnme = $projetoCnme;
        $this->tarefa = $tarefa;

        $this->tipo = $tipo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->tarefa == null && $this->tipo == SendEmailProjeto::TIPO_NOTIFICACAO_ENVIADO){
            MailSender::notificarEnviarTodos($this->projetoCnme);
        }elseif($this->tipo == SendEmailProjeto::TIPO_NOTIFICACAO_RECUPERAR){
            MailSender::recuperar($this->projetoCnme);
        }elseif($this->tipo == SendEmailProjeto::TIPO_NOTIFICACAO_CANCELAR){
            MailSender::cancelamento($this->projetoCnme);
        }elseif($this->tarefa != null && $this->tipo == SendEmailProjeto::TIPO_NOTIFICACAO_ENVIADO){
            MailSender::notificar($this->projetoCnme, $this->tarefa);
        }
    }
}
