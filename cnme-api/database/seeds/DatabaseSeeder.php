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
        $this->call(UsuarioGestorTableSeeder::class);
    }
}
