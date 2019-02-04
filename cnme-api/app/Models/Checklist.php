<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Checklist extends Model
{
    protected $fillable = ['versao', 'descricao','usuario_id','ativo'];

    protected $table = "checklists";

    public function usuario(){
        return $this->belongsTo(User::class); 
    }

    public function itemChecklists(){
        return $this->hasMany(ItemChecklist::class);
    }

    public $rules = [
        'versao'    =>  'required|unique:checklists|max:100',
        'descricao'    =>  'required|max:255',
        'usuario_id'  => 'integer|required',
        'ativo' => 'required'
    
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max' => 'No campo :attribute, o valor :input deve possuir no máximo :max caracteres',
        'unique' => 'Já existe um registro com :attribute igual a :input' 
    ];
}
