<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Etapa extends Model
{
    public const STATUS_ABERTA = 'ABERTA';
    public const STATUS_EXECUCAO = 'EXECUCAO';
    public const STATUS_CONCLUIDA = 'CONCLUIDA';
    public const STATUS_CANCELADA = 'CANCELADA';

    public const TIPO_ENVIO = 'ENVIO';
    public const TIPO_INSTALACAO = 'INSTALACAO';
    public const TIPO_ATIVACAO = 'ATIVACAO';

    protected $fillable = [
       'status','descricao','tipo','usuario_id','projeto_cnme_id','data_inicio'
        ,'data_fim','data_inicio_prevista','data_fim_prevista'
    ];

    public function usuario(){
        return $this->belongsTo(User::class);
    }

    public function projetoCnme(){
        return $this->belongsTo(ProjetoCnme::class);
    }

    public function tarefas()
    {
        return $this->hasMany(Tarefa::class);
    }

    public function equipamentos()
    {
        $ids =  $this->tarefas()->pluck('id');
        $equipamentosProjetos = EquipamentoProjeto::whereHas('tarefas', function($query) use ($ids)
        {
            $query->whereIn('id', $ids);
        })
        ->with('tarefas')
        ->get();

        return  $equipamentosProjetos;
    }

    public $rules = [
        'descricao'    =>  'required|max:255',
        'status'    =>  'required',
        'tipo' =>  'required',
        'usuario_id' => 'required|integer',
        'projeto_cnme_id' => 'required|integer',
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
