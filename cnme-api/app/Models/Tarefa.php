<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Tarefa extends Model
{
    public const STATUS_ABERTA = 'ABERTA';
    public const STATUS_EM_ANDAMENTO = 'EM_ANDAMENTO';
    public const STATUS_CONCLUIDA = 'CONCLUIDA';
    public const STATUS_PARALIZADA = 'PARALIZADA';
    public const STATUS_CANCELADA = 'CANCELADA';

    public const TIPO_PLANEJAMENTO = 'PLANEJAMENTO';
    public const TIPO_ENVIO = 'ENVIO';
    public const TIPO_ENTREGA = 'ENTREGA';
    public const TIPO_INSTALACAO = 'INSTALACAO';
    public const TIPO_AVALIACAO = 'AVALIACAO';
    public const TIPO_ATIVACAO = 'ATIVACAO';

    protected $fillable = [
        'nome','descricao','numero','status','tipo','data_inicio_prevista','data_fim_prevista','data_inicio',
        'data_fim','etapa_id','usuario_id','responsavel_id','unidade_responsavel_id'
    ];

    public function usuario(){
        return $this->belongsTo(User::class);
    }

    public function etapa(){
        return $this->belongsTo(Etapa::class);
    }

    public function responsavel(){
        return $this->belongsTo(User::class,'responsavel_id');
    }

    public function unidadeResponsavel(){
        return $this->belongsTo(Unidade::class,'unidade_responsavel_id');
    }

    public $rules = [
        'nome'    =>  'required|max:255',
        'descricao'    =>  'required',
        'numero' =>  'required:integer',
        'status'    =>  'required:max:50',
        'tipo'    =>  'required:max:50',

        'usuario_id' => 'required|integer',
        'etapa_id' => 'required|integer',
        'responsavel_id' => 'integer',
        'unidade_responsavel_id' => 'integer',
        'data_inicio' => 'nullable|date',
        'data_fim' => 'nullable|date',
        'data_inicio_prevista' => 'required|date',
        'data_fim_prevista' => 'required|date',
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'date' => 'O campo :attribute é um campo no formato de data'
    ];


}
