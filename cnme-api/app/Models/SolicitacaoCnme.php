<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class SolicitacaoCnme extends Model
{

    public const STATUS_ABERTA = 'ABERTA';
    public const STATUS_EM_ANDAMENTO = 'EM_ANDAMENTO';
    public const STATUS_CONCLUIDA = 'CONCLUIDA';
    public const STATUS_CANCELADA = 'CANCELADA';
    

    protected $fillable = [
        'id','descricao', 'usuario_id','unidade_id','status','data_solicitacao'
    ];

    public function usuario(){
        return $this->belongsTo(User::class);
    }

    public function unidade(){
        return $this->belongsTo(Unidade::class);
    }

    public $rules = [
        'descricao'    =>  'required',
        'status' => 'required',
        'data_solicitacao'       =>  'date',
        'usuario_id' => 'required|integer'
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'date' => 'O campo :attribute é um campo no formato de data'
    ];
}
