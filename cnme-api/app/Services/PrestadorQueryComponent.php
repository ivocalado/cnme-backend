<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

use App\User;
use Illuminate\Support\Facades\DB;
use App\Models\ProjetoCnme;
use App\Models\Tarefa;
use App\Models\Etapa;

class PrestadorQueryComponent{
    public function queryPrestadoras($etapa){
        $result = DB::select("
        SELECT 
        unidade_responsavel_id,
        empresa, 
        sum(total) total,
        COALESCE(SUM(total_andamento),0) as total_andamento,
        COALESCE(SUM(total_vence_hoje),0) as total_vence_hoje,
        COALESCE(SUM(total_atrasada),0) as total_atrasada,
        COALESCE(SUM(total_concluida),0) as total_concluida,
        COALESCE(SUM(total_concluida_atrasada),0) as total_concluida_atrasada,
        avg(t2.prazo_medio) as prazo_medio,
        avg(t2.periodo_medio) as periodo_medio,
        avg(CASE WHEN status_tarefa IN ('ATRASADA', 'CONCLUIDA COM ATRASO') THEN t2.atraso_medio END) as atraso_medio
        FROM
        (
        SELECT 
        unidade_responsavel_id,
        empresa, 
        CASE WHEN status_tarefa = 'ANDAMENTO' THEN COUNT(tarefa_id) END as total_andamento, 
        CASE WHEN status_tarefa = 'VENCE HOJE' THEN COUNT(tarefa_id) END as total_vence_hoje, 
        CASE WHEN status_tarefa = 'ATRASADA' THEN COUNT(tarefa_id) END as total_atrasada, 
        CASE WHEN status_tarefa = 'CONCLUIDA' THEN COUNT(tarefa_id) END as total_concluida, 
        CASE WHEN status_tarefa = 'CONCLUIDA COM ATRASO' THEN COUNT(tarefa_id) END as total_concluida_atrasada, 
            status_tarefa, count(*) as total,
            avg(prazo_total) as prazo_medio,
            avg(periodo_total) as periodo_medio,
            avg(dias_atrasos) as atraso_medio
        FROM
        (	
        SELECT 

        u.nome as empresa, 
        p.id as ṕrojeto_cnme_id,
        u2.nome as polo,
        CASE 
            WHEN t.data_fim_prevista > now() and t.data_fim is null THEN 'ANDAMENTO' 
            WHEN t.data_fim_prevista = CURRENT_DATE and t.data_fim is null THEN 'VENCE HOJE'
            WHEN t.data_fim_prevista < now() and t.data_fim is null THEN 'ATRASADA'
            WHEN t.data_fim <= t.data_fim_prevista THEN 'CONCLUIDA'
            WHEN t.data_fim > t.data_fim_prevista THEN 'CONCLUIDA COM ATRASO'
        END AS status_tarefa,
        (t.data_fim_prevista - t.data_inicio_prevista)  as prazo_total,
        (t.data_fim - t.data_inicio) as periodo_total,
        CASE 
            WHEN t.data_fim_prevista < now() and t.data_fim is null then (now()::date -t.data_fim_prevista)
            WHEN t.data_fim is not null THEN  (t.data_fim - t.data_fim_prevista)
        ELSE 0 
        END dias_atrasos,

        e.id as etapa_id,
        t.id as tarefa_id,
        t.descricao,
        t.numero,
        t.link_externo,
        t.data_inicio_prevista,
        t.data_fim_prevista,
        t.data_inicio,
        t.data_fim,
        t.unidade_responsavel_id
        FROM tarefas t
        INNER JOIN unidades u on u.id = t.unidade_responsavel_id
        INNER JOIN localidades l on l.id = u.localidade_id
        INNER JOIN estados es on es.id =  l.estado_id
        INNER JOIN municipios m on m.id = l.municipio_id
        INNER JOIN etapas e on e.id = t.etapa_id and e.tipo = 'ENVIO'
        INNER JOIN projeto_cnmes p on p.id = e.projeto_cnme_id
        INNER JOIN unidades u2 on u2.id = p.unidade_id
        WHERE e.tipo = ? and p.data_inicio is not null
        ) as t
        GROUP BY unidade_responsavel_id, empresa, status_tarefa
        ) as t2
        GROUP BY unidade_responsavel_id, empresa
        ",[strtoupper($etapa)]);

        return $result;

    }

    public function queryPrestadoraPorEstado($etapa,$empresaId){
        $result = DB::select("
        SELECT 
        unidade_responsavel_id,
        empresa, estado, uf,
        sum(total) total,
        COALESCE(SUM(total_andamento),0) as total_andamento,
        COALESCE(SUM(total_vence_hoje),0) as total_vence_hoje,
        COALESCE(SUM(total_atrasada),0) as total_atrasada,
        COALESCE(SUM(total_concluida),0) as total_concluida,
        COALESCE(SUM(total_concluida_atrasada),0) as total_concluida_atrasada,
        avg(t2.prazo_medio) as prazo_medio,
        avg(t2.periodo_medio) as periodo_medio,
        avg(CASE WHEN status_tarefa IN ('ATRASADA', 'CONCLUIDA COM ATRASO') THEN t2.atraso_medio END) as atraso_medio
        FROM
        (
        SELECT 
        unidade_responsavel_id,
        empresa, estado, uf,
        CASE WHEN status_tarefa = 'ANDAMENTO' THEN COUNT(tarefa_id) END as total_andamento, 
        CASE WHEN status_tarefa = 'VENCE HOJE' THEN COUNT(tarefa_id) END as total_vence_hoje, 
        CASE WHEN status_tarefa = 'ATRASADA' THEN COUNT(tarefa_id) END as total_atrasada, 
        CASE WHEN status_tarefa = 'CONCLUIDA' THEN COUNT(tarefa_id) END as total_concluida, 
        CASE WHEN status_tarefa = 'CONCLUIDA COM ATRASO' THEN COUNT(tarefa_id) END as total_concluida_atrasada, 
            status_tarefa, count(*) as total,
            avg(prazo_total) as prazo_medio,
            avg(periodo_total) as periodo_medio,
            avg(dias_atrasos) as atraso_medio
        FROM
        (	
        SELECT 
        
        u.nome as empresa, 
        p.id as projeto_cnme_id,
        u2.nome as polo,
        es.nome as estado,
        es.sigla as uf,
        m.nome as municipio,
        CASE 
            WHEN t.data_fim_prevista > now() and t.data_fim is null THEN 'ANDAMENTO' 
            WHEN t.data_fim_prevista = CURRENT_DATE and t.data_fim is null THEN 'VENCE HOJE'
            WHEN t.data_fim_prevista < now() and t.data_fim is null THEN 'ATRASADA'
            WHEN t.data_fim <= t.data_fim_prevista THEN 'CONCLUIDA'
            WHEN t.data_fim > t.data_fim_prevista THEN 'CONCLUIDA COM ATRASO'
        END AS status_tarefa,
        (t.data_fim_prevista - t.data_inicio_prevista)  as prazo_total,
        (t.data_fim - t.data_inicio) as periodo_total,
        CASE 
            WHEN t.data_fim_prevista < now() and t.data_fim is null then (now()::date -t.data_fim_prevista)
            WHEN t.data_fim is not null THEN  (t.data_fim - t.data_fim_prevista)
        ELSE 0 
        END dias_atrasos,
        
        e.id as etapa_id,
        t.id as tarefa_id,
        t.descricao,
        t.numero,
        t.link_externo,
        t.data_inicio_prevista,
        t.data_fim_prevista,
        t.data_inicio,
        t.data_fim,
        t.unidade_responsavel_id
        FROM tarefas t
        INNER JOIN unidades u on u.id = t.unidade_responsavel_id
        INNER JOIN localidades l on l.id = u.localidade_id
        INNER JOIN estados es on es.id =  l.estado_id
        INNER JOIN municipios m on m.id = l.municipio_id
        INNER JOIN etapas e on e.id = t.etapa_id
        INNER JOIN projeto_cnmes p on p.id = e.projeto_cnme_id
        INNER JOIN unidades u2 on u2.id = p.unidade_id
        WHERE e.tipo = ? and p.data_inicio is not null
        ) as t
        GROUP BY unidade_responsavel_id, empresa, estado,uf, status_tarefa
        ) as t2
        WHERE unidade_responsavel_id = ?
        GROUP BY unidade_responsavel_id, empresa, estado, uf        
        ",[strtoupper($etapa), $empresaId]);

        return $result;

    }
}