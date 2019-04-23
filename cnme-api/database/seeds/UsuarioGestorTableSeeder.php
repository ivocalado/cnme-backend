<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Models\Unidade;

class UsuarioGestorTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mec = Unidade::where('classe', Unidade::CLASSE_MEC)->first();

        $gestorMec = User::create([
            'name' => 'Gestor MEC', 
            'email' => $mec->email,
            'password' => Hash::make('123456'),
            'telefone' => $mec->telefone,
            'cpf' => '00.394.445/0139-39',
            'funcao' => 'Diretor A',
            'tipo' => User::TIPO_GESTOR,
            'unidade_id' => $mec->id
        ]);

        $mec->responsavel()->associate($gestorMec);
        $mec->usuarioChamados()->associate($gestorMec);
        $mec->save();

        /***************************************** */

        $tv = Unidade::where('classe', Unidade::CLASSE_TVESCOLA)->first();

        $gestorTV = User::create([
            'name' => 'Gestor TV Escola', 
            'email' => $tv->email,
            'password' => Hash::make('123456'),
            'telefone' => $tv->telefone,
            'cpf' => '00.394.445/0139-39',
            'funcao' => 'Diretor A',
            'tipo' => User::TIPO_GESTOR,
            'unidade_id' => $tv->id
        ]);

        $tv->responsavel()->associate($gestorTV);
        $tv->usuarioChamados()->associate($gestorTV);
        $tv->save();

        /****************************************** */

        $admin = Unidade::where('classe', Unidade::CLASSE_ADMIN)->first();

        $gestorAdmin = User::create([
            'name' => 'Admin', 
            'email' => 'admin@cnme.gov.br',
            'password' => Hash::make('123456'),
            'tipo' => User::TIPO_ADMINISTRADOR,
            'unidade_id' => $admin->id
        ]);

        $admin->responsavel()->associate($gestorAdmin);
        $admin->usuarioChamados()->associate($gestorAdmin);
        $admin->save();
    }
}
