<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\ProjetoCnme;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use App\User;

class ProjetoEvent
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

    public function projetoUpdated(ProjetoCnme $projeto)
    {
        $changes = $projeto->isDirty() ? $projeto->getDirty() : false;

        if($changes){
            $comment = new Comment();
            $message = "";
            foreach($changes as $attr => $value){
                if($attr != "updated_at"){
                    $oldValue = $projeto->getOriginal($attr);
                    if($oldValue)
                        $message .= $attr." alterado de ".$oldValue." para ".$value."\n";
                    else
                        $message .= $attr." configurado para ".$value."\n";
                }//end if not updated_at
            }//end foreach

            $comment = new Comment();
            $user = Auth::check()? Auth::user() : User::where('tipo', 'administrador')->first(); 
            $comment->build($message,$user ,get_class($projeto), $projeto->id, true);
            $comment->save();
        }
    }
}
