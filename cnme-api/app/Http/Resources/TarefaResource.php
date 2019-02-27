<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TarefaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'etapa_id' =>  $this->etapa->id,
            'etapa_tipo' => $this->etapa->tipo,  
            'numero' => $this->numero,
            'descricao' => $this->descricao,
            'status' => $this->status,
            'link_externo' => $this->link_externo,
            'data_inicio_prevista' => (string)$this->data_inicio_prevista,
            'data_fim_prevista' => (string)$this->data_fim_prevista,
            'data_inicio' => (string)$this->data_inicio,
            'data_fim'  => (string)$this->data_fim,
            'usuario'   => $this->usuario,
            'responsavel' => $this->responsavel,
            'unidade_responsavel' => $this->unidadeResponsavel,
            'equipamentos_projeto' => EquipamentoProjetoResource::collection($this->equipamentosProjetos),
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at

        ];
    }
}
