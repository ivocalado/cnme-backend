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
            'solicitacao_cnme' => isset($this->solicitacaoCnme)?$this->solicitacaoCnme->id:null,
            'kit_id' => isset($this->kit)?$this->kit->id:null,
            'equipamentos_projeto' =>   $this->equipamentoProjetos,
            'data_criacao' => (string)$this->data_criacao,
            'data_implantacao_prevista' => (string)$this->data_implantacao_prevista,
            'data_implantacao_realizada' => (string)$this->data_implantacao_realizada,
            'data_inicio_entrega' => (string)$this->data_implantacao_realizada,
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at,

        ];
    }
}
