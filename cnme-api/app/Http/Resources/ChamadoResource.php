<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChamadoResource extends JsonResource
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
            'status' => new StatusChamadoResource($this->status),
            'assunto' => $this->assunto,
            'descricao' => $this->descricao,
            'projeto_cnme' => $this->projetoCnme,
            'tarefa' => $this->tarefa_id,
            'tipo' => new TipoChamadoResource($this->tipo),
            'unidade_responsavel' => $this->unidadeResponsavel,
            'usuario_responsavel' =>$this->usuario,
            'usuario' => $this->usuario,
            'unidade' => new UnidadeResource($this->usuario->unidade),
            'prioridade' => $this->prioridade,
            'privado' => $this->privado,
            'data_inicio' => (string)$this->data_inicio,
            'data_fim' => (string)$this->data_fim,
            'notificado_at' => (string)$this->notificado_at,
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at
        ];
    }
}
