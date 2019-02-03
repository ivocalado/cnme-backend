<?php

use Illuminate\Database\Seeder;
use App\Models\TipoUnidade;
use App\Models\Unidade;

class TipoUnidadeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tipo_unidades')->delete();
        TipoUnidade::create(['nome' => 'MEC', 'descricao' => '','classe' => Unidade::CLASSE_MEC,'admin' => true]);
        TipoUnidade::create(['nome' => 'TV Escola', 'descricao' => '','classe' => Unidade::CLASSE_TVESCOLA,'admin' => true]);
        TipoUnidade::create(['nome' => 'Escola', 'descricao' => '','classe' => Unidade::CLASSE_POLO]);
        TipoUnidade::create(['nome' => 'Empresa', 'descricao' => '','classe' => Unidade::CLASSE_EMPRESA]);
        TipoUnidade::create(['nome' => 'Admin', 'descricao' => '','classe' => Unidade::CLASSE_ADMIN,'admin' => true]);
        
    }
}
