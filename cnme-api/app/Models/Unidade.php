<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\User;

class Unidade extends Model
{

    public const CLASSE_ADMIN = 'admin';
    public const CLASSE_MEC = 'mec';
    public const CLASSE_TVESCOLA = 'tvescola';
    public const CLASSE_POLO = 'polo';
    public const CLASSE_EMPRESA = 'empresa';

    public static function classes(){
        return [
            Unidade::CLASSE_ADMIN,
            Unidade::CLASSE_MEC,
            Unidade::CLASSE_TVESCOLA,
            Unidade::CLASSE_POLO,
            Unidade::CLASSE_EMPRESA,
        ];
    }

    protected $fillable = [
        'id','nome', 'email','email_institucional', 'codigo_inep', 'diretor', 'telefone', 'url','localidade_id','tipo_unidade_id','responsavel_id'
    ];

    public function projetoCnme(){
        return $this->hasOne(ProjetoCnme::class);
    }

    public function tipoUnidade(){
        return $this->belongsTo(TipoUnidade::class);
    }

    public function localidade(){
        return $this->belongsTo(Localidade::class);
    }

    public function responsavel(){
        return $this->belongsTo(User::class,'responsavel_id');
    }

    public function usuarios(){
        return $this->hasMany(User::class, 'unidade_id', 'id');
    }

    public function isPolo(){
        return $this->classe === Unidade::CLASSE_POLO;
    }

    public function isEmpresa(){
        return $this->classe === Unidade::CLASSE_EMPRESA; 
    }

    public function isMec(){
        return $this->classe === Unidade::CLASSE_MEC;
    }

    public function isTvEscola(){
        return $this->classe === Unidade::CLASSE_TVESCOLA;
    }

    public function isGestora(){
        return $this->isMec() || $this->isTvEscola();
    }

    public $rules = [
        'nome'    =>  'required|unique:unidades|max:255',
        'email'    =>  'required|unique:unidades|email|max:255',
        'codigo_inep'       =>  'nullable|unique:unidades|size:8',
        'classe'            =>  'nullable|max:20',
        'diretor'   => 'nullable',
        'telefone'   => 'nullable',
        'url'   => 'nullable|url|max:255',
        'tipo_unidade_id' => 'required|integer'
        //'responsavel_id' => 'required|integer'
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'email' => 'Esse campo deve possuir um email válido',
        'unique' => 'Já existe um registro com :attribute igual a :input',
        'url' => 'O campo :attribute deve possuir um endereço(url) válido',
        'active_url' => 'O campo :attribute deve possuir um endereço(url) válido',
        'codigo_inep.size' => 'O código INEP deve possuir :size caracteres',
        'tipo_unidade_id'  => 'Um tipo de unidade deve ser determinado',
        'responsavel_id'   => 'Um responsável da unidade deve ser determinado'
        
    ];
}
