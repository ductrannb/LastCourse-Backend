<?php

namespace App\Repositories;

use App\Models\DiscountRange;

class DiscountRangeRepository
{
    public function create(array $data)
    {
        return DiscountRange::create($data);
    }
}
