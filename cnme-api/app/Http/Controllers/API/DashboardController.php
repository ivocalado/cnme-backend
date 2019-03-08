<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ProjetoCnme;
use App\Models\Tarefa;
use App\User;
use App\Models\Estado;
use App\Models\Unidade;
use App\Models\Etapa;
use function GuzzleHttp\json_decode;

class DashboardController extends Controller
{
    //QTD de projetos OK
    //QTD de projetos por STATUS OK 
    //QTD de projetos por Estado OK 
    //QTD de projetos com tarefas em atraso OK
    //QTD de usuários gestores não confirmados OK
    
    public function countProjetos(Request $request){
        $query = DB::table('projeto_cnmes');

        if($request->uf){
           
            $query->join('unidades', 'projeto_cnmes.unidade_id', '=', 'unidades.id')
            ->join('localidades', 'unidades.localidade_id','=','localidades.id')
            ->join('estados', 'localidades.estado_id', '=', 'estados.id');

            $query->where('estados.sigla', '=', strtoupper($request['uf']));
        }

        return response()->json($query->count());
    }

    protected $totalCount;
    protected $debugTemp = "";
    public function queryPorStatus(Request $request){
        $query = DB::table('projeto_cnmes')
                     ->select(DB::raw('count(*) as status_count, status'));

        if($request->uf){
            $query->join('unidades', 'projeto_cnmes.unidade_id', '=', 'unidades.id')
            ->join('localidades', 'unidades.localidade_id','=','localidades.id')
            ->join('estados', 'localidades.estado_id', '=', 'estados.id');

            $query->where('estados.sigla', '=', strtoupper($request['uf']));
        }

        $query->groupBy('status');
        $result = $query->get();

        $statusList = ProjetoCnme::status();
        $this->totalCount = DB::table('projeto_cnmes')->count();

        foreach($statusList as $status){
            if(!$result->contains('status',$status)){
                $v['status_count'] = 0;
                $v['status'] = $status;
                $result->push($v);
            }
        }

        $result = collect($result)->map(function($v){
            $v =  (array)$v;
            $v['status_percent'] = ($this->totalCount != 0) ? round(($v['status_count']/$this->totalCount),4)*100 : 0;
            return $v;
        })->toArray();      

        
    

        return response()->json($result);

    }

    public function queryPorEstado(Request $request){
        $result = DB::table('projeto_cnmes')
        ->join('unidades', 'projeto_cnmes.unidade_id', '=', 'unidades.id')
        ->join('localidades', 'unidades.localidade_id','=','localidades.id')
        ->join('estados', 'localidades.estado_id', '=', 'estados.id')
        ->select(DB::raw('count(*) as estado_count, estados.nome, estados.sigla'))
        ->groupBy('estados.nome','estados.sigla')
        ->get();

        $this->totalCount = DB::table('projeto_cnmes')->count();

        $result = collect($result)->map(function($v){
            $v =  (array)$v;
            $v['estado_percent'] = ($this->totalCount != 0) ? round(($v['estado_count']/$this->totalCount),4)*100 : 0;
            return $v;
        })->toArray();    

        return response()->json($result);
    }

    public function queryPorEstadoAll(Request $request){
        $projetosEstado = DB::table('projeto_cnmes')
        ->join('unidades', 'projeto_cnmes.unidade_id', '=', 'unidades.id')
        ->join('localidades', 'unidades.localidade_id','=','localidades.id')
        ->join('estados', 'localidades.estado_id', '=', 'estados.id')
        ->select(DB::raw('count(*) as estado_count, estados.nome as estado, estados.sigla'))
        ->groupBy('estados.nome','estados.sigla')
        ->get();


        $estadosList = Estado::all();

        foreach($estadosList as $estado){
            if(!$projetosEstado->contains('estado',$estado->nome)){
                $v['estado_count'] = 0;
                $v['estado'] = $estado->nome;
                $projetosEstado->push($v);
            }
        }

        $this->totalCount = DB::table('projeto_cnmes')->count();

        $result = collect($projetosEstado)->map(function($v){
            $v =  (array)$v;
            $v['estado_percent'] = ($this->totalCount != 0) ? round(($v['estado_count']/$this->totalCount),4)*100 : 0;
            return $v;
        })->toArray();  

        return response()->json($result);
    }

    private function queryAtrasados(Request $request){
        $query = DB::table('projeto_cnmes')
        ->join('etapas','etapas.projeto_cnme_id','projeto_cnmes.id' )
        ->join('tarefas','tarefas.etapa_id','etapas.id' );

        if($request->uf){
            $query->join('unidades', 'projeto_cnmes.unidade_id', '=', 'unidades.id')
            ->join('localidades', 'unidades.localidade_id','=','localidades.id')
            ->join('estados', 'localidades.estado_id', '=', 'estados.id');

            $query->where('estados.sigla', '=', strtoupper($request['uf']));
        }

        $query->whereNotNull('tarefas.data_inicio');
        $query->whereNull('tarefas.data_fim');
        $query->where('tarefas.status','=',Tarefa::STATUS_ANDAMENTO)
        
        ->whereNull('tarefas.data_fim')
        ->where('tarefas.data_fim_prevista','<=',\DB::raw('NOW()'));

        return $query;
    }
    

    public function countAtrasados(Request $request){
        $query = $this->queryAtrasados($request);

        return response()->json($query->count());
    }

    public function countAtrasadosPorEtapa(Request $request){
        $query = $this->queryAtrasados($request);

        if(Etapa::checkTipo($request->etapa)){
            $query->where('etapas.tipo','=',  strtoupper($request->etapa));
            return response()->json($query->count());
        }else
            return response()->json(
            array('message' => "Tipo desconhecido. Tipos:(".implode("|",Etapa::tipos()).")") , 422);

       
    }

    public function countGestoresNaoConfirmados(Request $request){
        $query = DB::table('users as usuario');
        $query->join('unidades', 'usuario.unidade_id', '=', 'unidades.id');

        if($request->uf){
            $query->join('localidades', 'unidades.localidade_id','=','localidades.id')
            ->join('estados', 'localidades.estado_id', '=', 'estados.id');

            $query->where('estados.sigla', '=', strtoupper($request['uf']));
        }

        $query->where('unidades.classe','=',Unidade::CLASSE_POLO);
        $query->whereNull('email_verified_at');
        $query->whereRaw('unidades.responsavel_id = usuario.id');

        return response()->json($query->count());
    }

    public function queryStatusEstados(Request $request){
        $result = DB::select("SELECT estado, uf, sum(total) as total,
        sum(total_criado) as total_criado,
        sum(total_planejamento) as total_planejamento,
        sum(total_enviado) as total_enviado,
        sum(total_entregue) as total_entregue,
        sum(total_instalado) as total_instalado,
        sum(total_finalizado) as total_finalizado,
        sum(total_cancelado) as total_cancelado
        FROM
            (SELECT 
            estado, 
            uf,
            CASE WHEN status IS not null THEN SUM(total) ELSE 0 END total,
            CASE WHEN status = 'CRIADO' THEN SUM(total) ELSE 0 END total_criado,
            CASE WHEN status = 'PLANEJAMENTO' THEN SUM(total) ELSE 0 END total_planejamento,
            CASE WHEN status = 'ENVIADO' THEN SUM(total) ELSE 0 END total_enviado,
            CASE WHEN status = 'ENTREGUE' THEN SUM(total) ELSE 0 END total_entregue,
            CASE WHEN status = 'INSTALADO' THEN SUM(total) ELSE 0 END total_instalado,
            CASE WHEN status = 'FINALIZADO' THEN SUM(total) ELSE 0 END total_finalizado,
            CASE WHEN status = 'CANCELADO' THEN SUM(total) ELSE 0 END total_cancelado
            FROM
             (select e.nome estado, e.sigla as uf, p.status,count(*) as total from estados e
                left join localidades l on l.estado_id = e.id
                left join unidades u on u.localidade_id = l.id
                left join projeto_cnmes p on p.unidade_id = u.id
            group by e.nome, e.sigla, p.status
            order by e.sigla) t
            GROUP BY estado, uf, status) t2
        
        GROUP BY estado, uf
        ORDER BY uf");

        $this->totalCount = DB::table('projeto_cnmes')->count();

        $result = collect($result)->map(function($v){
            $v =  (array)$v;
            $v['percent'] = ($this->totalCount != 0) ? round(($v['total']/$this->totalCount),4)*100 : 0;
            return $v;
        })->toArray();  

        

        return response()->json($result); 
    }

    public function queryPrestadoras(Request $request, $etapa){
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
        WHERE e.tipo = ?
        ) as t
        GROUP BY unidade_responsavel_id, empresa, status_tarefa
        ) as t2
        GROUP BY unidade_responsavel_id, empresa
        ",[strtoupper($etapa)]);

        return response()->json($result); 
    }

    public function queryPrestadoraPorEstado(Request $request, $etapa,$empresaId){
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
        p.id as ṕrojeto_cnme_id,
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
        INNER JOIN etapas e on e.id = t.etapa_id and e.tipo = 'ENVIO'
        INNER JOIN projeto_cnmes p on p.id = e.projeto_cnme_id
        INNER JOIN unidades u2 on u2.id = p.unidade_id
        WHERE e.tipo = ?
        ) as t
        GROUP BY unidade_responsavel_id, empresa, estado,uf, status_tarefa
        ) as t2
        WHERE unidade_responsavel_id = ?
        GROUP BY unidade_responsavel_id, empresa, estado, uf        
        ",[strtoupper($etapa), $empresaId]);

        return response()->json($result); 
    }

    public function querPrazosTarefasPorEstado(Request $request, $etapa){
        $result = DB::select("
        SELECT 
   
        estado, uf,
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
        
        estado, uf,
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
        INNER JOIN etapas e on e.id = t.etapa_id and e.tipo = 'ENVIO'
        INNER JOIN projeto_cnmes p on p.id = e.projeto_cnme_id
        INNER JOIN unidades u2 on u2.id = p.unidade_id
        WHERE e.tipo = ?
        ) as t
        GROUP BY 
        estado,uf, status_tarefa
        ) as t2
        GROUP BY estado, uf 
        ", [strtoupper($etapa)]);

        return response()->json($result); 
    }

    public function querPrazosTarefasPorMunicipio(Request $request, $etapa, $uf){
        $result = DB::select("
        SELECT 
   
        estado, uf, municipio,
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
        
        estado, uf,
        municipio,
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
        INNER JOIN etapas e on e.id = t.etapa_id and e.tipo = 'ENVIO'
        INNER JOIN projeto_cnmes p on p.id = e.projeto_cnme_id
        INNER JOIN unidades u2 on u2.id = p.unidade_id
        WHERE e.tipo = ? and es.sigla = ?
        ) as t
        GROUP BY 
        estado,uf, municipio, status_tarefa
        ) as t2
        --WHERE unidade_responsavel_id = ?
        GROUP BY estado, uf, municipio
        ", [strtoupper($etapa), strtoupper($uf) ]);

        return response()->json($result); 
    }
}
