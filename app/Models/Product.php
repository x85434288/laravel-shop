<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductSku;

class Product extends Model
{
    //
    protected $fillable = ['title', 'description', 'image', 'on_sale', 'rating', 'sold_count', 'review_count', 'price'];

    protected $casts = [
        'on_sale' => 'boolean', // on_sale 是一个布尔类型的字段
    ];

    //商品与商品sku关联
    public function sku()
    {

        return $this->hasMany(ProductSku::class);
    }

}