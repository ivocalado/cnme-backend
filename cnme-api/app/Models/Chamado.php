<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Event;

class Chamado extends Model
{

    protected $fillable = [
        'assunto', 'descricao', 'projeto_cnme_id', 'tarefa_id',
        'usuario_responsavel_id', 'unidade_responsavel_id', 'data_inicio', 'data_fim',
        'status_id', 'tipo_id', 'prioridade', 'privado'
    ];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'comment');
    }

    public function projetoCnme(){
        return $this->belongsTo(ProjetoCnme::class);
    }

    public function tarefa(){
        return $this->belongsTo(Tarefa::class)->withTrashed();
    }

    public function usuario(){
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function usuarioResponsavel(){
        return $this->belongsTo(User::class);
    }

    public function unidadeResponsavel(){
        return $this->belongsTo(Unidade::class);
    }

    public function status(){
        return $this->belongsTo(StatusChamado::class,'status_id');
    }

    public function tipo(){
        return $this->belongsTo(TipoChamado::class,'tipo_id');
    }

    public $rules = [
        'assunto'    =>  'required',
        'descricao'    =>  'required',
        'projeto_cnme_id'   => 'integer|required|exists:projeto_cnmes,id',
        'tarefa_id'   => 'integer|nullable|exists:tarefas,id',
        'usuario_id'   => 'integer|required|exists:users,id',
        'usuario_responsavel_id'   => 'integer|nullable|exists:users,id',
        'unidade_responsavel_id'   => 'integer|nullable|exists:unidades,id',
        'data_inicio' => 'nullable|date|before_or_equal:data_fim',
        'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        'status_id'   => 'integer|exists:status_chamados,id',
        'tipo_id'   => 'integer|exists:tipo_chamados,id',
        'prioridade'    => 'integer|nullable',
        'privado'    => 'boolean|nullable',
    ];

    public $messages = [
    ];

    public function validar(){
        $errors = array();

        if(!isset($this->usuario_responsavel_id) && !isset($this->unidade_responsavel_id)){
            $errors['messages']['usuario_responsavel'][] = 'Um usuário responsável ou uma unidade responsável deve ser definida.';
        }
    }

    public static function boot() {
        parent::boot();

        static::updated(function($chamado) {
	        Event::fire('chamado.updated', $chamado);
        });
        
        static::created(function($chamado) {
	        Event::fire('chamado.created', $chamado);
	    });
    }
}
