<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\OrderItem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

//  implements ShouldQueue 代表此监听器是异步执行的
class UpdateProductSoldCount implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        //获取订单信息
        $order = $event->getOrder();
        //预加载商品数据
        $order->load('items.product');
        foreach($order->items as $item){
            $product = $item->product;
            //计算对应商品的总销量
            $orderCount = OrderItem::query()
                ->where('product_id',$product->id)
                ->whereHas('order',function($query){
                    $query->whereNotNull('paid_at');  //计算已经支付
                })->sum('amount');
            //更新商品销量
            $product->update([
                'sold_count' => $orderCount,
            ]);
//            $product->update([
//                'sold_count' =>$product->sold_count+$item->amount
//            ]);
        }


    }
}
