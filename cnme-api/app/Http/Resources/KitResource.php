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
            'descricao' => $this->descricao,
            'versao' => $this->versao,
            'status' => $this->status,
            'data_inicio' => (string) $this->data_inicio,
            'data_fim' => (string) $this->data_inicio,
            'usuario' =>  $this->usuario,
            'equipamentos' => $this->equipamentos
        ];
    }
}
