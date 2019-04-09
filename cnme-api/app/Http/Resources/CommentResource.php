<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $str = "App\\Models\\".ucfirst($this->comment_type);
        $array = explode("\\",$str);
        $classe = end($array);


        return [
            'id' => $this->id,
            'content' => $this->content,
            'comment_type' => $this->comment_type,
            'classe' => $classe,
            'commentable' => $this->comment,
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at,
            'tipo' => $this->tipo,
            'usuario'=> $this->usuario
        ];
    }
}
