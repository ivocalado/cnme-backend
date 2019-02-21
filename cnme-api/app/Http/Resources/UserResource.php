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
            'token' => $this->remember_token,
            'email_verified_at' => $this->email_verified_at,
            'ativo' => $this->email_verified_at !== null,
            'telefone' => $this->telefone,
            'cpf' => $this->cpf,
            'funcao' => $this->funcao,
            'tipo' => $this->tipo,
            'unidade' => new UnidadeResource($this->unidade),
            'removido'  =>  $this->deleted_at !== null,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'deleted_at' => (string) $this->deleted_at

        ];
    }
}
