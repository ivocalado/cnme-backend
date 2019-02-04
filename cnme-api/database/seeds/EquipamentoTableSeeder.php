<?php

use Illuminate\Database\Seeder;
use App\Models\Equipamento;
use Illuminate\Support\Facades\DB;

class EquipamentoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('equipamentos')->delete();
        
        Equipamento::create(['nome' => 'TV Beta 100x', 'descricao' => 'Aparelho de televisão 100x','tipo_equipamento_id' => 1, 'fornecedor' => 'Digital 100x Tecnologia','requisitos' => 'Mesa de apoio ou suporte para TV 40 / 15kg']);
        Equipamento::create(['nome' => 'Microfone Alpha 100x', 'descricao' => 'Microfone','tipo_equipamento_id' => 2, 'fornecedor' => 'Digital 100x Tecnologia','requisitos' => 'Espaço livre de respingos de água']);
        Equipamento::create(['nome' => 'Câmera 4.0 100x', 'descricao' => 'Câmera 4.0 4000MHz','tipo_equipamento_id' => 3, 'fornecedor' => 'Digital 100x Tecnologia','requisitos' => 'Bom nível de Iluminação']);
        Equipamento::create(['nome' => 'Box Sound 2 1000w', 'descricao' => 'Box Sound 2.0 1000W','tipo_equipamento_id' => 4, 'fornecedor' => 'Digital 100x Tecnologia','requisitos' => 'Recomanda para espaço de no máximo 80m²']);
        Equipamento::create(['nome' => 'Antena Digital HD Over', 'descricao' => 'Antena Digital HD Over','tipo_equipamento_id' => 5, 'fornecedor' => 'Digital 100x Tecnologia','requisitos' => 'Posicionada em um ponto alto']);
        Equipamento::create(['nome' => 'Decodificador Digital Box HD Over', 'descricao' => 'Decodificador Digital Box HD Over','tipo_equipamento_id' => 6, 'fornecedor' => 'Digital 100x Tecnologia','requisitos' => 'Ligação com a TV, antena e caixa de som']);
    }
}
