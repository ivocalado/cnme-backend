<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\User;

class Unidade extends Model
{

    protected $fillable = [
        'nome', 'email', 'codigo_inep', 'diretor', 'telefone', 'url','localidade_id','tipo_unidade_id','responsavel_id'
    ];


    public function tipoUnidade(){
        return $this->belongsTo(TipoUnidade::class);
    }

    public function localidade(){
        return $this->belongsTo(Localidade::class);
    }

    public function responsavel(){
        return $this->belongsTo(User::class,'responsavel_id');
    }

    public function usuarios(){
        return $this->hasMany(User::class, 'unidade_id', 'id');
    }
}
