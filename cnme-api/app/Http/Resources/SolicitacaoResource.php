<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SolicitacaoResource extends JsonResource
{
    
    // 'id','descricao', 'usuario_id','unidade_id','data_solicitacao'

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'status' => $this->status,
            'data_solicitacao' => (string)$this->data_solicitacao,
            'usuario' => $this->usuario,
            'unidade' => $this->unidade,
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at
        ];
    }
}
