<?php
namespace App\Services;

use Auth;
use App\Models\CartItem;


class CartService{

    //获取购物车列表
    public function get()
    {
        return Auth::user()->cartItems()->with(['productSku.product'])->get();
    }


    //添加商品到购物车
    public function save($skuId, $amount)
    {
        if($cart = Auth::user()->cartItems()->where('product_sku_id', $skuId)->first()){
            $cart->update([
                'amount' => $cart+$amount
            ]);
        }else{
            //如果不存在 添加此条记录
            $cart = new CartItem([
                'amount' => $amount
            ]);
            $cart->user()->associate(Auth::user());
            $cart->productSku()->associate($skuId);
            $cart->save();
        }


    }

    //删除购物车中的商品
    public function remove($skuIds)
    {
        // 可以传单个 ID，也可以传 ID 数组
        if (!is_array($skuIds)) {
            $skuIds = [$skuIds];
        }
        Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
    }

}