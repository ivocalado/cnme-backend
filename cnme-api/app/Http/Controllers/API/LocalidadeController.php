<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Estado;
use App\Models\Municipio;

class LocalidadeController extends Controller
{
    public function estados(){
        return response()->json(
            array(
                "data" => Estado::all()
            )
        );
    }

    public function municipios($estadoUf){

        $municipios = Estado::with('municipios')->where('sigla', $estadoUf)->first()->municipios;
        return response()->json(
            array(
                "data" => $municipios
            )
        );
    }
}
