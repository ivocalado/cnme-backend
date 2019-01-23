<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Kit extends Model
{


    public const STATUS_ATIVO = 'ATIVO';
    public const STATUS_INATIVO =  'INATIVO';
    

    protected $fillable = [
        'nome','descricao','versao','status','usuario_id','data_inicio','data_fim'
     ];

     public function usuario(){
        return $this->belongsTo(User::class);
    }

    public function equipamentos()
    {
        return $this->belongsToMany(Equipamento::class, 'kit_equipamento');
    }

    public $rules = [
        'nome'    =>  'required|max:255',
        'descricao'    =>  'nullable|max:255',
        'status'    =>  'required:max:100',
        'versao'    =>  'nullable:max:100',

        'usuario_id' => 'required|integer',
        'data_inicio' => 'required|date',
        'data_fim' => 'nullable|date'
       
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'date' => 'O campo :attribute é um campo no formato de data'
    ];
}
