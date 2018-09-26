<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;
use App\Http\Requests\CartRequest;

class CartItemsController extends Controller
{
    //添加购物车
    public function store(CartRequest $request)
    {
        $user = $request->user();
        $product_sku_id = $request->input('sku_id');
        $amount = $request->input('amount');
        //如果此商品已经加入了购物车
        if($cart = $user->cartItems()->where('product_sku_id', $product_sku_id)->first()){
            //商品数量加上添加的数量
            $cart->update([
                'amount' => $cart->amount + $amount
            ]);
        }else{

            //如果不存在 此在数据库中添加此记录
            $cart = new CartItem([
                'amount' => $amount
            ]);

            $cart->user()->associate($user);
            $cart->productSku()->associate($product_sku_id);
            $cart->save();
        }

        return [];

    }


    //购物车商品列表
    public function index(Request $request)
    {

        $addresses = $request->user()->addresses()->orderBy('last_used_at','desc')->get();
        $carts = $request->user()->cartItems()->with(['productSku.product'])->get();
        return view('cart.index',compact('carts','addresses'));
        
    }

    //购物车删除商品
    public function remove(ProductSku $sku, Request $request)
    {
        $request->user()->cartItems()->where('product_sku_id', $sku->id)->delete();
        return [];
    }

}
