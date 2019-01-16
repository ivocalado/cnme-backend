<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\User;

class Unidade extends Model
{

    protected $fillable = [
        'id','nome', 'email', 'codigo_inep', 'diretor', 'telefone', 'url','localidade_id','tipo_unidade_id','responsavel_id'
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

    public $rules = [
        'nome'    =>  'required|unique:unidades|max:20',
        'email'    =>  'required|max:255',
        'codigo_inep'       =>  'string|min:4',
        'diretor'   => 'nullable',
        'telefone' => 'required|string',
        'url'   => 'active_url|max:200',
        'tipo_unidade_id' => 'required|integer',
        'responsavel_id' => 'required|integer'
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é requerido',
        'numero.max' => 'O campo :attribute deve ter no máximo 20 caracteres',
        'url.max' => 'O campo :attribute deve ter no máximo 200 caracteres',
        'ano.min' => 'O campo :attribute deve ter no mínimo 4 caracteres',
        'titulo.max'  => 'O campo :attribute deve ter no máximo 255 caracteres',
        'numero.unique'   => 'O número deve ser único entre os orgão. Dica: Use junto com a sigla do seu orgão',
        'integer' => 'O campo :attribute deve inteiro',
        'arquivo.mimes'   => 'O documento anexado tem que estar no formato PDF',
        'active_url' => 'A url deve ter um formato válido. Ex.: http://www.seuorgao.com/arquivos/resolucao123'
    ];
}
