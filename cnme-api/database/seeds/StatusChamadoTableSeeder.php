<?php

use Illuminate\Database\Seeder;

use App\Models\StatusChamado;

class StatusChamadoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StatusChamado::create(['id' => 10, 'nome' => 'Novo']);
        StatusChamado::create(['id' => 20, 'nome' => 'Aberto']);
        StatusChamado::create(['id' => 30, 'nome' => 'Rejeitado']);
        StatusChamado::create(['id' => 40, 'nome' => 'Em andamento']);
        StatusChamado::create(['id' => 50, 'nome' => 'Resolvido']);
    }
}
