<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistCnme extends Model
{

    public const STATUS_ABERTO = 'ABERTO';
    public const STATUS_AVALIANDO = 'AVALIANDO';
    public const STATUS_OK = 'OK';
    public const STATUS_PENDENTE = 'PENDENTE';
    
    protected $fillable = ['descricao','avaliacao','status','projeto_cnme_id'];


    public function checklist(){
        return $this->belongsTo(Checklist::class); 
    }

    public function projetoCnme(){
        return $this->belongsTo(ProjetoCnme::class); 
    }

    public function itemChecklistCnmes(){
        return $this->hasMany(ItemChecklistCnme::class);
    }

    public $rules = [
        'descricao' =>  'required',
        'status' =>  'required|max:20',
        'projeto_cnme_id' =>  'required|integer',
    
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max' => 'No campo :attribute, o valor :input deve possuir no máximo :max caracteres',
        'unique' => 'Já existe um registro com :attribute igual a :input' 
    ];
}
