<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Models\Unidade;

class User extends Authenticatable
{

    public const TIPO_GESTOR = 'gestor';
    public const TIPO_COLABORADOR = 'colaborador';
    public const TIPO_ADMINISTRADOR = 'administrador';
    public const TIPO_TERCEIRO = 'terceiro';

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','telefone','cpf','funcao', 'tipo','unidade_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function unidade(){
        return $this->belongsTo(Unidade::class);
    }

    public $rules = [
        'name'          =>  'required|unique:users|max:255',
        'email'         =>  'required|unique:users|max:255|email',
        'password'      =>  'required',
        'cpf'           =>  'required|unique:users',
        'telefone'      =>  'nullable|max:50',
        'unidade_id'    =>  'required|integer',
        'tipo'          =>  'required|max:20'

       
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'email' => 'Esse campo deve possuir um email válido',
        'unique' => 'Já existe um registro com :attribute igual a :input',
        'max'   => 'O campo :attribute deve ter até :max caracteres'
    ];
}
