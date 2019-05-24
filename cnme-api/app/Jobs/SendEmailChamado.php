<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\MailSender;

class SendEmailChamado implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $chamado;

    private $comment;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($chamado, $comment = null)
    {
        $this->chamado = $chamado;
        $this->comment = $comment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->comment == null){ //novo chamado
            MailSender::notificarChamadoCriado($this->chamado);
        }else{
            MailSender::notificarChamadoAtualizado($this->chamado, $this->comment);
        }

        $this->chamado->notificado_at = date('Y-m-d H:i:s');
        $this->chamado->save();

    }
}
