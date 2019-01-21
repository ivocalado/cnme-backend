<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class SolicitacaoCnme extends Model
{
    protected $fillable = [
        'id','descricao', 'usuario_id','unidade_id','data_solicitacao'
    ];

    public function usuario(){
        return $this->belongsTo(User::class);
    }

    public function unidade(){
        return $this->belongsTo(Unidade::class);
    }

    public $rules = [
        'descricao'    =>  'required',
        'data_solicitacao'       =>  'required|date',
        'usuario_id' => 'required|integer',
        'unidade_id' => 'required|integer'
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'date' => 'O campo :attribute é um campo no formato de data'
    ];
}
