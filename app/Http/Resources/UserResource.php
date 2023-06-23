<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "name" => $this->fullname,
            "birthday" => $this->birthday,
            "avatar" => $this->avatar()->url,
            "gender" => $this->gender == 0 ? "Nam" : ($this->gender == 1 ? "Nữ" : "Không xác định"),
            "invite_code" => $this->invite_code,
            
        ];
    }
}