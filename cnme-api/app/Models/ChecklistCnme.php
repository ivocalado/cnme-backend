<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistCnme extends Model
{
    protected $fillable = ['descricao','status','checklist_id','projeto_cnme_id'];


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
        'descricao' =>  'required|max:255',
        'status' =>  'required|max:20',
        'checklist_id' =>  'required|integer',
        'projeto_cnme_id' =>  'required|integer',
    
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max' => 'No campo :attribute, o valor :input deve possuir no máximo :max caracteres',
        'unique' => 'Já existe um registro com :attribute igual a :input' 
    ];
}
