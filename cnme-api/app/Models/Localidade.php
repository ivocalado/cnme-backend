<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Localidade extends Model
{
    protected $fillable = [
        'nome', 'email', 'codigo_inep', 'diretor', 'telefone', 'url'
    ];

    public function municipio(){
        return $this->belongsTo(Municipio::class);
    }

    public function estado(){
        return $this->belongsTo(Estado::class);
    }
}
