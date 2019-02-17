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
            'usuario'          => $this->usuario,
            'tarefas' => TarefaResource::collection($this->tarefas)

        ];
    }
}
