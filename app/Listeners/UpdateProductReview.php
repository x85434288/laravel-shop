<?php

namespace App\Listeners;

use DB;
use App\Events\OrderReviewed;
use App\Models\OrderItem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

//implements ShouldQueue 代表异步处理
class UpdateProductReview
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
     * @param  OrderReviewed  $event
     * @return void
     */
    //修改商品的评论数和平均评分
    public function handle(OrderReviewed $event)
    {
        //获取商品items 提前预加载，避免N+1问题
        $items = $event->getOrder()->items()->with(['product'])->get();
        foreach($items as $item){
            $result = OrderItem::query()
                ->where('product_id',$item->product_id)
                ->whereHas('order',function($query){
                    $query->whereNotNull('paid_at');
                })
                ->first([
                    DB::raw('count(*) as review_count'),
                    DB::raw('avg(rating) as rating')
                ]);
            $item->product->update([
                'review_count'=>$result->review_count,
                'rating'     => $result->rating
            ]);
        }

    }
}
