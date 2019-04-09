<?php

use Illuminate\Database\Seeder;
use App\Models\TipoChamado;

class TipoChamadoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TipoChamado::create(['id' => 1, 'nome' => 'Suporte']);
        TipoChamado::create(['id' => 2, 'nome' => 'Defeito']);
    }
}
