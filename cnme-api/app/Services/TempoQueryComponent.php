<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

use App\User;
use Illuminate\Support\Facades\DB;
use App\Models\ProjetoCnme;
use App\Models\Tarefa;
use App\Models\Etapa;

class TempoQueryComponent{
    public function queryProjeto12Meses(){
        $result = DB::select("
        SELECT t.ano_mes, t.mes_ano, t.mes_ano_abrev, 
        COUNT(DISTINCT(p1.id)) as iniciados,
        COUNT(DISTINCT(p2.id)) as concluidos
        from tempo t 
        LEFT OUTER JOIN projeto_cnmes p1 on TO_CHAR(p1.data_inicio,'YYYYMM') = t.ano_mes and p1.status != 'CANCELADO'
        LEFT OUTER JOIN projeto_cnmes p2 on TO_CHAR(p1.data_fim,'YYYYMM') = t.ano_mes and p2.status != 'CANCELADO'
        WHERE (CURRENT_DATE - t.data_atual) between 0 and 330
        GROUP BY t.ano_mes, t.mes_ano, t.mes_ano_abrev
        ");

        return $result;

    }

    public function queryProjetoEstadoAno($pAno, $uf = null, $regiao = null){

        $ano = isset($pAno)? $pAno: date("Y");
        
        $sql = "
        SELECT 
            regiao,
            uf,
            sum(total_jan) as total_jan,
            sum(total_fev) as total_fev,
            sum(total_mar) as total_mar,
            sum(total_abr) as total_abr,
            sum(total_mai) as total_mai,
            sum(total_jun) as total_jun,
            sum(total_jul) as total_jul,
            sum(total_ago) as total_ago,
            sum(total_set) as total_set,
            sum(total_out) as total_out,
            sum(total_nov) as total_nov,
            sum(total_dez) as total_dez,
            (sum(total_jan) + sum(total_fev) + sum(total_mar) + sum(total_abr) + sum(total_mai) + sum(total_jun) + sum(total_jul) +
            sum(total_ago) + sum(total_set) + sum(total_out) + sum(total_nov) + sum(total_dez)) as total
        FROM
            (SELECT 
                regiao,
                uf,
                CASE WHEN mes = 1 THEN total end as total_jan,
                CASE WHEN mes = 2 THEN total end as total_fev,
                CASE WHEN mes = 3 THEN total end as total_mar,
                CASE WHEN mes = 4 THEN total end as total_abr,
                CASE WHEN mes = 5 THEN total end as total_mai,
                CASE WHEN mes = 6 THEN total end as total_jun,
                CASE WHEN mes = 7 THEN total end as total_jul,
                CASE WHEN mes = 8 THEN total end as total_ago,
                CASE WHEN mes = 9 THEN total end as total_set,
                CASE WHEN mes = 10 THEN total end as total_out,
                CASE WHEN mes = 11 THEN total end as total_nov,
                CASE WHEN mes = 12 THEN total end as total_dez
                FROM
                (SELECT 
                t.ano,
                t.regiao,
                t.uf,
                t.ano_mes, t.mes_ano, t.mes_ano_abrev, t.mes,
                COUNT(DISTINCT(p.id)) as total
            FROM projeto_cnmes p
            INNER JOIN unidades u on u.id = p.unidade_id
            INNER JOIN localidades l on l.id = u.localidade_id
            INNER JOIN estados e on e.id = l.estado_id

            FULL OUTER JOIN (
                select distinct e.regiao, e.sigla as uf, t.ano, t.ano_mes, t.mes_ano, t.mes_ano_abrev, t.mes
                FROM estados e
                CROSS JOIN tempo t
                WHERE t.ano = ? 
                GROUP BY e.regiao, e.sigla,t.ano, t.ano_mes, t.mes_ano, t.mes_ano_abrev, t.mes
                ORDER BY e.regiao, e.sigla,t.ano, t.ano_mes, t.mes_ano, t.mes_ano_abrev, t.mes
            ) t on t.ano_mes = TO_CHAR(p.data_inicio, 'YYYYMM') and e.sigla = t.uf
            WHERE p.status != 'CANCELADO'
            GROUP BY t.regiao, t.uf, t.ano, t.ano_mes, t.mes_ano, t.mes_ano_abrev, t.mes
            ORDER BY t.regiao, t.uf, t.ano, t.ano_mes, t.mes) tt
        ) ttt
        ";
        if(isset($uf) || isset($regiao)){
            $sql = $sql." where 1=1 ";
            $sql = isset($uf)?$sql." and uf = '".strtoupper($uf)."' ":$sql;
            $sql = isset($regiao)?$sql." and UPPER(regiao) = '".strtoupper($regiao)."' ":$sql;
        }

        $sql = $sql." group by regiao, uf order by regiao, uf"; 

        $result = DB::select($sql, [$ano]);

        return $result;
    }
}