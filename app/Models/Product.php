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

    public function images()
    {
        return Image::whereIn("id", $this->image_ids)->get();
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function discountRanges()
    {
        return $this->hasMany(DiscountRange::class);
    }

    public function setImageIdsAttribute($value)
    {
        $this->attributes['image_ids'] = json_encode($value);
    }

    public function getImageIdsAttribute($value)
    {
        return json_decode($value);
    }
}
