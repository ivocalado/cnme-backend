<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class ProjetoCnme extends Model
{

    public const STATUS_PLANEJAMENTO = 'PLANEJAMENTO';
    public const STATUS_ENVIO = 'ENVIO';
    public const STATUS_INSTALACAO = 'INSTALACAO';
    public const STATUS_ATIVACAO = 'ATIVACAO';
    public const STATUS_FINALIZADO = 'FINALIZADO';
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

    public function kit(){
        return $this->belongsTo(Kit::class);
    }

    public function etapas(){
        return $this->hasMany(Etapa::class);
    }

    public function equipamentoProjetos(){
        return $this->hasMany(EquipamentoProjeto::class);
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
