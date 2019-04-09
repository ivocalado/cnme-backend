<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Chamado;
use Illuminate\Support\Facades\Log;
use App\Models\Comment;
use App\User;
use App\Models\Unidade;
use App\Models\StatusChamado;
use App\Models\TipoChamado;
use Illuminate\Support\Facades\Auth;

class ChamadoEvent
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

    public function chamadoUpdated(Chamado $chamado)
    {
        $changes = $chamado->isDirty() ? $chamado->getDirty() : false;

        if($changes)
        {
            $comment = new Comment();
            $message = "";
            foreach($changes as $attr => $value)
            {
                if($attr != "updated_at"){
                    switch ($attr){
                        case('usuario_responsavel_id'):
                            $usuarioResponsavelOld = User::find($chamado->getOriginal("usuario_responsavel_id"));
                            if($usuarioResponsavelOld && $value)
                                $message .= "Usuário responsável alterado de ".$usuarioResponsavelOld->name." para ".$chamado->usuarioResponsavel->name."\n";
                            elseif($usuarioResponsavelOld && !isset($value))
                                $message .= "Usuário responsável ".$usuarioResponsavelOld->name." removido do chamado.\n";
                            elseif($chamado->usuarioResponsavel)
                                $message .= "Usuário responsável configurado para".$chamado->usuarioResponsavel->name.".\n";
                            break;
                        case('unidade_responsavel_id'):
                            $unidadeResponsavelOld = Unidade::find($chamado->getOriginal("unidade_responsavel_id"));
                            if($unidadeResponsavelOld)
                                $message .= "Unidade responsável alterada de ".$unidadeResponsavelOld->nome." para ".$chamado->unidadeResponsavel->nome."\n";
                            else
                                $message .= "Unidade responsável configurado para ".$chamado->unidadeResponsavel->nome."\n";
                            break;
                        case('status_id'):
                            $statusOld = StatusChamado::find($chamado->getOriginal("status_id"));
                            $message .= "Status de ".$statusOld->nome." para ".$chamado->status->nome."\n";
                            break;
                        case($attr == 'tipo_id'):
                            $tipoOld = TipoChamado::find($chamado->getOriginal("tipo_id"));
                            $message .= "Tipo de ".$tipoOld->nome." para ".$chamado->tipo->nome."\n";
                            break;
                        default:
                            $oldValue = $chamado->getOriginal($attr);
                            if($oldValue)
                                $message .= $attr." alterado de ".$oldValue." para ".$value."\n";
                            else
                                $message .= $attr." configurado para ".$value."\n";
                    }//end swith
                }//end if not updated_at
            }//end foreach

            $comment = new Comment();
            $comment->build($message,Auth::user(),get_class($chamado), $chamado->id, true);
            $comment->save();
            

        }
    
    }
}
