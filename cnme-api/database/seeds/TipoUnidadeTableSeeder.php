<?php

use Illuminate\Database\Seeder;
use App\Models\TipoUnidade;

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
        TipoUnidade::create(['nome' => 'Escola', 'descricao' => '','categoria' => 'Educação']);
    }
}