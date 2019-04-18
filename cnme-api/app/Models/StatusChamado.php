<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusChamado extends Model
{
    protected $fillable = ['nome', 'descricao'];

    public static function status(){
        return StatusChamado::all()->pluck('nome')->toArray();
    }

}
