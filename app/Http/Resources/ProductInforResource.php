<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductInforResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $this->loadMissing("variants.colorImage", "variants.sizeImage");
        $category = $this->category;
        $parent = $category->parent;
        $variants = $this->variants;

        $colorImages = $variants->unique("color")->map(function ($variant) {
            return $variant->colorImage->url ?? null;
        })->values();
        $sizeImages = $variants->unique("size")->map(function ($variant) {
            return $variant->sizeImage->url ?? null;
        })->values();

        $groupedQuantities = $variants->groupBy("color")->map(function ($variants, $color) {
            $prices = $variants->pluck("price")->toArray();
            $quantities = $variants->pluck("quantity")->toArray();
            return [
                "prices" => $prices,
                "quantities" => $quantities,
            ];
        });

        $discount_ranges = $this->discountRanges;
        return [
            "images" => $this->images()->pluck("url"),
            "cat_lv1_id" => $parent ? $parent->id : $category->id,
            "cat_lv2_id" => $parent ? $category->id : null,
            "condition_id" => $this->condition->id,
            "is_variant" => $this->is_variant ? true : false,
            "is_buy_more_discount" => $this->is_buy_more_discount ? true : false,
            "is_pre_order" => $this->is_pre_order ? true : false,
            "name" => $this->name,
            "detail" => $this->detail,
            "brand" => $this->brand,
            "inventory" => $this->inventory,
            "price" => $this->price,
            "promotional_price" => $this->promotional_price,
            "weight" => $this->weight,
            "length" => $this->length,
            "height" => $this->height,
            "width" => $this->width,
            "variant_names" => [$this->colors(), $this->sizes()],
            "variant_images" => [$colorImages, $sizeImages],
            "variants_item_quantity" => $groupedQuantities->pluck("quantities")->toArray(),
            "variants_item_price" => $groupedQuantities->pluck("prices")->toArray(),
            "discount_ranges_min" => $discount_ranges->pluck("min"),
            "discount_ranges_max" => $discount_ranges->pluck("max"),
            "discount_ranges_amount" => $discount_ranges->pluck("amount"),
        ];
    }
}
