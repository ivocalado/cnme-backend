<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Comment extends Model
{
    protected $fillable = [
        'usuario_id', 'comment_id', 'comment_type', 'tipo'
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


    public function comment()
    {
        return $this->morphTo();
    }

    public function usuario(){
        return $this->belongsTo(User::class)->withTrashed();
    }
}
