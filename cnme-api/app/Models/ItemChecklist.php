<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemChecklist extends Model
{

    public const TIPO_EQUIPAMENTO = 'EQUIPAMENTO';
    public const TIPO_INFRAESTRUTURA = 'INFRAESTRUTURA';


    protected $table = 'itens_checklist';

    protected $fillable = ['descricao','equipamento_id','tipo'];

    public function checklist(){
        return $this->belongsTo(Checklist::class,'checklist_id');
    }

    public function equipamento(){
        return $this->belongsTo(Equipamento::class);
    }

    public $rules = [
        'checklist_id'    =>  'required|integer',
        'equipamento_id'    =>  'nullable|integer',
        'descricao'    =>  'required|max:255',
        'tipo' => 'required|max:100'
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'max' => 'No campo :attribute, o valor :input deve possuir no máximo :max caracteres'
    ];
}
