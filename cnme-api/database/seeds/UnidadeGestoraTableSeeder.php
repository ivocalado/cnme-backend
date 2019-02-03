<?php

use Illuminate\Database\Seeder;
use App\Models\Unidade;
use Illuminate\Support\Facades\DB;
use App\Models\Localidade;
use App\Models\Municipio;
use App\Models\Estado;
use App\Models\TipoUnidade;

class UnidadeGestoraTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('unidades')->delete();

        $tipoMec = TipoUnidade::where('classe',Unidade::CLASSE_MEC)->first();
        $mec = Unidade::create([
            'nome' => 'MEC', 
            'descricao' => '',
            'classe' => Unidade::CLASSE_MEC,
            'admin' => true,
            'email' => 'thiago.oliveira@ifal.edu.br',
            'email_institucional' => 'mec@mec.gov.br',
            'url' => 'https://www.mec.gov.br/',
            'diretor' => 'João MEC',
            'telefone' => '(0800 61 6161)',
            'tipo_unidade_id' => $tipoMec->id
        ]);

        $localMec = Localidade::create([
            'logradouro' => 'Esplanada dos Ministérios', 
            'numero' => 'sn',
            'bairro' => 'Ministério da Educação',
            'cep' => '70047-900',
            'estado_id' => Estado::where('sigla','DF')->first()->id,
            'municipio_id' => Municipio::where('codigo_ibge','5300108')->first()->id
        ]);

        $tipoEscola = TipoUnidade::where('classe',Unidade::CLASSE_TVESCOLA)->first();
        $mec->localidade()->associate( $localMec );
        $mec->save();

        $tvEscola = Unidade::create([
            'nome' => 'TV Escola', 
            'descricao' => '',
            'classe' => Unidade::CLASSE_TVESCOLA,
            'admin' => true,
            'email' => 'thiago.araujo.so@gmail.com',
            'email_institucional' => 'tvescola@tvescola.gov.br',
            'url' => 'https://tvescola.org.br/',
            'diretor' => 'José TV Escola',
            'telefone' => '(999-99999-9999)',
            'tipo_unidade_id' => $tipoEscola->id
        ]);

        $localTV = Localidade::create([
            'logradouro' => 'Esplanada dos Ministérios', 
            'numero' => 'sn',
            'bairro' => 'Ministério da Educação',
            'cep' => '70047-900',
            'complemento' => 'Bloco L - Sala 916',
            'estado_id' => Estado::where('sigla','DF')->first()->id,
            'municipio_id' => Municipio::where('codigo_ibge','5300108')->first()->id
        ]);

        $tvEscola->localidade()->associate( $localTV );
        $tvEscola->save();

        /***************************************************** */

        $tipoAdmin = TipoUnidade::where('classe',Unidade::CLASSE_ADMIN)->first();

        $unidadeAdmin = Unidade::create([
            'nome' => 'Administração do Sistema', 
            'classe' => Unidade::CLASSE_ADMIN,
            'admin' => true,
            'email' => 'admin@cmne.gov.br',
            'email_institucional' => 'comunicacao@cnme.gov.br',
            'url' => 'http://cnme.mec.gov.br/',
            'tipo_unidade_id' => $tipoAdmin->id
        ]);

        $localAdmin = Localidade::create([
            'logradouro' => 'Universidade Federal de Alagoas - UFAL', 
            'numero' => 'sn',
            'complemento' => 'Núcleo de Excelência em Tecnologias Sociais - NEES. Instituto Computação - IC',
            'bairro' => 'Cidade Universitária',
            'cep' => '57072-970',
            'estado_id' => Estado::where('sigla','AL')->first()->id,
            'municipio_id' => Municipio::where('codigo_ibge','2704302')->first()->id
        ]);

        $unidadeAdmin->localidade()->associate( $localAdmin );
        $unidadeAdmin->save();



    }
}
