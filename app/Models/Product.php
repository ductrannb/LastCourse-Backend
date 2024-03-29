<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public const STATUS_HIDDEN = "Đã ẩn";
    public const STATUS_AVAILABLE = "Còn hàng";
    public const STATUS_UNAVAILABLE = "Hết hàng";

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function filterVariants($color = null, $size = null)
    {
        return $this->variants()
            ->when($color, function ($query) use ($color) {
                return $query->where("color", $color);
            })
            ->when($size, function ($query) use ($size) {
                return $query->where("size", $size);
            })
            ->get();
    }

    public function filterRating($rating = null)
    {
        return $this->hasMany(Comment::class)
            ->when($rating !== null, function ($query) use ($rating) {
                $query->where("rating", $rating);
            })
            ->get();
    }

    public function condition()
    {
        return $this->belongsTo(ProductCondition::class);
    }

    public function getShippingFee()
    {
        // Cong thuc tinh gia ship
        return 0.003 * $this->weight + 0.0000001 * $this->length * $this->width * $this->height;
    }

    public function relates()
    {
        return Product::whereIn("cat_id", [$this->cat_id, $this->category->parent_id])
            ->orderBy("sold", "desc")
            ->whereNot("id", $this->id)
            ->take(12)->get();
    }

    public function category()
    {
        return $this->belongsTo(Category::class, "cat_id");
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function colors()
    {
        return $this->hasMany(ProductVariant::class)
            ->select("color")
            ->distinct()
            ->pluck("color")
            ->toArray();
    }

    public function sizes()
    {
        return $this->hasMany(ProductVariant::class)
            ->select("size")
            ->distinct()
            ->pluck("size")
            ->toArray();
    }

    public function comments($page = null, $rating = null)
    {
        $per_page = 6;
        $offset = ($page - 1) * $per_page;

        return $this->hasMany(Comment::class)
            ->with("user")
            ->when($rating != null, function ($query) use ($rating) {
                return $query->where("rating", $rating);
            })
            ->orderBy("created_at", "desc")
            ->skip($offset)
            ->take($per_page)->get();
    }

    public function allComments()
    {
        return $this->hasMany(Comment::class)->orderByDesc("created_at");
    }

    public function getAverageRating()
    {
        $evaluates = $this->allComments;
        return $evaluates->sum("rating") / $evaluates->count();
    }

    public function discountRanges()
    {
        return $this->hasMany(DiscountRange::class);
    }

    public function setImagesAttribute($value)
    {
        $this->attributes["images"] = json_encode($value);
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }

    public function getRatingAttribute($value)
    {
        return round($value, 1);
    }
}
