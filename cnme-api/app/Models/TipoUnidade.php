<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoUnidade extends Model
{
    protected $fillable = [
        'nome', 'descricao', 'categoria'
    ];


    public function unidades(){
        return $this->hasMany(Unidade::class);
    }
}
