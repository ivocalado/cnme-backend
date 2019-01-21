<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjetoCnme extends Model
{
    protected $fillable = [
        'id','numero', 'status','descricao','unidade_id','usuario_id','solicitacao_cnme_id','data_criacao'
        ,'data_implantacao_prevista','data_implantacao_realizada','data_inicio_entrega'
    ];
}
