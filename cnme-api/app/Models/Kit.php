<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kit extends Model
{

    use SoftDeletes;
    
    public const STATUS_ATIVO = 'ATIVO';
    public const STATUS_INATIVO =  'INATIVO';
    

    protected $fillable = [
        'nome','descricao','usuario_id'
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
        'usuario_id' => 'required|integer'
       
       
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'date' => 'O campo :attribute é um campo no formato de data'
    ];
}
