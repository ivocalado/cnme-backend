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

    public function countPorStatus(Request $request){
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

        foreach($statusList as $status){
            if(!$result->contains('status',$status)){
                $v['status_count'] = 0;
                $v['status'] = $status;
                $result->push($v);
            }
        }
        
        return response()->json($result);

    }

    public function countPorEstado(Request $request){
        $projetosEstado = DB::table('projeto_cnmes')
        ->join('unidades', 'projeto_cnmes.unidade_id', '=', 'unidades.id')
        ->join('localidades', 'unidades.localidade_id','=','localidades.id')
        ->join('estados', 'localidades.estado_id', '=', 'estados.id')
        ->select(DB::raw('count(*) as estado_count, estados.nome'))
        ->groupBy('estados.nome')
        ->get();

        return response()->json($projetosEstado);
    }

    public function countPorEstadoAll(Request $request){
        $projetosEstado = DB::table('projeto_cnmes')
        ->join('unidades', 'projeto_cnmes.unidade_id', '=', 'unidades.id')
        ->join('localidades', 'unidades.localidade_id','=','localidades.id')
        ->join('estados', 'localidades.estado_id', '=', 'estados.id')
        ->select(DB::raw('count(*) as estado_count, estados.nome as estado'))
        ->groupBy('estados.nome')
        ->get();


        $estadosList = Estado::all();

        foreach($estadosList as $estado){
            if(!$projetosEstado->contains('estado',$estado->nome)){
                $v['estado_count'] = 0;
                $v['estado'] = $estado->nome;
                $projetosEstado->push($v);
            }
        }

        return response()->json($projetosEstado);
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


        $query->where('tarefas.status','=',Tarefa::STATUS_ANDAMENTO)
        ->whereNull('tarefas.data_fim')
        ->where('data_fim_prevista','<=',\DB::raw('NOW()'));

        return $query;
    }
    

    public function countAtrasados(Request $request){
        $query = $this->queryAtrasados($request);

        return response()->json($query->count());
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

    public function countStatusEstados(Request $request){
        $result = DB::select("SELECT estado, uf, 
        sum(total_criado) as total_criado,
        sum(total_planejamento) as total_planejamento,
        sum(total_enviado) as total_enviado,
        sum(total_entregue) as total_entregue,
        sum(total_instalado) as total_instalado,
        sum(total_finalizado) as total_finalizado
        FROM
            (SELECT 
            estado, 
            uf,
            CASE WHEN status = 'CRIADO' THEN SUM(total) ELSE 0 END total_criado,
            CASE WHEN status = 'PLANEJAMENTO' THEN SUM(total) ELSE 0 END total_planejamento,
            CASE WHEN status = 'ENVIADO' THEN SUM(total) ELSE 0 END total_enviado,
            CASE WHEN status = 'ENTREGUE' THEN SUM(total) ELSE 0 END total_entregue,
            CASE WHEN status = 'INSTALADO' THEN SUM(total) ELSE 0 END total_instalado,
            CASE WHEN status = 'FINALIZADO' THEN SUM(total) ELSE 0 END total_finalizado
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

        return response()->json($result); 
    }
}
