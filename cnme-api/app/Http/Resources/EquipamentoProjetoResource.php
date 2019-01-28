<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EquipamentoProjetoResource extends JsonResource
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
            'id'            => $this->id,
            'detalhes'      => $this->detalhes,
            'observacao'    => $this->observacao,
            'status'        => $this->status,
            'projeto'       => $this->projetoCnme->id,
            'equipamento'   => new EquipamentoResource($this->equipamento)
        ];
    }
}
