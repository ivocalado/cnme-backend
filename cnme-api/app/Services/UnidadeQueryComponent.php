<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

use App\User;
use Illuminate\Support\Facades\DB;
use App\Models\ProjetoCnme;
use App\Models\Tarefa;
use App\Models\Etapa;
use App\Models\Unidade;

class UnidadeQueryComponent{
    public function countGestoresNaoConfirmados($uf){
        $query = DB::table('users as usuario');
        $query->join('unidades', 'usuario.unidade_id', '=', 'unidades.id');

        if($uf){
            $query->join('localidades', 'unidades.localidade_id','=','localidades.id')
            ->join('estados', 'localidades.estado_id', '=', 'estados.id');

            $query->where('estados.sigla', '=', strtoupper($uf));
        }

        $query->where('unidades.classe','=',Unidade::CLASSE_POLO);
        $query->whereNull('email_verified_at');
        $query->whereRaw('unidades.responsavel_id = usuario.id');

        return $query->count();
    }

    public function countGestoresConfirmados($uf){
        $query = DB::table('users as usuario');
        $query->join('unidades', 'usuario.unidade_id', '=', 'unidades.id');

        if($uf){
            $query->join('localidades', 'unidades.localidade_id','=','localidades.id')
            ->join('estados', 'localidades.estado_id', '=', 'estados.id');

            $query->where('estados.sigla', '=', strtoupper($uf));
        }

        $query->where('unidades.classe','=',Unidade::CLASSE_POLO);
        $query->whereNotNull('email_verified_at');
        $query->whereRaw('unidades.responsavel_id = usuario.id');

        return $query->count();
    }
}