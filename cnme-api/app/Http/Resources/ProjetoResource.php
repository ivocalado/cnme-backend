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
        $messages = $this->validate();

        $avisos = ($messages &&  array_key_exists("avisos", $messages) && count($messages["avisos"]) > 0);
        $infos = ($messages && array_key_exists("infos", $messages) && count($messages["infos"]) > 0);
        $erros = ($messages && array_key_exists("erros", $messages) && count($messages["erros"]) > 0);
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'status' => $this->status,
            'descricao' => $this->descricao,
            'unidade' => $this->unidade,
            'usuario' => new UserResource($this->usuario),
            'kit_id' => isset($this->kit)?$this->kit->id:null,
            'equipamentos_projeto' =>  EquipamentoProjetoResource::collection($this->equipamentoProjetos),
            'data_inicio' => (string)$this->data_inicio,
            'data_fim' => (string)$this->data_fim,
            'data_inicio_previsto' => (string)$this->data_inicio_previsto,
            'data_fim_previsto' => (string)$this->data_fim_previsto,
            'checklist_id' => $this->checklist_id,
            'checklist_at' => (string)$this->checklist_at,
            'usuario_checklist' => $this->usuarioChecklist,
            'avisos' => $avisos,
            'infos' => $infos,
            'erros' => $erros,
            'messages' => $this->validate(),
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at,
        ];
    }
}
