<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Services\MailSender;

class Tarefa extends Model
{
    public const STATUS_ABERTA = 'ABERTA';
    public const STATUS_ANDAMENTO = 'ANDAMENTO';
    public const STATUS_CONCLUIDA = 'CONCLUIDA';
    public const STATUS_CANCELADA = 'CANCELADA';

    public const TIPO_ENVIO = 'ENVIO';
    public const TIPO_ENTREGA = 'ENTREGA';
    public const TIPO_INSTALACAO = 'INSTALACAO';
    public const TIPO_SUPORTE = 'SUPORTE';
    public const TIPO_ATIVACAO = 'ATIVACAO';

    public const DESC_TAREFA_ENVIO = "Envio dos equipamentos";
    public const DESC_TAREFA_INSTALACAO = "Instalação dos equipamentos";
    public const DESC_TAREFA_ATIVACAO = "Ativaçãos dos equipamentos";

    protected $fillable = [
        'nome','descricao','numero','status','link_externo',
        'data_inicio_prevista','data_fim_prevista',
        'data_inicio', 'data_fim',
        'etapa_id','usuario_id','responsavel_id','unidade_responsavel_id'
    ];

    public static function status(){
        return [
            Tarefa::STATUS_ABERTA,
            Tarefa::STATUS_ANDAMENTO,
            Tarefa::STATUS_CONCLUIDA,
            Tarefa::STATUS_CANCELADA,
        ];
    }

    public function isAberta(){
        return $this->status === Tarefa::STATUS_ABERTA;
    }

    public function isAndamento(){
        return $this->status === Tarefa::STATUS_ANDAMENTO;
    }

    public function isConcluida(){
        return $this->status === Tarefa::STATUS_CONCLUIDA;
    }

    public function isEnvio(){
        return $this->etapa->isEnvio();
    }

    public function isInstalacao(){
        return $this->etapa->isInstalacao();
    }

    public function isAtivacao(){
        return $this->etapa->isAtivacao();
    }

    public function tipo(){
        return $this->etapa->tipo;
    }

    public function getTipo(){
        return $this->etapa->tipo;
    }

    public function usuario(){
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function etapa(){
        return $this->belongsTo(Etapa::class);
    }

    public function responsavel(){
        return $this->belongsTo(User::class,'responsavel_id')->withTrashed();
    }

    public function unidadeResponsavel(){
        return $this->belongsTo(Unidade::class,'unidade_responsavel_id');
    }

    public function equipamentosProjetos(){
        return $this->belongsToMany(EquipamentoProjeto::class,'tarefa_equipamento_projeto')->withTimestamps();
    }

    public function validate(){
        $messages = [];

        $reponsavel = isset($this->unidadeResponsavel) ? "(".$this->unidadeResponsavel->nome.")." : "";
        //.(isset($this->responsavel)) ? "Usuário: ".$this->responsavel->name:".";

        if($this->isConcluida() && $this->data_fim > $this->data_fim_prevista){
            $msg = "Data fim($this->data_fim) da tarefa de ".$this->tipo()." foi posterior a data fim planejada($this->data_fim_prevista).".$reponsavel;
            $dateInterval = (new \DateTime($this->data_fim))->diff(new \DateTime($this->data_fim_prevista));
            $msg =  $msg." Houve um atraso de ".$dateInterval->days." dias.";
            $messages["infos"][] = $msg;
        }
            

        if($this->data_inicio > $this->data_inicio_prevista){
            $msg = "Data início($this->data_inicio) da tarefa ".$this->tipo()." foi posterior a data início planejada($this->data_inicio_prevista).".$reponsavel;
            $dateInterval = (new \DateTime($this->data_inicio))->diff(new \DateTime($this->data_inicio_prevista));
            $msg =  $msg." Houve um atraso de ".$dateInterval->days." dias.";
            $messages["infos"][] = $msg;
        }
            
            
        if($this->isConcluida() && !isset($this->data_fim))
            $messages["erros"][] = "A tarefa de ".$this->tipo()." está concluída mas não tem data fim.";
        
        if($this->isAndamento() && !isset($this->data_inicio))
            $messages["erros"][] = "A tarefa de ".$this->tipo()." está em andamento mas não tem data início.";

        if($this->isAndamento() && $this->data_fim_prevista < date('Y-m-d') ){
            $messages["avisos"][] = "A tarefa de ".$this->tipo()." está em atrasada para ser concluída em relação ao cronograma inicial($this->data_fim_prevista).".$reponsavel;
        }

        if($this->isConcluida() && $this->data_fim < $this->data_inicio)
            $messages["erros"][] = "A tarefa de ".$this->tipo()." está com data início posterior a data início.";
        
        if($this->isAberta() && $this->data_inicio_prevista < date('Y-m-d')){
            $msg = "A tarefa de ".$this->tipo()." está com seu início atrasado em relação ao cronograma inicial($this->data_inicio_prevista).";
            $dateInterval = (new \DateTime(date('Y-m-d')))->diff(new \DateTime($this->data_inicio_prevista));
            $msg =  $msg." Há um atraso de ".$dateInterval->days." dias.".$reponsavel;
            $messages["avisos"][] = $msg;
        }
           
        
        if($this->isEnvio() && ($this->isAndamento() || $this->isConcluida()) &&  !isset($this->numero))
            $messages["avisos"][] = "A tarefa de ENVIO está com status $this->status mas não informou número de rastreio.".$reponsavel;
        
        return $messages;
       

    }

    public function notificar(){
        $projeto = ProjetoCnme::find($this->etapa->projeto_cnme_id);
        MailSender::notificar($projeto, $this);
        $this->notificado_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function enviar(){
        $this->status = Tarefa::STATUS_ANDAMENTO;

        if(!isset($this->data_inicio))
            $this->data_inicio = date("Y-m-d");

        $etapa = $this->etapa;
        $projeto = $etapa->projetoCnme;

        if($projeto->status === ProjetoCnme::STATUS_PLANEJAMENTO){
            $projeto->status = ProjetoCnme::STATUS_ENVIADO;
            $projeto->data_inicio = date("Y-m-d");
            $projeto->save();
        }

        $etapa->status = Etapa::STATUS_ANDAMENTO;
        $etapa->save();

        $this->equipamentosProjetos->each(function ($eP, $key) {
            if($eP->status === EquipamentoProjeto::STATUS_PLANEJADO){
                $eP->status = EquipamentoProjeto::STATUS_ENVIADO;
                $eP->save();
            }      
        });

        $this->save();

        return $etapa;
    }

    public function entregar(){
        $this->status = Tarefa::STATUS_CONCLUIDA;

        if(!isset($this->data_fim))
            $this->data_fim = date("Y-m-d");
        
        $this->equipamentosProjetos->each(function($eP, $value){
            if($eP->status === EquipamentoProjeto::STATUS_ENVIADO){
                $eP->status = EquipamentoProjeto::STATUS_ENTREGUE;
                $eP->save();
            }
            
        });

        $this->save();

        $etapa = $this->etapa;
        $projeto = $etapa->projetoCnme;

        $temEntregasAndamento = $etapa->hasTarefasAbertasAndamento();

        if(!$temEntregasAndamento){
            $etapa->status = Etapa::STATUS_CONCLUIDA;
            $etapa->save();

            $projeto->status = ProjetoCnme::STATUS_ENTREGUE;
            $projeto->save();

            $etapaInstalacao = $projeto->getEtapaInstalacao();
            $etapaInstalacao->status = Etapa::STATUS_ANDAMENTO;
            $etapaInstalacao->save();

            $tarefaInstalacao = $etapaInstalacao->getFirstTarefa();
            $tarefaInstalacao->status = Tarefa::STATUS_ANDAMENTO;
            $tarefaInstalacao->data_inicio = $this->data_fim;
            $tarefaInstalacao->save();

        }

        
    }

    public function instalar(){
       
        if(!isset($this->data_inicio))
            $this->data_inicio = date("Y-m-d");

        if(!isset($this->data_fim))
            $this->data_fim = date("Y-m-d");

        $this->status = Tarefa::STATUS_CONCLUIDA;
        $this->save();


        $this->etapa->instalar();
        
    }

    public function ativar(){
        if(!isset($this->data_inicio))
            $this->data_inicio = date("Y-m-d");

        $this->data_fim = date("Y-m-d");

        $this->status = Tarefa::STATUS_CONCLUIDA;
        $this->save();


        $this->etapa->ativar();
    }

    public $rules = [
        'nome'    =>  'required|max:255',
        'numero' => 'max:30',
        'link_externo' => 'max:400',
        'status'    =>  'required:max:50',
        'usuario_id' => 'required|integer|exists:users,id',
        'etapa_id' => 'required|integer|exists:etapas,id',
        'responsavel_id' => 'integer|exists:users,id',
        'unidade_responsavel_id' => 'integer|required|exists:unidades,id',
        'data_inicio' => 'nullable|date|before_or_equal:data_fim',
        'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        'data_inicio_prevista' => 'required|date|before_or_equal:data_fim_prevista',
        'data_fim_prevista' => 'required|date|after_or_equal:data_inicio_prevista',
       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'date' => 'O campo :attribute é um campo no formato de data'
    ];


}
