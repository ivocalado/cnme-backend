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

    public $rules = [
        'logradouro'    =>  'required|max:255',
        'numero'    =>  'required|max:10',
        'bairro'       =>  '|required|max:100',
        'cep'   => 'required|min:8|max:9',
        'complemento'   => 'nullable',
        'estado_id'   => 'required|integer|exists:estados,id',
        'municipio_id' => 'required|integer|exists:municipios,id',
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max' => 'No campo :attribute, o valor :input deve possuir no máximo :max caracteres',
        'between' => 'O :attribute deve possuir entre :min e :max caracteres',
        'min' => 'No campo :attribute, o valor atual é :input, porém deve possuir no mínimo :min caracteres',
        'estado_id.exists' => 'Estado(estado_id) não encontrado',
        'municipio_id.exists' => 'Município(municipio_id) não encontrado'
    ];
}
