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
            'checklist_id' => $this->checklist_id,
            'projeto_cnme' => $this->projetoCnme,
            'itens_checklist' => ItemChecklistCnmeResource::collection($this->itemChecklistCnmes)
            
        ];
    }
}
