<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EtapaResource extends JsonResource
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
            'descricao' => $this->descricao,
            'status'    => $this->status,
            'tipo'      => $this->tipo,
            'data_inicio' => $this->getDataInicio(),
            'data_fim' => $this->getDataFim(),
            'data_inicio_prevista' => $this->getDataInicioPrevista(),
            'data_fim_prevista' => $this->getDataFimPrevista(),
            'tarefas' => TarefaResource::collection($this->tarefas),
            'usuario'          => $this->usuario,
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at

        ];
    }
}
