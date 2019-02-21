<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipamento extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['nome', 'descricao','requisitos','fornecedor','tipo_equipamento_id'];

    public function tipoEquipamento(){
        return $this->belongsTo(TipoEquipamento::class);
    }

    public function kits()
    {
        return $this->belongsToMany(Kit::class, 'kit_equipamento');
    }

    public $rules = [
        'nome'    =>  'required|unique:equipamentos|max:255',
        'descricao'    =>  'required|max:255',
        'tipo_equipamento_id'   => 'integer|required|exists:tipo_equipamentos,id'

       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max'   => 'O campo :attribute é deve ter no máximo :max caracteteres',
        'tipo_equipamento_id.exists' => 'Tipo de Equipamento(tipo_equipamento_id) não encontrado',
    ];
}
