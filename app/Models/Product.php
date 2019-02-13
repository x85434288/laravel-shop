<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductSku;
use Illuminate\Support\Str;

class Product extends Model
{
    //
    protected $fillable = ['title', 'description', 'image', 'on_sale', 'rating', 'sold_count', 'review_count', 'price'];

    protected $casts = [
        'on_sale' => 'boolean', // on_sale 是一个布尔类型的字段
    ];

    //商品与商品sku关联
    public function skus()
    {

        return $this->hasMany(ProductSku::class);
    }

    public function getImageUrlAttribute()
    {
        //取名为image_url是为了不和数据库中image形成干扰
        $value = $this->attributes['image'];
        //判断是否为云空间
        if(Str::startsWith($value, ['http://', 'https://'])){
            return $value;
        }
        return '/upload/'.$value;
        
    }



    public function favoriteUser()
    {
        return $this->belongsToMany(User::class, 'user_favorite_products')
            ->withTimestamps()
            ->orderBy('user_favorite_products.created_at','desc');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

}
