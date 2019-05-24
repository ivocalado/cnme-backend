<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailConvite;
use App\Services\MailSender;

class SendEmailConvite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;

    private $novaSenha;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $novaSenha = false)
    {
        $this->user = $user;

        $this->novaSenha = $novaSenha;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->novaSenha){
            MailSender::notificacaoNovaSenha($this->user);
        }else{
            MailSender::convite($this->user);
        }

       
    }
}
