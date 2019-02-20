<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChecklistCnmeResource extends JsonResource
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
            'id'        => $this->id,
            'descricao' => $this->descricao,
            'avaliacao' => $this->avaliacao,
            'status'    => $this->status,
            'requisitos_html' => $this->requisitos,
            'requisitos_array' => $this->requisitosList(),
            'projeto_cnme' => $this->projetoCnme,
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at
            //'itens_checklist' => ItemChecklistCnmeResource::collection($this->itemChecklistCnmes)
            
        ];
    }
}
