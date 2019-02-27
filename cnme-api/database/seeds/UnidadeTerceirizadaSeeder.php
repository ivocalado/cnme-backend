<?php

use Illuminate\Database\Seeder;
use App\Models\TipoUnidade;
use App\Models\Unidade;
use App\User;

class UnidadeTerceirizadaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tipoEmpresa = TipoUnidade::where('classe',Unidade::CLASSE_EMPRESA)->first();
        $empresa = Unidade::create([
            'nome' => 'Transportador Golaço Log', 
            'descricao' => 'Transportadora autorizada pela portaria A3E322DS',
            'classe' => Unidade::CLASSE_EMPRESA,
            'admin' => false,
            'email' => 'transportadora@golaco.com.br',
            'email_institucional' => 'faleconosco@transportadora.com.br',
            'url' => 'https://www.transportadoragolaco.gov.br/',
            'diretor' => 'Contato Golaço',
            'telefone' => '(0300 11324234)',
            'tipo_unidade_id' => $tipoEmpresa->id
        ]);

        $gestorEmpresa = User::create([
            'name' => 'Administrador Golaço Log', 
            'email' => $empresa->email,
            'password' => Hash::make('dasda623asda2'),
            'telefone' => $empresa->telefone,
            'cpf' => '99.999.999/9999-99',
            'funcao' => 'Diretor A',
            'tipo' => User::TIPO_EXTERNO,
            'unidade_id' => $empresa->id
        ]);

        $empresa->responsavel()->associate($gestorEmpresa);
        $empresa->save();
    }
}
