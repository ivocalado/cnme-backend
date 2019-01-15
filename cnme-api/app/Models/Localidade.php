<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Localidade extends Model
{
    protected $fillable = [
        'logradouro', 'numero', 'bairro', 'cep', 'complemento', 'estado_id','municipio_id'
    ];

    public function municipio(){
        return $this->belongsTo(Municipio::class);
    }

    public function estado(){
        return $this->belongsTo(Estado::class);
    }
}
