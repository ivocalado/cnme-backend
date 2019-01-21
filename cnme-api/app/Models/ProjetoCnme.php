<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class ProjetoCnme extends Model
{

    public const STATUS_ABERTO = 'ABERTO';
    public const STATUS_EM_ANDAMENTO = 'EM_ANDAMENTO';
    public const STATUS_CONCLUIDO = 'CONCLUIDO';
    public const STATUS_PARALIZADO = 'PARALIZADO';
    public const STATUS_CANCELADO = 'CANCELADO';

    protected $fillable = [
        'id','numero', 'status','descricao','unidade_id','usuario_id','solicitacao_cnme_id','data_criacao'
        ,'data_implantacao_prevista','data_implantacao_realizada','data_inicio_entrega'
    ];

    public function usuario(){
        return $this->belongsTo(User::class);
    }

    public function unidade(){
        return $this->belongsTo(Unidade::class);
    }

    public function solicitacaoCnme(){
        return $this->belongsTo(SolicitacaoCnme::class);
    }

    public $rules = [
        'numero'    =>  'required|unique:projeto_cnmes|max:20',
        'status'    =>  'required',
        'descricao' =>  'required',
        'usuario_id' => 'required|integer',
        'unidade_id' => 'required|integer',
        'solicitacao_cnme_id' => 'nullable|integer',
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'date' => 'O campo :attribute é um campo no formato de data'
    ];
}
