<?php

use Illuminate\Database\Seeder;
use App\Models\Kit;
use Illuminate\Support\Facades\DB;
use App\Models\Equipamento;
use App\Models\TipoUnidade;
use App\Models\Unidade;
use App\User;
use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Localidade;
use App\Models\ProjetoCnme;
use App\Models\Etapa;
use App\Models\Tarefa;
use App\Services\UnidadeService;
use App\Models\EquipamentoProjeto;
use App\Models\Checklist;

class DataBaseTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function createKit(){
        //DB::table('kits')->delete();
        $kit = Kit::create([
            "nome" => "Kit de testes",
            "descricao" => "kit criado com o objetivo de testar o sistema.",
            "usuario_id" => 2
        ]);

        $equipamentoIds = Equipamento::all()->pluck('id');
        $kit->equipamentos()->attach($equipamentoIds);
        $kit->save();
    }

    private function createPoloUF(){
        $estados = Estado::all();
        $tipoPolo = TipoUnidade::where('classe', Unidade::CLASSE_POLO)->first();
        foreach($estados as $e){

            for($i = 1; $i<=3; $i++){
                $nome = strtolower( preg_replace("/[^a-zA-Z0-9-]/", "-", strtr(utf8_decode(trim($e->nome)), utf8_decode("áàãâéêíóôõúüñçÁÀÃÂÉÊÍÓÔÕÚÜÑÇ"),"aaaaeeiooouuncAAAAEEIOOOUUNC-")) );
                $nome .= " ".$i;
                $polo = Unidade::create([
                    'nome' => 'Polo '.$e->nome."(".$i.")", 
                    'descricao' => 'Escola de Teste '.$e->nome."(".$i.")",
                    'classe' => Unidade::CLASSE_POLO,
                    'admin' => false,
                    'email' => strtolower($nome).'@poloteste.com.br',
                    'email_institucional' => strtolower($nome).'_faleconosco@polo.com.br',
                    'url' => 'https://www.'.$nome.'.gov.br/',
                    'diretor' => 'Contato DIretor',
                    'telefone' => '(0300 34234233)',
                    'tipo_unidade_id' => $tipoPolo->id
                ]);

                $local = Localidade::create([
                    'logradouro' => 'Lugar nenhum', 
                    'numero' => 'sn',
                    'bairro' => 'Setor Planejado',
                    'cep' => '99999-999',
                    'estado_id' => $e->id,
                    'municipio_id' => $e->municipios->first()->id
                ]);

                $polo->localidade()->associate( $local );
                $polo->save();


                $gestorPolo = User::create([
                    'name' => 'Gestor do Polo '.$e->nome, 
                    'email' => $polo->email,
                    'password' => Hash::make('dasda623asda2'),
                    'telefone' => $polo->telefone,
                    'cpf' => '999.999.999-'.$e->id,
                    'funcao' => 'Diretor',
                    'tipo' => User::TIPO_GESTOR,
                    'unidade_id' => $polo->id
                ]);

                $polo->responsavel()->associate($gestorPolo);
                $polo->usuarioChamados()->associate($gestorPolo);
                $polo->save();

            }
        }
    }

    public function createProjetoUf(){
        $administrador = User::where('tipo','administrador')->first();

        $unidades = Unidade::where('tipo_unidade_id',3)->doesnthave('projetoCnme')->get();
        foreach($unidades as $u){
            $projeto = new ProjetoCnme();
            $projeto->numero = '00000'.$u->id;
            $projeto->descricao = 'Projeto Teste da Unidade '.$u->nome;
            $projeto->status = ProjetoCnme::STATUS_PLANEJAMENTO;
            $projeto->unidade()->associate( $u );
            $projeto->usuario()->associate( $administrador );

            $startInicio = strtotime("2019-01-01");
            $endInicio = strtotime("2019-06-30");
            $randInicio = mt_rand($startInicio, $endInicio);
            $projeto->data_inicio_previsto =  date("Y-m-d", $randInicio);
            $endFinal = strtotime("2019-12-31");
            $projeto->data_fim_previsto = date("Y-m-d", mt_rand($randInicio, $endFinal));

            $kit = Kit::all()->random();

            $projeto->kit()->associate($kit);

            $checklist = Checklist::first();

            $projeto->checklist()->associate($checklist);
            $projeto->checklist_at = date("Y-m-d H:i:s");
            $projeto->save();


            $equipamentos = $kit->equipamentos;

            foreach($equipamentos as $q){
                $equipamentoProjeto = new EquipamentoProjeto();
                $equipamentoProjeto->equipamento()->associate($q);
                $equipamentoProjeto->projetoCnme()->associate($projeto);
                $equipamentoProjeto->status = EquipamentoProjeto::STATUS_PLANEJADO;

                $equipamentoProjeto->save();
            }

            
            

            $this->createEtapaEnvio($projeto);
            $this->createEtapaInstalacao($projeto);
            $this->createEtapaAtivacao($projeto);

            $numero = rand(1, 100);

            if( $numero >= 20)
                $tarefaEnvio = $this->enviar($projeto);
            if( $numero >= 30)
                $this->entregarProjeto($projeto, $tarefaEnvio);
            if( $numero >= 40)  
                $this->instalarProjeto($projeto); 
            if( $numero >= 50) 
                $this->ativarProjeto($projeto);
            
        }
    }

    private function ativarProjeto($projeto){
        $etapaAtivacao = $projeto->getEtapaAtivacao();
        $tarefaAtivacao = $etapaAtivacao->getFirstTarefa();


        $etapaInstalacao = $projeto->getEtapaInstalacao();
        $tarefaInstalacao = $etapaInstalacao->getFirstTarefa();
        $dateTempIni = strtotime($tarefaInstalacao->data_fim);
        
        
        $dateTempFim = strtotime(date('Y-m-d', strtotime($tarefaAtivacao->data_fim_prevista. ' + 10 days')));
        $randFim = mt_rand($dateTempIni, $dateTempFim );
        $tarefaAtivacao->data_fim = date("Y-m-d", $randFim);


        $tarefaAtivacao->status = Tarefa::STATUS_CONCLUIDA;
        $tarefaAtivacao->save();

        $etapaAtivacao->status = Etapa::STATUS_CONCLUIDA;
        $etapaAtivacao->save();

        $projeto->status = ProjetoCnme::STATUS_ATIVADO;
        $projeto->data_fim = date('Y-m-d');
        $projeto->save();

        $projeto->equipamentoProjetos->each(function($eP, $value){
            $eP->status = EquipamentoProjeto::STATUS_ATIVADO;
            $eP->save();
        });
    }

    private function instalarProjeto($projeto){
        $etapaInstalacao = $projeto->getEtapaInstalacao();
        $tarefaInstalacao = $etapaInstalacao->getFirstTarefa();

        $tarefaInstalacao->instalar();
    }

    private function entregarProjeto($projeto, $tarefa){
        $dateTempIni = strtotime($tarefa->data_inicio);
        $dateTempFim = strtotime(date('Y-m-d', strtotime($tarefa->data_fim_prevista. ' + 10 days')));
        $randFim = mt_rand($dateTempIni, $dateTempFim );
        $tarefa->data_fim = date("Y-m-d", $randFim);
        $tarefa->entregar();
    }

    private function enviar($projeto){
        $etapaEnvio = $projeto->getEtapaEnvio();
        $tarefaEnvio = $etapaEnvio->getFirstTarefa();

        $dateTempIni = strtotime($tarefaEnvio->data_inicio_prevista);
        $dateTempFim = strtotime(date('Y-m-d', strtotime($tarefaEnvio->data_fim_prevista. ' + 10 days')));
        $randFim = mt_rand($dateTempIni, $dateTempFim );

        $tarefaEnvio->data_inicio = date("Y-m-d", $randFim);
        $tarefaEnvio->descricao = "Geração automátic de envio em ".date("Y-m-d", $randFim);

        $etapaEnvio = $tarefaEnvio->enviar();
        $etapaEnvio->status = Etapa::STATUS_ANDAMENTO;
        $etapaEnvio->save();

        return $tarefaEnvio;
    }

    private function createEtapaAtivacao($projeto){

        $etapaAtivacao = $projeto->firstOrCreateEtapa(Etapa::TIPO_ATIVACAO);
        $tarefaAtivacao = $etapaAtivacao->firstOrCreateTarefa();

        $tarefaAtivacao->etapa_id = $etapaAtivacao->id;
        $tarefaAtivacao->nome = Tarefa::DESC_TAREFA_ATIVACAO;

        $etapaInstalacao = $projeto->getEtapaInstalacao();
        $tarefaInstalacao = $etapaInstalacao->getFirstTarefa();

        $tarefaAtivacao->numero = $tarefaInstalacao->numero;
        $tarefaAtivacao->usuario_id = $tarefaInstalacao->usuario_id;

        $unidadeService = new UnidadeService();

        $tarefaAtivacao->unidade_responsavel_id = $unidadeService->tvescola()->id;
       
        $tarefaAtivacao->data_inicio_prevista = date('Y-m-d', strtotime($tarefaInstalacao->data_fim_prevista. ' + 1 days'));
        
        $dateTempIni = strtotime($tarefaAtivacao->data_inicio_prevista);

        
        $dateTemp2 = date('Y-m-d', strtotime($tarefaAtivacao->data_inicio_prevista. ' + 10 days'));
        $dateTempFim = strtotime($dateTemp2);

       
        $randFim = mt_rand($dateTempIni,  $dateTempFim);
        $tarefaAtivacao->data_fim_prevista  =  date("Y-m-d", $randFim);
         

        $etapaAtivacao->tarefas()->save($tarefaAtivacao);
    }

    private function createEtapaInstalacao($projeto){
        $etapaInstalacao = $projeto->firstOrCreateEtapa(Etapa::TIPO_INSTALACAO);
        $tarefaInstalacao = $etapaInstalacao->firstOrCreateTarefa();

        $tarefaInstalacao->etapa_id = $etapaInstalacao->id;
        $tarefaInstalacao->nome = Tarefa::DESC_TAREFA_INSTALACAO;

        $etapaEnvio = $projeto->getEtapaEnvio();
        $tarefaEnvio = $etapaEnvio->getFirstTarefa();

        $tarefaInstalacao->numero = $tarefaEnvio->numero;
        $tarefaInstalacao->usuario_id = $tarefaEnvio->usuario_id;
        $tarefaInstalacao->unidade_responsavel_id = $tarefaEnvio->unidade_responsavel_id;

        $tarefaInstalacao->data_inicio_prevista = date('Y-m-d', strtotime($tarefaEnvio->data_fim_previsto. ' + 1 days'));
        
        $dateTempIni = strtotime($tarefaInstalacao->data_inicio_prevista);

        $dateTemp2 = date('Y-m-d', strtotime($tarefaInstalacao->data_inicio_prevista. ' + 10 days'));

       
        
        $dateTempFim = strtotime($dateTemp2);
        $randFim = mt_rand($dateTempIni,  $dateTempFim);
        $tarefaInstalacao->data_fim_prevista  =  date("Y-m-d", $randFim);

        $unidadeService = new UnidadeService();
        $empresasIds = $unidadeService->empresas()->pluck('id')->all();
        $randIndex = array_rand($empresasIds);
        
        $tarefaInstalacao->unidade_responsavel_id =  $empresasIds[$randIndex];

        $etapaInstalacao->tarefas()->save($tarefaInstalacao);


    }

    private function createEtapaEnvio($projeto){
        $unidadeService = new UnidadeService();

        $etapa = new Etapa();
        $etapa->projetoCnme()->associate($projeto);
        $etapa->usuario()->associate($projeto->usuario);
        $etapa->status = Etapa::STATUS_ABERTA;
        $etapa->tipo = Etapa::TIPO_ENVIO;
        $etapa->descricao = Etapa::DESC_ETAPA_ENVIO;

        $etapa->save();

        $tarefa = new Tarefa();
        
        $tarefa->usuario_id = $projeto->usuario_id;

        $empresasIds = $unidadeService->empresas()->pluck('id')->all();
        $randIndex = array_rand($empresasIds);


        $tarefa->unidade_responsavel_id =  $empresasIds[$randIndex];
        $tarefa->nome = Tarefa::DESC_TAREFA_ENVIO;
        $tarefa->status = Tarefa::STATUS_ABERTA;
        $tarefa->numero = rand();

        $tarefa->data_inicio_prevista = $projeto->data_inicio_previsto;
        $date2 = new DateTime($projeto->data_inicio_previsto);
        $date2->add(new DateInterval('P10D'));
        $tarefa->data_fim_prevista = $date2;
        
       
        // $tarefa->etapa()->associate( $etapa );
        // $tarefa->save();

        $etapa->tarefas()->save($tarefa);

        $equipamentosProjeto = EquipamentoProjeto::where('projeto_cnme_id',$projeto->id);
        $tarefa->equipamentosProjetos->merge( $equipamentosProjeto );

       

    }

    public function run(){
        $this->createKit();
        $this->createPoloUF();
        $this->createProjetoUf();
    }
}
