<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEquipamento extends Model
{
    protected $fillable = ['nome', 'descricao'];

    public function equipamentos(){
        return $this->hasMany(Equipamento::class);
    }

    public $rules = [
        'nome'    =>  'required|unique:tipo_equipamentos|max:255',
        'descricao'    =>  'required|max:255',
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max'   => 'O campo :attribute é deve ter no máximo :max caracteteres'
    ];
}
