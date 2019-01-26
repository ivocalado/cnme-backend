<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChecklistResource extends JsonResource
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
            'versao' => $this->versao,
            'descricao' => $this->descricao,
            'status' => $this->status,
            'ativo' => $this->status,
            'usuario' => $this->usuario,
            'itens_checklist' => $this->itemChecklists
        ];
    }
}
