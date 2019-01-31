<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemChecklistCnmeResource extends JsonResource
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
            'status'    => $this->status,
            'observacao'    => $this->observacao,
            'item_checklist'    => new ItemChecklistResource($this->itemChecklist),
        ];
    }
}
