<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductShopResource extends JsonResource
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
            "name" => $this->name,
            "image" => $this->firstImage->url ?? "#",
            "price" => $this->price,
            "status" => $this->is_hidden ? Product::STATUS_HIDDEN
            : ($this->inventory <= 0 ? Product::STATUS_UNAVAILABLE : Product::STATUS_AVAILABLE),
            "warehouse" => $this->warehouse->name,
            "sold" => $this->sold,
            "inventory" => $this->inventory,
        ];
    }
}
