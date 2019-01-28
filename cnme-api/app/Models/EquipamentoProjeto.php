<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipamentoProjeto extends Model
{

    public const STATUS_DISPONIVEL = "DISPONIVEL";
    public const STATUS_PROJETO = "PROJETO";

    protected $fillable = ['detalhes','observacao','status','equipamento_id','projeto_cnme_id'];


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
        'equipamento_id' =>  'required|integer',
        'projeto_cnme_id' =>  'required|integer',
    
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max' => 'No campo :attribute, o valor :input deve possuir no máximo :max caracteres',
        'unique' => 'Já existe um registro com :attribute igual a :input' 
    ];
}
