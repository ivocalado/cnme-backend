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

    public $rules = [
        'nome'    =>  'required|unique:tipo_unidades|max:100',
        'descricao'    =>  'nullable|max:255',
        'categoria'       =>  'nullable|max:50',
    
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max' => 'No campo :attribute, o valor :input deve possuir no máximo :max caracteres',
        'unique' => 'Já existe um registro com :attribute igual a :input' 
    ];

}
