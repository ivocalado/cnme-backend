<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoChamado extends Model
{
    protected $fillable = ['nome', 'descricao'];

    public static function tipos(){
        return TipoChamado::all()->pluck('nome')->toArray();
    }
}
