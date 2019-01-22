<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UnidadeResource extends JsonResource
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
            'email' => $this->email,
            'codigo_inep' => $this->codigo_inep,
            'diretor' => $this->diretor,
            'telefone' => $this->telefone,
            'url' => $this->url,
            'localidade' => new LocalidadeResource($this->localidade),
            'tipo_unidade' => $this->tipoUnidade,
            'responsavel' => $this->responsavel,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at];
    }
}
