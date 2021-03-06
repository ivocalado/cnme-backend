<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(EstadosTableSeeder::class);
        $this->call(MunicipioTableSeeder::class);
        $this->call(TipoUnidadeTableSeeder::class);
        $this->call(UnidadeGestoraTableSeeder::class);
        $this->call(UnidadeTerceirizadaSeeder::class);
        $this->call(UsuarioGestorTableSeeder::class);
        $this->call(TipoEquipamentoTableSeeder::class);
        $this->call(EquipamentoTableSeeder::class);

        $this->call(UpdateEstadoRegiaoSeeder::class);
        $this->call(StatusChamadoTableSeeder::class);
        $this->call(TipoChamadoTableSeeder::class);
        $this->call(ChecklistTableSeeder::class);
        $this->call(DataBaseTestSeeder::class);
        $this->call(ChamadoTableSeeder::class);
       


       
    }
}
