<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Exceptions\InvalidRequestException;

class ProductsController extends Controller
{
    //商品列表
    public function index(Product $product, Request $request)
    {
        //构造查询构造器
        $builder = $product->where('on_sale', true);
        //获取查询的数据
        $search = $request->input('search', '');
        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if($search){
            $like = "%".$search."%";
            // 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
            $builder->where(function($query) use ($like){
                $query->where('title', 'like' ,$like)
                    ->orWhere('description', 'like' ,$like)
                    ->orWhereHas('skus', function($query) use ($like){
                        $query->where('title', 'like' ,$like)
                            ->orWhere('description', 'like' ,$like);
                    });
            });
        }

        $orderStr = $request->input('order','');
        if($orderStr){
            //判断是否为asc或者desc结尾
            if(preg_match('/^(.+)_(asc|desc)/',$orderStr,$m)){
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if(in_array($m[1],['sold_count','rating','price'])){
                    //进行排序
                    $builder->orderBy($m[1], $m[2]);
                }

            }
        }
        $products = $builder->paginate(12);
        //$products = $product->where('on_sale', true)->paginate(12);
        $filters = [
            'search' => $search,
            'order'  => $orderStr
        ];

        return view('products.index',compact('products','filters'));
    }


    //商品详情
    public function show(Product $product, Request $request)
    {

        if(!$product->on_sale){
            throw new InvalidRequestException('此商品未上架');
        }
        $favor = false;
        //判断是否登录
        if($user = $request->user()){
            $favor = boolval($user->favoriteProducts()->find($product->id));
        }

        return view('products.show',compact('product','favor'));
    }

    //添加商品收藏
    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if($user->favoriteProducts()->find($product->id)){
            throw new InvalidRequestException('此商品已经被收藏');
        }
        $user->favoriteProducts()->attach($product->id);
        return [];
    }

    //删除商品收藏
    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product->id);
        return [];
    }

    public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(12);
        return view('products.favorites', compact('products'));
    }


}
