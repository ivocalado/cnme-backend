<?php

use Illuminate\Database\Seeder;
use App\Models\Unidade;
use Illuminate\Support\Facades\DB;

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
        Unidade::create([
            'nome' => 'MEC', 
            'descricao' => '',
            'classe' => Unidade::CLASSE_MEC,
            'admin' => true,
            'email' => 'thiago.oliveira@ifal.edu.br',
            'email_institucional' => 'mec@mec.gov.br',
            'url' => 'https://www.mec.gov.br/',
            'diretor' => 'João MEC',
            'telefone' => '(000-00000-0000)',
            'tipo_unidade_id' => '1'
        ]);

        Unidade::create([
            'nome' => 'TV Escola', 
            'descricao' => '',
            'classe' => Unidade::CLASSE_TVESCOLA,
            'admin' => true,
            'email' => 'thiago.araujo.so@gmail.com',
            'email_institucional' => 'tvescola@tvescola.gov.br',
            'url' => 'https://tvescola.org.br/',
            'diretor' => 'José TV Escola',
            'telefone' => '(999-99999-9999)',
            'tipo_unidade_id' => '2'
        ]);
    }
}
