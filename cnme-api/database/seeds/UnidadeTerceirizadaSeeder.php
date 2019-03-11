<?php

use Illuminate\Database\Seeder;
use App\Models\TipoUnidade;
use App\Models\Unidade;
use App\User;
use App\Models\Localidade;
use App\Models\Estado;
use App\Models\Municipio;

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

        $local = Localidade::create([
            'logradouro' => 'Lugar nenhum', 
            'numero' => 'sn',
            'bairro' => 'Setor Planejado',
            'cep' => '94933-541',
            'estado_id' => Estado::where('sigla','DF')->first()->id,
            'municipio_id' => Municipio::where('codigo_ibge','5300108')->first()->id
        ]);

        $empresa->localidade()->associate( $local );
        $empresa->save();

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
