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
            //'usuario'          => new UserResource($this->usuario),
            'usuario'          => $this->usuario,
            //'projeto'          => new ProjetoResource($this->projetoCnme),
            'projeto'          => $this->projetoCnme,
            'data_inicio_prevista' => (string)$this->data_inicio_prevista,
            'data_fim_prevista' => (string)$this->data_fim_prevista,
            'data_inicio' => (string)$this->data_inicio,
            'data_fim' => (string)$this->data_fim

        ];
    }
}
