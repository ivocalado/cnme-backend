<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Tarefa extends Model
{
    public const STATUS_ABERTA = 'ABERTA';
    public const STATUS_ANDAMENTO = 'ANDAMENTO';
    public const STATUS_CONCLUIDA = 'CONCLUIDA';
    public const STATUS_CANCELADA = 'CANCELADA';

    public const TIPO_ENVIO = 'ENVIO';
    public const TIPO_ENTREGA = 'ENTREGA';
    public const TIPO_INSTALACAO = 'INSTALACAO';
    public const TIPO_SUPORTE = 'SUPORTE';
    public const TIPO_ATIVACAO = 'ATIVACAO';

    public const DESC_TAREFA_ENVIO = "Envio dos equipamentos";
    public const DESC_TAREFA_INSTALACAO = "Instalação dos equipamentos";
    public const DESC_TAREFA_ATIVACAO = "Ativaçãos dos equipamentos";

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
        'numero' => 'max:30',
        'link_externo' => 'max:255',
        'status'    =>  'required:max:50',
        'usuario_id' => 'required|integer|exists:users,id',
        'etapa_id' => 'required|integer|exists:etapas,id',
        'responsavel_id' => 'integer|exists:users,id',
        'unidade_responsavel_id' => 'integer|required|exists:unidades,id',
        'data_inicio' => 'nullable|date|before_or_equal:data_fim',
        'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        'data_inicio_prevista' => 'required|date|before_or_equal:data_fim_prevista',
        'data_fim_prevista' => 'required|date|after_or_equal:data_inicio_prevista',
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'date' => 'O campo :attribute é um campo no formato de data'
    ];


}
