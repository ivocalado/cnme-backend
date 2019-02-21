<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipamentoProjeto extends Model
{

    public const STATUS_PLANEJADO = "PLANEJADO";
    public const STATUS_ENVIADO = "ENVIADO";
    public const STATUS_ENTREGUE = "ENTREGUE";
    public const STATUS_INSTALADO = "INSTALADO";
    public const STATUS_ATIVADO = "ATIVADO";

    protected $fillable = ['detalhes','observacao','status','equipamento_id','projeto_cnme_id'];


    public static function status(){
        
        return array( 
            EquipamentoProjeto::STATUS_PLANEJADO, 
            EquipamentoProjeto::STATUS_ENVIADO,
            EquipamentoProjeto::STATUS_ENTREGUE,
            EquipamentoProjeto::STATUS_INSTALADO,
            EquipamentoProjeto::STATUS_ATIVADO
        );
       
    }

    public function equipamento(){
        return $this->belongsTo(Equipamento::class); 
    }

    public function projetoCnme(){
        return $this->belongsTo(ProjetoCnme::class); 
    }

    public function tarefas(){
        return $this->belongsToMany(Tarefa::class,'tarefa_equipamento_projeto')->withTimestamps();
    }

    public $rules = [
        'observacao' =>  'nullable|max:20',
        'status' =>  'required|max:20',
        'equipamento_id' =>  'required|integer|exists:equipamentos,id',
        'projeto_cnme_id' =>  'required|integer|exists:projeto_cnmes,id'
    
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max' => 'No campo :attribute, o valor :input deve possuir no máximo :max caracteres',
        'unique' => 'Já existe um registro com :attribute igual a :input',
        'equipamento_id.exists' => 'Equipamento(equipamento_id) não encontrado',
        'projeto_cnme_id.exists' => 'Projeto CNME(projeto_cnme_id) não encontrado'
    ];
}
