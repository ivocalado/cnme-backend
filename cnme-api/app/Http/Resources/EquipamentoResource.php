<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EquipamentoResource extends JsonResource
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
            'fornecedor' => $this->fornecedor,
            'requisitos' => $this->requisitos,
            'removido'  =>  $this->deleted_at !== null,
            'tipo_equipamento' => new TipoEquipamentoResource($this->tipoEquipamento),
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at,
            'deleted_at' => (string) $this->deleted_at
        ];
    }
}
