<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemChecklistCnme extends Model
{
    protected $fillable = ['descricao','status', 'observacao','checklist_cnme_id','item_checklist_id'];


    public function checklistCnme(){
        return $this->belongsTo(ChecklistCnme::class); 
    }

    public function itemChecklistCnme(){
        return $this->belongsTo(ItemChecklistCnme::class); 
    }


    public $rules = [
        'descricao' =>  'nullable|max:255',
        'status' =>  'required|max:20',
        'observacao' =>  'nullable|max:20',
        'checklist_cnme_id' =>  'required|integer',
        'item_checklist_id' =>  'required|integer',
    
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max' => 'No campo :attribute, o valor :input deve possuir no máximo :max caracteres',
        'unique' => 'Já existe um registro com :attribute igual a :input' 
    ];
}
