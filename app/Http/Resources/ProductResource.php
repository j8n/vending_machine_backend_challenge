<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'id' => $this->id,
            'productName' => $this->productName,
            'amountAvailable' => $this->amountAvailable,
            'cost' => $this->cost,
            'seller' => [
                'id' => $this->sellerId,
                'username' => $this->seller->username
            ]
        ];
    }
}
