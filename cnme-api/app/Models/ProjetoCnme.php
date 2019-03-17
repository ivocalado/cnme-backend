<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Validation\Rule;
use App\Services\MailSender;

class ProjetoCnme extends Model
{

    public const STATUS_PLANEJAMENTO = 'PLANEJAMENTO';/**Projeto iniciado em planejamento mas equipamentos não foram enviados */
    public const STATUS_ENVIADO = 'ENVIADO';//Planejamento realizado e todos os equipamentos enviados;
    public const STATUS_ENTREGUE = 'ENTREGUE';//Equipamentos entregues;
    public const STATUS_INSTALADO = 'INSTALADO';//Produto entregue e instalado
    public const STATUS_ATIVADO = 'ATIVADO';//Instalado e ativado para operação
    public const STATUS_CANCELADO = 'CANCELADO';

    protected $fillable = [
        'id','numero', 'status','descricao',
        'unidade_id','usuario_id','solicitacao_cnme_id',
        'data_inicio_previsto',
        'data_fim_previsto',
        'data_inicio',
        'data_fim'
    ];

    public static function status(){
        return [
            ProjetoCnme::STATUS_PLANEJAMENTO, 
            ProjetoCnme::STATUS_ENVIADO, 
            ProjetoCnme::STATUS_ENTREGUE, 
            ProjetoCnme::STATUS_INSTALADO, 
            ProjetoCnme::STATUS_ATIVADO, 
            ProjetoCnme::STATUS_CANCELADO];
    }

    public function isPlanejamento(){
        return $this->status === ProjetoCnme::STATUS_PLANEJAMENTO;
    }

    public function isEnviado(){
        return $this->status === ProjetoCnme::STATUS_ENVIADO;
    }

    public function isEntregue(){
        return $this->status === ProjetoCnme::STATUS_ENTREGUE;
    }

    public function isInstalado(){
        return $this->status === ProjetoCnme::STATUS_INSTALADO;
    }

    public function isAtivado(){
        return $this->status === ProjetoCnme::STATUS_ATIVADO;
    }

    public function isCancelado(){
        return $this->status === ProjetoCnme::STATUS_CANCELADO;
    }

    public function isAndamento(){
        return $this->isEnviado() || $this->isEntregue() ||  $this->isInstalado();
    }

    public function isAberto(){
        return $this->isPlanejamento() || $this->isCancelado();
    }


    public static function checkStatus($status){
        return in_array($status, ProjetoCnme::status());
    }

    public function usuario(){
        return $this->belongsTo(User::class);
    }

    public function unidade(){
        return $this->belongsTo(Unidade::class);
    }

    public function solicitacaoCnme(){
        return $this->belongsTo(SolicitacaoCnme::class);
    }

    public function kit(){
        return $this->belongsTo(Kit::class);
    }

    public function etapas(){
        return $this->hasMany(Etapa::class);
    }

    public function tarefas(){
        return $this->hasManyThrough(Tarefa::class, Etapa::class);
    }

    public function equipamentoProjetos(){
        return $this->hasMany(EquipamentoProjeto::class);
    }

    public function notificar(){
        if($this->status === ProjetoCnme::STATUS_CANCELADO)
            MailSender::cancelamento($this);
    }

    public function recuperar(){
        MailSender::recuperar($this);
    }

    public function validate(){
        $messages = [];
        
        if($this->data_inicio > $this->data_inicio_previsto)
            $messages["infos"][] = "Projeto $this->numero teve início($this->data_inicio) após a data prevista($this->data_inicio_previsto).";
        
        if($this->data_fim > $this->data_fim_previsto)
            $messages["infos"][] = "Projeto $this->numero teve o fim($this->data_fim) posterior a data prevista($this->data_fim_previsto).";

        if(isset($this->data_fim) && $this->isAndamento())
            $messages["erros"][] = "Projeto $this->numero possui data de conclusão($this->data_fim) porém ainda está em andamento.";
        
        if($this->isAtivado() && !isset($this->data_fim))
            $messages["erros"][] = "Projeto $this->numero está concluído porém não tem data fim registrada.";
        
        if($this->isPlanejamento() && $this->data_inicio_previsto > date('Y-m-d') && $this->data_inicio == null)
            $messages["avisos"][] = "Projeto $this->numero está em planejamento porém já está atrasado segundo o cronograma. Conclusão prevista($this->data_fim_previsto)";
        
        if($this->isAndamento() && $this->data_fim_previsto < date('Y-m-d'))
            $messages["avisos"][] = "Projeto $this->numero está em $this->status porém já está atrasado segundo o cronograma. Conclusão prevista($this->data_fim_previsto)";


        $messageEtapas = $this->etapas->map(function ($e, $key){
            $result = $e->validate();
            return $result;
        });

        $errosEtapas = ($messageEtapas->pluck("erros")->filter()->all());
        foreach($errosEtapas as $k => $er)
            $messages["erros"][] = $er[0];
        
        $avisosEtapas = ($messageEtapas->pluck("avisos")->filter()->all());
        foreach($avisosEtapas as $k => $a)
            $messages["avisos"][] = $a[0];
        
        $infosEtapas = ($messageEtapas->pluck("avisos")->filter()->all());
        foreach($infosEtapas as $k => $i)
            $messages["avisos"][] = $i[0];
        
        
        return $messages;
        
    }

    public function validarDatasPrevistas(){
        $errors = array();
        $dataInicioProjetoPrevisto = $this->data_inicio_previsto;
        $dataFimProjetoPrevisto = $this->data_fim_previsto;

        $etapaEnvio = $this->getEtapaEnvio();
        $tarefasEnvio =  $etapaEnvio ?  $etapaEnvio->tarefas:[];

       
        if( $tarefasEnvio && $tarefasEnvio->isNotEmpty()){
            $dataInicioEnvio = $tarefasEnvio->min('data_inicio_prevista');
            $dataFimEnvio = $tarefasEnvio->max('data_fim_prevista');

            if($dataInicioEnvio > $dataFimEnvio){
                $errors['data_envio'] = "A data inicial do prazo previsto para a envio($dataInicioEnvio) deve ser anterior a data de final($dataFimEnvio).";
            }

            if($dataInicioProjetoPrevisto > $dataInicioEnvio){
                $errors['datas_envio_inicio'] = "A data prevista para o início do envio dos equipamentos($dataInicioEnvio) deve ser maior que a data de início de planejamento do projeto de implantação($dataInicioProjetoPrevisto).";
            }

            if($dataFimProjetoPrevisto < $dataFimEnvio){
                $errors['datas_envio_fim'] = "A data prevista para conclusão do envio dos equipamentos($dataFimEnvio) deve ser anterior a data planejada para o fim do processo de implantação($dataFimProjetoPrevisto).";
            }
        }

        $etapaInstalacao = $this->getEtapaInstalacao();
        $tarefasInstalacao = $etapaInstalacao ? $etapaInstalacao->tarefas:[];
        
        if( $tarefasInstalacao && $tarefasInstalacao->isNotEmpty()){
            $dataInicioInstalacao = $tarefasInstalacao->min('data_inicio_prevista');
            $dataFimInstalacao = $tarefasInstalacao->max('data_fim_prevista');

            if($dataInicioInstalacao > $dataFimInstalacao){
                $errors['data_instalacao'] = "A data inicial do prazo previsto para a instalação($dataInicioInstalacao) deve ser anterior a data de final($dataFimInstalacao).";
            }

            if($dataInicioInstalacao < $dataFimEnvio){
                $errors['data_instalacao_inicio'] = "A data inicial do prazo previsto para a instalação($dataInicioInstalacao) deve ser posterior a data de final prevista para entrega dos equipamentos($dataFimEnvio).";
            }
            
            if($dataFimInstalacao > $dataFimProjetoPrevisto){
                $errors['data_instalacao_fim'] = "A data final do prazo previsto para a instalação($dataFimInstalacao) deve ser anterior a data de final planejada para o fim do processo de implantação($dataFimProjetoPrevisto).";
            }

        }

        $etapaAtivacao = $this->getEtapaAtivacao();
        $tarefasAtivacao = $etapaAtivacao ? $etapaAtivacao->tarefas:[];
        if( $tarefasAtivacao && $tarefasAtivacao->isNotEmpty()){
            $dataInicioAtivacao = $tarefasAtivacao->min('data_inicio_prevista');
            $dataFimAtivacao = $tarefasAtivacao->max('data_fim_prevista');

            if($dataInicioAtivacao > $dataFimAtivacao){
                $errors['data_ativacao'] = "A data inicial do prazo previsto para a ativação($dataInicioAtivacao) deve ser anterior a data de final($dataFimAtivacao).";
            }

            if($dataInicioAtivacao < $dataFimInstalacao){
                $errors['data_ativacao_inicio'] = "A data inicial do prazo previsto para a ativação($dataInicioAtivacao) deve ser posterior a data de final prevista para instalação dos equipamentos($dataFimInstalacao).";
            }

            if($dataFimAtivacao > $dataFimProjetoPrevisto){
                $errors['data_ativacao_fim'] = "A data final do prazo previsto para a ativação($dataFimAtivacao) deve ser anterior a data de final planejada para o fim do processo de implantação($dataFimProjetoPrevisto).";
            }

        }

        return $errors;
    }
    
    public function desplanejar(){
        Etapa::where('projeto_cnme_id', $this->id)->delete();
    }


    public function getEtapaEnvio(){
        $etapa =  Etapa::where([
            ['projeto_cnme_id', $this->id],
            ['tipo', Etapa::TIPO_ENVIO]
            ])->first();
        
        return $etapa;
    }

    public function getEtapaInstalacao(){
        $etapa =  Etapa::where([
            ['projeto_cnme_id', $this->id],
            ['tipo', Etapa::TIPO_INSTALACAO]
            ])->first();
        
        return $etapa;
    }

    public function getEtapaAtivacao(){
        $etapa =  Etapa::where([
            ['projeto_cnme_id', $this->id],
            ['tipo', Etapa::TIPO_ATIVACAO]
            ])->first();
        
        return $etapa;
    }

    public function firstOrCreateEtapa($TIPO_ETAPA){
        $etapa =  Etapa::where([
            ['projeto_cnme_id', $this->id],
            ['tipo', $TIPO_ETAPA]
            ])->first();
        if($etapa)
            return $etapa;
        else {
            $etapa = new Etapa();
            $etapa->projetoCnme()->associate($this);
            $etapa->usuario()->associate($this->usuario);
            $etapa->status = Etapa::STATUS_ABERTA;
            $etapa->tipo = $TIPO_ETAPA;
            $etapa->descricao = 'Etapa de '.$TIPO_ETAPA.' dos equipamentos';

            $etapa->save();

            return $etapa;
        }
    }

    public function getEtapasPorTipo($tipo){
        $etapas =  Etapa::where([
            ['projeto_cnme_id', $this->id],
            ['tipo', strtoupper($tipo)]
            ])->get();
        return $etapas;
    }

    public $rules = [
        'numero'    =>  'required|unique:projeto_cnmes|max:20',
        'descricao' =>  'required',
        'usuario_id' => 'required|integer|exists:users,id',
        'unidade_id' => 'required|integer|exists:unidades,id',
        'solicitacao_cnme_id' => 'nullable|integer',
        'data_inicio_previsto' => 'required|date|before_or_equal:data_fim_previsto',
        'data_fim_previsto' => 'required|date|after_or_equal:data_inicio_previsto',
        'data_inicio' => 'nullable|date|before_or_equal:data_fim',
        'data_fim' => 'nullable|date|after_or_equal:data_inicio',       
    ];

    public $messages = [
        'required' => 'O campo :attribute é obrigatório',
        'integer' => 'O campo :attribute deve ser um inteiro',
        'date' => 'O campo :attribute é um campo no formato de data',
        'usuario_id.exists' => 'Usuário(usuario_id) não encontrado',
        'unidade_id.exists' => 'Unidade(unidade_id) não encontrada'
    ];
}
