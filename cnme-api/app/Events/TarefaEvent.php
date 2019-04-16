<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Tarefa;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use App\User;

class TarefaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    public function tarefaUpdated(Tarefa $tarefa)
    {
        $changes = $tarefa->isDirty() ? $tarefa->getDirty() : false;

        if($changes){
            $comment = new Comment();
            $message = "";
            foreach($changes as $attr => $value){
                

                    switch ($attr){
                        case('responsavel_id'):
                            $usuarioResponsavelOld = User::find($tarefa->getOriginal("responsavel_id"));
                            if($usuarioResponsavelOld && $value)
                                $message .= "Usuário responsável alterado de ".$usuarioResponsavelOld->name." para ".$tarefa->responsavel->name."\n";
                            elseif($usuarioResponsavelOld && !isset($value))
                                $message .= "Usuário responsável ".$usuarioResponsavelOld->name." removido do tarefa.\n";
                            elseif($tarefa->usuarioResponsavel)
                                $message .= "Usuário responsável configurado para".$tarefa->responsavel->name.".\n";
                            break;
                        case('unidade_responsavel_id'):
                            $unidadeResponsavelOld = Unidade::find($tarefa->getOriginal("unidade_responsavel_id"));
                            if($unidadeResponsavelOld && $tarefa->unidadeResponsavel)
                                $message .= "Unidade responsável alterada de ".$unidadeResponsavelOld->nome." para "
                                                .$tarefa->unidadeResponsavel->nome."\n";
                            elseif($unidadeResponsavelOld && !isset($tarefa->unidadeResponsavel))
                                $message .= "Unidade responsável ".$unidadeResponsavelOld->name." removida da tarefa.\n";
                            elseif($tarefa->unidadeResponsavel)
                                $message .= "Unidade responsável configurado para ".$tarefa->unidadeResponsavel->nome."\n";
                            break;
                        default: 
                            if($attr != "updated_at"){
                                $oldValue = $tarefa->getOriginal($attr);
                                if($oldValue)
                                    $message .= $attr." alterado de ".$oldValue." para ".$value."\n";
                                else
                                    $message .= $attr." configurado para ".$value."\n";
                            }//end if not updated_at
                    }
                   
                
            }//end foreach

            $comment = new Comment();

            $user = Auth::check()? Auth::user() : User::where('tipo', 'administrador')->first(); 
            $comment->build($message, $user, get_class($tarefa), $tarefa->id, true);
            
            $comment->save();
        }
    }
}
