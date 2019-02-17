<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KitResource extends JsonResource
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
            'descricao' => $this->descricao,
            'usuario' =>  $this->usuario,
            'removido'  =>  $this->deleted_at !== null,
            'equipamentos' => EquipamentoResource::collection($this->equipamentos)
        ];
    }
}
