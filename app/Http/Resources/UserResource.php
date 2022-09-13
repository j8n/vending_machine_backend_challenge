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
            'id' => (int) $this->id,
            'username' => $this->username,
            'role' => [
                'id' => (int) $this->role->id,
                'name' => $this->role->name
            ],
            'deposit' => (int) $this->deposit
        ];
    }
}
