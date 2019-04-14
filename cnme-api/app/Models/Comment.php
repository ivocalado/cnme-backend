<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Comment extends Model
{
    protected $fillable = [
        'usuario_id', 'comment_id', 'comment_type', 'tipo', 'content'
    ];

    function __construct()
	{
    }

    function build($content, $user, $commentType, $commentId, $auto = false){
        $this->usuario()->associate($user);
        $this->content = $content;
        $this->comment_id = $commentId;
        $this->comment_type = $commentType;

    
        $this->tipo = $auto ? 'auto':'comment';
    }

    public function isAuto(){
        return $this->tipo == 'auto';
    }

    public function isCommentUser(){
        return $this->tipo == 'comment';
    }

    public function comment()
    {
        return $this->morphTo();
    }

    public function usuario(){
        return $this->belongsTo(User::class)->withTrashed();
    }

    public static function findCommentable($commentType, $commentableId){
        
        switch($commentType){
            case "projeto":
                return ProjetoCnme::find($commentableId);
            case "tarefa":
                return  Tarefa::find($commentableId);
            case "chamado":
                return Chamado::find($commentableId);
            case "unidade":
                return  Unidade::find($commentableId);
            default:
                return null;
        }

    }
}
