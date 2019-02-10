<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjetoResource extends JsonResource
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
            'numero' => $this->numero,
            'status' => $this->status,
            'descricao' => $this->descricao,
            'unidade' => $this->unidade,
            'usuario' => new UserResource($this->usuario),
            //'solicitacao_cnme' => isset($this->solicitacaoCnme)?$this->solicitacaoCnme->id:null,
            'kit_id' => isset($this->kit)?$this->kit->id:null,
            'equipamentos_projeto' =>   $this->equipamentoProjetos,
            'data_inicio' => (string)$this->data_inicio,
            'data_fim' => (string)$this->data_fim,
            'data_inicio_prevista' => (string)$this->data_inicio_prevista,
            'data_fim_realizada' => (string)$this->data_fim_realizada,
        ];
    }
}
