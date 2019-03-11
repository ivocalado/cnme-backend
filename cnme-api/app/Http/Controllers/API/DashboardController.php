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
use App\Services\QueryComponent;
use App\Services\EstadoQueryComponent;
use App\Services\UnidadeQueryComponent;
use App\Services\PrestadorQueryComponent;
use App\Services\TempoQueryComponent;

class DashboardController extends Controller
{
    public function countProjetos(Request $request){
        try {
            $queryComponent = new QueryComponent();

            $total = $queryComponent->countProjetos($request->uf);

            return response()->json($total);
        }catch(\Exception $e){
            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    protected $totalCount;
    public function queryPorStatus(Request $request){
        $queryComponent = new QueryComponent();

        $result = $queryComponent->queryPorStatus($request->uf);
        $statusList = ProjetoCnme::status();

        $this->totalCount =  $queryComponent->countProjetos($request->uf);

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
        $estadoComponent = new EstadoQueryComponent();
        $result = $estadoComponent->queryPorEstado();

        $queryComponent = new QueryComponent();
        $this->totalCount = $queryComponent->countProjetos($request->uf);

        $result = collect($result)->map(function($v){
            $v =  (array)$v;
            $v['estado_percent'] = ($this->totalCount != 0) ? round(($v['estado_count']/$this->totalCount),4)*100 : 0;
            return $v;
        })->toArray();    

        return response()->json($result);
    }

    public function queryPorEstadoAll(Request $request){
        $estadoComponent = new EstadoQueryComponent();
        $projetosEstado =  $estadoComponent->queryPorEstado();


        $estadosList = Estado::all();

        foreach($estadosList as $estado){
            if(!$projetosEstado->contains('estado',$estado->nome)){
                $v['estado_count'] = 0;
                $v['estado'] = $estado->nome;
                $projetosEstado->push($v);
            }
        }

        $queryComponent = new QueryComponent();
        $this->totalCount = $queryComponent->countProjetos($request->uf);

        $result = collect($projetosEstado)->map(function($v){
            $v =  (array)$v;
            $v['estado_percent'] = ($this->totalCount != 0) ? round(($v['estado_count']/$this->totalCount),4)*100 : 0;
            return $v;
        })->toArray();  

        return response()->json($result);
    }
    
    public function countAtrasados(Request $request){
        $queryComponent = new QueryComponent();
        $totalAtrasados = $queryComponent->countAtrasados($request->uf);

        return response()->json($totalAtrasados);
    }

    public function countAtrasadosValue(Request $request){
        $queryComponent = new QueryComponent();
        $query = $queryComponent->countAtrasados($request->uf);

        return $query->count();
    }

    public function queryExtrato(Request $request){

        $queryComponent = new QueryComponent();

        $totalProjetos = $queryComponent->countProjetos($request->uf);
        $totalAtrasados = $queryComponent->countAtrasados($request->uf);
        $totalPlanejamento = $queryComponent->countPlanejamento($request->uf);
        $totalAndamento = $queryComponent->countAndamento($request->uf);
        $totalConcluidos = $queryComponent->countConcluidos($request->uf);

        $result = []; 
        $result["total_projetos"] = $totalProjetos;
        $result["total_atrasados"] = $totalAtrasados;
        $result["total_planejamento"] = $totalPlanejamento;
        $result["total_andamento"] = $totalAndamento;
        $result["total_concluidos"] = $totalConcluidos;

        $result['percent_atrasados'] = ($totalAndamento != 0) ? round(($totalAtrasados/$totalAndamento)*100,2) : 0;
        $result['percent_concluidos'] = ($totalProjetos != 0) ? round(($totalConcluidos/$totalProjetos)*100,2) : 0;
        


        return response()->json($result);
    }

    public function countAtrasadosPorEtapa(Request $request){
        $queryComponent = new QueryComponent();
        $total = $queryComponent->countAtrasados($request->uf, $request->etapa);

        return response()->json($total);

    }

    public function countGestoresNaoConfirmados(Request $request){
        $unidadeQuery = new UnidadeQueryComponent();
        $naoConfirmados = $unidadeQuery->countGestoresNaoConfirmados($request->uf);

        return response()->json($naoConfirmados);

    }

    public function queryStatusEstados(Request $request){
       $estadoComponent = new EstadoQueryComponent();

       $result = $estadoComponent->queryStatusEstados();

       return response()->json($result);
    }

    public function queryPrestadoras(Request $request, $etapa){
        $prestadoQuery = new PrestadorQueryComponent();
        $result = $prestadoQuery->queryPrestadoras($etapa);

        return response()->json($result);
    }

    public function queryPrestadoraPorEstado(Request $request, $etapa,$empresaId){
        $prestadoQuery = new PrestadorQueryComponent();
        $result = $prestadoQuery->queryPrestadoraPorEstado($etapa,$empresaId);

        return response()->json($result); 
    }

    public function querPrazosTarefasPorEstado(Request $request, $etapa){
        $estadoComponent = new EstadoQueryComponent();
        $result = $estadoComponent->querPrazosTarefasPorEstado($etapa);
        return response()->json($result); 
    }

    public function querPrazosTarefasPorMunicipio(Request $request, $etapa, $uf){
        $estadoComponent = new EstadoQueryComponent();
        $result = $estadoComponent->querPrazosTarefasPorMunicipio($etapa, $uf);
        return response()->json($result); 
    }

    protected $newValue = array();
    public function queryProjetosEtapasExtrato(Request $request){
        $queryComponent = new QueryComponent();
        $result =  $queryComponent->queryProjetosEtapasExtrato();

        collect($result)->map(function($v){
            $v =  (array)$v;
            $etapa = \strtolower($v["etapa"]);
            $this->newValue[$etapa."_total"] = $v["total"];
            $this->newValue[$etapa."_total_andamento"] = $v["total_andamento"];
            $this->newValue[$etapa."_total_atrasada"] = $v["total_atrasada"];
            $this->newValue[$etapa."_total_concluida"] = $v["total_concluida"];
            $this->newValue[$etapa."_total_concluida_atrasada"] = $v["total_concluida_atrasada"];

            $this->newValue[$etapa."_percent_andamento"] = $v["total_andamento"] != 0 ? 
                                                        round(($v["total_atrasada"]/$v["total_andamento"])*100, 2):0;

            return $v;
        })->toArray();  

        $result = ($this->newValue);

        return response()->json($result); 
    }

    public function queryProjeto12Meses(){
        $tempoComponent = new TempoQueryComponent();

        $result = $tempoComponent->queryProjeto12Meses();

        return response()->json($result); 
    }

    public function queryProjetoEstadoAno(Request $request){
        $tempoComponent = new TempoQueryComponent();

        $result = $tempoComponent->queryProjetoEstadoAno($request->ano, $request->uf, $request->regiao);

        return response()->json($result); 
    }
}
