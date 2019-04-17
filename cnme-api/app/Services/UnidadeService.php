<?php

namespace App\Services;

use App\Models\Unidade;

class UnidadeService{
    public function admin(){
        $unidade = Unidade::where('classe', Unidade::CLASSE_ADMIN)->first();
        return $unidade;
    }

    public function mec(){
        $unidade = Unidade::where('classe', Unidade::CLASSE_MEC)->first();
        return $unidade;
    }

    public function tvescola(){
        $unidade = Unidade::where('classe', Unidade::CLASSE_TVESCOLA)->first();
        return $unidade;
    }

    public function gestoras(){
        $unidades = Unidade::orWhere('classe', Unidade::CLASSE_MEC)
            ->orWhere('classe', Unidade::CLASSE_TVESCOLA)->get();

        return $unidades;
    }

    public function empresas($per_page = 25){
        
        $empresas = Unidade::where('classe', Unidade::CLASSE_EMPRESA)->paginate( $per_page );

        return $empresas;
    }
}