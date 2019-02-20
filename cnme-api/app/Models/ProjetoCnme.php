<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Validation\Rule;

class ProjetoCnme extends Model
{

    public const STATUS_CRIADO = 'CRIADO';/**Projeto criado mas sem o planejamento das entregas */
    public const STATUS_PLANEJAMENTO = 'PLANEJAMENTO';/**Projeto iniciado em planejamento mas equipamentos não foram enviados */
    public const STATUS_ENVIADO = 'ENVIADO';//Planejamento realizado e todos os equipamentos enviados;
    public const STATUS_ENTREGUE = 'ENTREGUE';//Equipamentos entregues;
    public const STATUS_INSTALADO = 'INSTALADO';//Produto entregue e instalado
    public const STATUS_FINALIZADO = 'FINALIZADO';//Instalado e ativado para operação
    public const STATUS_CANCELADO = 'CANCELADO';

    protected $fillable = [
        'id','numero', 'status','descricao','unidade_id','usuario_id','solicitacao_cnme_id',
        'data_inicio_previsto','data_fim_previsto','data_inicio','data_fim'
    ];

    public static function status(){
        return [
            ProjetoCnme::STATUS_CRIADO, 
            ProjetoCnme::STATUS_PLANEJAMENTO, 
            ProjetoCnme::STATUS_ENVIADO, 
            ProjetoCnme::STATUS_ENTREGUE, 
            ProjetoCnme::STATUS_INSTALADO, 
            ProjetoCnme::STATUS_FINALIZADO, 
            ProjetoCnme::STATUS_CANCELADO];
    }

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

    public function getEtapaEnvio(){
        $etapa =  Etapa::where([
            ['projeto_cnme_id', $this->id],
            ['tipo', Etapa::TIPO_ENVIO]
            ])->first();
        
        return $etapa;
    }

    public $rules = [
        'numero'    =>  'required|unique:projeto_cnmes|max:20',
        'descricao' =>  'required',
        'usuario_id' => 'required|integer',
        'unidade_id' => 'required|integer',
        'solicitacao_cnme_id' => 'nullable|integer',
        'data_inicio_previsto' => 'required|date',
        'data_fim_previsto' => 'required|date',
        'data_inicio' => 'nullable|date',
        'data_fim' => 'nullable|date',       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'date' => 'O campo :attribute é um campo no formato de data'
    ];
}
