<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Tarefa extends Model
{
    public const STATUS_ABERTA = 'ABERTA';
    public const STATUS_EXECUCAO = 'EXECUCAO';
    public const STATUS_CONCLUIDA = 'CONCLUIDA';
    public const STATUS_CANCELADA = 'CANCELADA';

    protected $fillable = [
        'nome','descricao','numero','status','link_externo','data_inicio_prevista','data_fim_prevista','data_inicio',
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

    public function equipamentosProjetos(){
        return $this->belongsToMany(EquipamentoProjeto::class,'tarefa_equipamento_projeto')->withTimestamps();
    }

    public $rules = [
        'nome'    =>  'required|max:255',
        'status'    =>  'required:max:50',
        'usuario_id' => 'required|integer',
        'etapa_id' => 'required|integer',
        'responsavel_id' => 'integer',
        'unidade_responsavel_id' => 'integer|required',
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
