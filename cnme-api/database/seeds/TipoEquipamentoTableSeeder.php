<?php

use Illuminate\Database\Seeder;
use App\Models\TipoEquipamento;
use Illuminate\Support\Facades\DB;

class TipoEquipamentoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tipo_equipamentos')->delete();
        TipoEquipamento::create(['nome' => 'TV', 'descricao' => 'Aparelho de televisão']);
        TipoEquipamento::create(['nome' => 'Microfone', 'descricao' => 'Aparelho de capitação de áudio']);
        TipoEquipamento::create(['nome' => 'Câmera', 'descricao' => 'Aparelho de capitação de vídeo']);
        TipoEquipamento::create(['nome' => 'Caixa de Som', 'descricao' => 'Aparelho de ampliação de áudio']);
        TipoEquipamento::create(['nome' => 'Antena', 'descricao' => 'Aparelho de para capitação de sinal']);
        TipoEquipamento::create(['nome' => 'Receptor', 'descricao' => 'Receptor decodificador de sinal']);
    }
}
