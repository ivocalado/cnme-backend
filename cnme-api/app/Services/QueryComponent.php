<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

use App\User;
use Illuminate\Support\Facades\DB;
use App\Models\ProjetoCnme;
use App\Models\Tarefa;
use App\Models\Etapa;

class QueryComponent{

    public function countProjetos($uf = null){
        $query = DB::table('projeto_cnmes');

        if($uf){
           
            $query->join('unidades', 'projeto_cnmes.unidade_id', '=', 'unidades.id')
            ->join('localidades', 'unidades.localidade_id','=','localidades.id')
            ->join('estados', 'localidades.estado_id', '=', 'estados.id');

            $query->where('estados.sigla', '=', strtoupper($uf));
        }

        $query->where('status', '!=', ProjetoCnme::STATUS_CANCELADO);


        return $query->count();
    }

    public function countPlanejamento($uf = null){
        $query = DB::table('projeto_cnmes');
        $query->where('status','=',ProjetoCnme::STATUS_PLANEJAMENTO);

        return $query->count();
    }

    public function countConcluidos($uf = null){
        $query = DB::table('projeto_cnmes');
        $query->where('status','=',ProjetoCnme::STATUS_ATIVADO);

        return $query->count();

    }

    public function countAndamento($uf = null){
        $query = DB::table('projeto_cnmes');
        $query->whereIn('status', [
                                    ProjetoCnme::STATUS_ENTREGUE, 
                                    ProjetoCnme::STATUS_ENVIADO,
                                    ProjetoCnme::STATUS_INSTALADO]);

        return $query->count();
    }

    
    public function queryProjetosEtapasExtrato($uf = null){
        
        $sql = "
        SELECT 
            etapa,
            sum(total) total,
            COALESCE(SUM(total_aberta),0) as total_aberta,
            COALESCE(SUM(total_andamento),0) as total_andamento,
            COALESCE(SUM(total_atrasada),0) as total_atrasada,
            COALESCE(SUM(total_concluida),0) as total_concluida,
            COALESCE(SUM(total_concluida_atrasada),0) as total_concluida_atrasada
            FROM
            (
            SELECT 
            etapa, 
            CASE WHEN status_tarefa = 'ANDAMENTO' THEN COUNT(etapa_id) END as total_andamento, 
            CASE WHEN status_tarefa = 'ABERTA' THEN COUNT(etapa_id) END as total_aberta, 
            CASE WHEN status_tarefa = 'ATRASADA' THEN COUNT(etapa_id) END as total_atrasada, 
            CASE WHEN status_tarefa = 'CONCLUIDA' THEN COUNT(etapa_id) END as total_concluida, 
            CASE WHEN status_tarefa = 'CONCLUIDA COM ATRASO' THEN COUNT(etapa_id) END as total_concluida_atrasada, 
            status_tarefa, count(etapa_id) as total
            FROM
            (	
                SELECT 

                e.tipo as etapa,
                CASE 
                    WHEN t.data_fim is null and t.data_inicio is null and t.data_inicio_prevista >= now() THEN 'ABERTA'
                    WHEN t.data_fim is null and t.data_inicio is null and t.data_inicio_prevista < now() THEN 'ATRASADA'
                    WHEN t.data_fim is null and t.data_inicio is not null and t.data_fim_prevista < now() THEN 'ATRASADA'
                    WHEN t.data_fim is null and t.data_inicio is not null THEN 'ANDAMENTO' 
                    WHEN t.data_fim is not null and t.data_fim > t.data_fim_prevista THEN 'CONCLUIDA COM ATRASO'
                    WHEN t.data_fim is not null THEN 'CONCLUIDA'
                
                END AS status_tarefa,
                t.id as tarefa_id,
                e.id as etapa_id
                FROM tarefas t
                INNER JOIN etapas e on e.id = t.etapa_id
                INNER JOIN projeto_cnmes p on p.id = e.projeto_cnme_id
                INNER JOIN unidades u2 on u2.id = p.unidade_id
                INNER JOIN localidades l on l.id = u2.localidade_id
                INNER JOIN estados es on es.id = l.estado_id
                WHERE p.status != 'CANCELADO' 
        ";
            
        if($uf){
            $sql = $sql." and es.sigla = '".$uf."' ";
        }

        
        $sql = $sql." ORDER BY e.tipo
            ) as t
        GROUP BY etapa, status_tarefa
        ) as t2
        GROUP BY etapa";

        $result = DB::select($sql);

        return $result; 
    }

    public function countAtrasados($uf = null, $etapa = null){
        $query = DB::table('projeto_cnmes')
        ->join('etapas','etapas.projeto_cnme_id','projeto_cnmes.id' )
        ->join('tarefas','tarefas.etapa_id','etapas.id' );

        if($uf){
            $query->join('unidades', 'projeto_cnmes.unidade_id', '=', 'unidades.id')
            ->join('localidades', 'unidades.localidade_id','=','localidades.id')
            ->join('estados', 'localidades.estado_id', '=', 'estados.id');

            $query->where('estados.sigla', '=', strtoupper($uf));
        }

        $query->whereNotNull('tarefas.data_inicio');
        $query->whereNotNull('projeto_cnmes.data_inicio');
        $query->whereNull('tarefas.data_fim');
        $query->where('tarefas.status','=',Tarefa::STATUS_ANDAMENTO)
        ->where('projeto_cnmes.status','!=',ProjetoCnme::STATUS_CANCELADO)
        ->whereNull('tarefas.data_fim')
        ->where('tarefas.data_fim_prevista','<=',\DB::raw('NOW()'));

        if(isset($etapa) && Etapa::checkTipo($etapa)){
            $query->where('etapas.tipo','=',  strtoupper($etapa));
        }

        return $query->count();
    }

    public function queryPorStatus($uf = null){
        $query = DB::table('projeto_cnmes')
                     ->select(DB::raw('count(*) as status_count, status'));

        if($uf){
            $query->join('unidades', 'projeto_cnmes.unidade_id', '=', 'unidades.id')
            ->join('localidades', 'unidades.localidade_id','=','localidades.id')
            ->join('estados', 'localidades.estado_id', '=', 'estados.id');

            $query->where('estados.sigla', '=', strtoupper($uf));
        }

        $query->groupBy('status');
        $result = $query->get();

        return $result;
    }
}