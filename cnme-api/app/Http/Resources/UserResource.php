<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'nome' => $this->name,
            'name' => $this->name,
            'remember_token' => $this->remember_token,
            'telefone' => $this->telefone,
            'cpf' => $this->cpf,
            'funcao' => $this->funcao,
            'tipo' => $this->tipo,
            'unidade' => new UnidadeResource($this->unidade)

        ];
    }
}
