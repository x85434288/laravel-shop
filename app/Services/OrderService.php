<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/29
 * Time: 9:33
 */

namespace App\Services;

use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use Carbon\Carbon;
use App\Services\CartService;
use App\Jobs\CloseOrder;
use App\Exceptions\InvalidRequestException;

class OrderService
{

    public function add($user, UserAddress $address, $remark, $items)
    {
        //开启事物
        $order = \DB::transaction(function()use($user, $address, $remark, $items){
            // 更新此地址的最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);
            //创建一个新订单
            $order = new Order([
                'address' => [
                    'address' =>$address->full_address,
                    'zip' =>$address->zip,
                    'contact_name' =>$address->contact_name,
                    'contact_phone' =>$address->contact_phone,
                ],
                'total_amount' =>0,
                'remark' =>$remark
            ]);

            //和用户表关联
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;
            foreach($items as $data){
                $sku = ProductSku::find($data['sku_id']);
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price'  => $sku->price
                 ]);
                $item->sku()->associate($sku);
                $item->product()->associate($sku->product_id);
                $item->save();
                $totalAmount +=$data['amount'] * $sku->price;
                //减库存
                if($sku->decreaseStock($data['amount'])<0){
                    throw new InvalidRequestException('库存不足');
                }
            }
            //更新订单总价格
            $order->update([
                'total_amount' => $totalAmount,
            ]);
            $skuIds = collect($items)->pluck('sku_id')->toArray();
            //清空购物车
            app(CartService::class)->remove($skuIds);

            return $order;
        });
        //延时关闭订单
        dispatch(new CloseOrder($order, config('app.order_ttl')));
        return $order;
    }

}