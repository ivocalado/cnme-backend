<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Services\MailSender;

class Etapa extends Model
{
    public const STATUS_ABERTA = 'ABERTA';
    public const STATUS_ANDAMENTO = 'ANDAMENTO';
    public const STATUS_CONCLUIDA = 'CONCLUIDA';
    public const STATUS_CANCELADA = 'CANCELADA';

    public const TIPO_ENVIO = 'ENVIO';
    public const TIPO_INSTALACAO = 'INSTALACAO';
    public const TIPO_ATIVACAO = 'ATIVACAO';


    public const DESC_ETAPA_ENVIO = 'Etapa de ENVIO dos equipamentos';
    public const DESC_ETAPA_INSTALACAO = 'Etapa de INSTALAÇÃO dos equipamentos';
    public const DESC_ETAPA_ATIVACAO = 'Etapa de ATIVAÇÃO dos equipamentos';

    protected $fillable = [
       'status','descricao','tipo','usuario_id','projeto_cnme_id'
       //'data_inicio','data_fim','data_inicio_prevista','data_fim_prevista'
    ];

    public static function status(){
        return [
            Etapa::STATUS_ABERTA,
            Etapa::STATUS_ANDAMENTO,
            Etapa::STATUS_CONCLUIDA,
            Etapa::STATUS_CANCELADA,
        ];
    }

    public static function checkStatus($status){
        return in_array($status, Etapa::status());
    }

    public static function tipos(){
        return [
            Etapa::TIPO_ENVIO,
            Etapa::TIPO_INSTALACAO,
            Etapa::TIPO_ATIVACAO
        ];
    }

    public static function checkTipo($tipo){
        return in_array(strtoupper($tipo), Etapa::tipos());
    }

    public function usuario(){
        return $this->belongsTo(User::class);
    }

    public function projetoCnme(){
        return $this->belongsTo(ProjetoCnme::class);
    }

    public function tarefas()
    {
        return $this->hasMany(Tarefa::class);
    }

    public function getDataInicio(){
        return $this->tarefas->max('data_inicio');
    }


    public function getDataFim(){
        return $this->tarefas->max('data_fim');
    }

    public function getDataInicioPrevista(){
        return $this->tarefas->max('data_inicio_prevista');
    }


    public function getDataFimPrevista(){
        return $this->tarefas->max('data_fim_prevista');
    }

    public function notificarEnviarTodos(){
        MailSender::notificarEnviarTodos($this->projetoCnme);
    }

    public function instalar(){
        $this->status = Etapa::STATUS_CONCLUIDA;
        $this->save();

        $this->projetoCnme->status = ProjetoCnme::STATUS_INSTALADO;
        $this->projetoCnme->save();

        
        $etapaAtivacao = $this->projetoCnme->getEtapaAtivacao();
        $etapaAtivacao->status = Etapa::STATUS_ANDAMENTO;
        $etapaAtivacao->save();

        $tarefaAtivacao = $etapaAtivacao->getFirstTarefa();
        $tarefaAtivacao->status = Tarefa::STATUS_ANDAMENTO;
        $tarefaAtivacao->data_inicio = $this->getFirstTarefa()->data_fim;
        $tarefaAtivacao->save();

        $this->projetoCnme->equipamentoProjetos->each(function($eP, $value){
            if($eP->status === EquipamentoProjeto::STATUS_ENTREGUE){
                $eP->status = EquipamentoProjeto::STATUS_INSTALADO;
                $eP->save();
            }  
        });
    }

    public function ativar(){
        $this->status = Etapa::STATUS_CONCLUIDA;
        $this->save();

        $tarefaAtivacao = $this->getFirstTarefa();

        $this->projetoCnme->status = ProjetoCnme::STATUS_ATIVADO;
        $this->projetoCnme->data_fim = $tarefaAtivacao->data_fim;
        $this->projetoCnme->save();

        $this->projetoCnme->equipamentoProjetos->each(function($eP, $value){
            if($eP->status === EquipamentoProjeto::STATUS_INSTALADO){
                $eP->status = EquipamentoProjeto::STATUS_ATIVADO;
                $eP->save();
            }  
        });
    }

    public function equipamentos()
    {
        $ids =  $this->tarefas()->pluck('id');
        $equipamentosProjetos = EquipamentoProjeto::whereHas('tarefas', function($query) use ($ids)
        {
            $query->whereIn('id', $ids);
        })
        ->with('tarefas')
        ->get();

        return  $equipamentosProjetos;
    }

    public function hasTarefasAbertasAndamento(){
        $andamento =  $this->tarefas->contains('status',Tarefa::STATUS_ANDAMENTO);
        $abertas = $this->tarefas->contains('status',Tarefa::STATUS_ABERTA);

        return $andamento || $abertas;
    }

    public function getFirstTarefa(){
        $tarefa =  Tarefa::where([
            ['etapa_id', $this->id],
            ])->first();
       
        return $tarefa;
    }


    public function firstOrCreateTarefa(){
        $tarefa =  Tarefa::where([
            ['etapa_id', $this->id],
            ])->first();
        if($tarefa)
            return $tarefa;
        else {
            $tarefa = new Tarefa();
            $tarefa->status = Tarefa::STATUS_ABERTA;
            $tarefa->etapa()->associate($this);
            return $tarefa;
        }
    }
    public $rules = [
        'descricao'    =>  'required|max:255',
        'status'    =>  'required',
        'tipo' =>  'required',
        'usuario_id' => 'required|integer|exists:users,id',
        'projeto_cnme_id' => 'required|integer|exists:projeto_cnmes,id'
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'date' => 'O campo :attribute é um campo no formato de data',
        'projeto_cnme_id.exists' => 'Projeto CNME(projeto_cnme_id) não encontrado',
        'usuario_id.exists' => 'Usuário(usuario_id) não encontrado'
    ];
}
