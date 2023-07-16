<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShopProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $products_count = $this->allProducts->count();
        return [
            "id" => $this->id,
            "name" => $this->name,
            "avatar" => $this->avatar,
            "banner" => $this->banner,
            "followers" => $this->followers->count(),
            "rating" => $this->rating,
            "products_count" => $products_count,
            "num_page" => ceil($products_count / 24),
            "products" => CompactProductResource::collection($this->products()),
            "is_followed" => $this->is_followed,
        ];
    }
}
