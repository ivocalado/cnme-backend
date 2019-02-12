<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjetoResource extends JsonResource
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
            'numero' => $this->numero,
            'status' => $this->status,
            'descricao' => $this->descricao,
            'unidade' => $this->unidade,
            'usuario' => new UserResource($this->usuario),
            'kit_id' => isset($this->kit)?$this->kit->id:null,
            'equipamentos_projeto' =>  EquipamentoProjetoResource::collection($this->equipamentoProjetos),
            'data_inicio' => (string)$this->data_inicio,
            'data_fim' => (string)$this->data_fim,
            'data_inicio_previsto' => (string)$this->data_inicio_previsto,
            'data_fim_previsto' => (string)$this->data_fim_previsto,
        ];
    }
}
