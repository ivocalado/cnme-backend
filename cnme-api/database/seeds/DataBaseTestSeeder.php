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

            $nome = strtolower( preg_replace("/[^a-zA-Z0-9-]/", "-", strtr(utf8_decode(trim($e->nome)), utf8_decode("áàãâéêíóôõúüñçÁÀÃÂÉÊÍÓÔÕÚÜÑÇ"),"aaaaeeiooouuncAAAAEEIOOOUUNC-")) );

            $polo = Unidade::create([
                'nome' => 'Polo '.$e->nome, 
                'descricao' => 'Escola de Teste '.$e->nome,
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
            $polo->save();

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
            $projeto->save();
            }
    }

    public function run(){
        $this->createKit();
        $this->createPoloUF();
        $this->createProjetoUf();
    }
}
