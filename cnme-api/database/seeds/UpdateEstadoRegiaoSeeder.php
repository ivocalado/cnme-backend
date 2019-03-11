<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateEstadoRegiaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('estados')
            ->whereIn('sigla', ['AL', 'BA', 'CE', 'MA', 'PB','PE','PI',
            'RN','SE'])
            ->update(['regiao' => "Nordeste"]);

        DB::table('estados')
        ->whereIn('sigla', ['AC','AP','AM','PA','RO', 'RR', 'TO'])
        ->update(['regiao' => "Norte"]);

        DB::table('estados')
        ->whereIn('sigla', ['MG', 'ES', 'RJ' , 'SP'])
        ->update(['regiao' => "Sudeste"]);

        DB::table('estados')
        ->whereIn('sigla', ['SC','PR','RS'])
        ->update(['regiao' => "Sul"]);

        DB::table('estados')
        ->whereIn('sigla', ['DF', 'GO', 'MT', 'MS'])
        ->update(['regiao' => "Centro-Oeste"]);
    }
}
