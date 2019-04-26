<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checklist extends Model
{
    protected $fillable = ['versao', 'descricao'];

    protected $table = "checklists";

    use SoftDeletes;

    public function usuario(){
        return $this->belongsTo(User::class); 
    }

    public function projetoCnmes(){
        return $this->hasMany(ProjetoCnme::class);
    }

    public $rules = [
        'versao'    =>  'required|unique:checklists|max:100',
        'descricao'    =>  'required'
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max' => 'No campo :attribute, o valor :input deve possuir no máximo :max caracteres',
        'unique' => 'Já existe um registro com :attribute igual a :input' 
    ];
}
