<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;

class EmailConvite extends Mailable
{
    use Queueable, SerializesModels;


    private $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $to_name    = $this->user->name;
        $to_email   = (getenv('APP_ENV') === 'local') ? getenv('MAIL_USERNAME') : $userNovo->email;

        $data = array(
            'nome'      =>  $this->user->name, 
            "email"     =>  $this->user->email,
            "token"     =>  $this->user->remember_token,
            "unidade"   =>  $this->user->unidade->nome,
            "tipo"      =>  $this->user->tipo,
            "APP_URL"   =>  getenv('APP_URL')  
            
        );


        return $this->markdown('emails.convite')
            ->subject('Acesso a plataforma CNME')
            ->with($data);
    }
}
