<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

use App\User;
use Mail;

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
}