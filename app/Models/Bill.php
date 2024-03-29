<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public const STATUS_SUCCESS = 3;

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products($search = null)
    {
        $query = $this->hasManyThrough(Product::class, BillDetail::class, 'bill_id', 'id', 'id', 'product_id');

        if ($search !== null) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query;
    }

    public function details()
    {
        return $this->hasMany(BillDetail::class);
    }
}
