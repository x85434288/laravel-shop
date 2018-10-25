<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Models\Order;
use Carbon\Carbon;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use Auth;
use App\Services\CartService;
use App\Services\OrderService;
use App\Http\Requests\ReviewedRequest;
use App\Events\OrderReviewed;


class OrdersController extends Controller
{
//    public function store(OrderRequest $request)
//    {
//        $user  = $request->user();
//        // 开启一个数据库事务
//        $order = \DB::transaction(function () use ($user, $request) {
//            $address = UserAddress::find($request->input('address_id'));
//            // 更新此地址的最后使用时间
//            $address->update(['last_used_at' => Carbon::now()]);
//            // 创建一个订单
//            $order   = new Order([
//                'address'      => [ // 将地址信息放入订单中
//                    'address'       => $address->full_address,
//                    'zip'           => $address->zip,
//                    'contact_name'  => $address->contact_name,
//                    'contact_phone' => $address->contact_phone,
//                ],
//                'remark'       => $request->input('extra'),
//                'total_amount' => 0,
//            ]);
//            // 订单关联到当前用户
//            $order->user()->associate($user);
//            // 写入数据库
//            $order->save();
//            $totalAmount = 0;
//            $items       = $request->input('items');
//            // 遍历用户提交的 SKU
//            foreach ($items as $data) {
//                $sku  = ProductSku::find($data['sku_id']);
//                // 创建一个 OrderItem 并直接与当前订单关联
//                $item = $order->items()->make([
//                    'amount' => $data['amount'],
//                    'price'  => $sku->price,
//                ]);
//                $item->product()->associate($sku->product_id);
//                $item->sku()->associate($sku);
//                $item->save();
//                $totalAmount += $sku->price * $data['amount'];
//                //减库存
//                if($sku->decreaseStock($data['amount'])<=0){
//                    throw new InvalidRequestException('该商品库存不足');
//                }
//            }
//
//            // 更新订单总金额
//            $order->update(['total_amount' => $totalAmount]);
//
//            // 将下单的商品从购物车中移除
//            $skuIds = collect($request->input('items'))->pluck('sku_id')->toArray();//返回数组
//            //$user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
//            app(CartService::class)->remove($skuIds);
//            return $order;
//        });
//        //触发关闭订单的延时任务
//        $this->dispatch(new CloseOrder($order ,config('app.order_ttl')));
//        return $order;
//    }

    //添加订单
    public function store(OrderRequest $request, OrderService $service)
    {

        $user = Auth::user();
        $address = UserAddress::find($request->input('address_id'));
        $remark = $request->input('extra');
        $items = $request->input('items');
        return $service->add($user, $address, $remark, $items);
    }

    //个人订单列表
    public function index(Order $order)
    {
        $user = Auth::user();
        $orders = $order->with(['items.product','items.sku'])
            ->where('user_id', $user->id)
            ->orderBy('created_at','desc')
            ->paginate(5);

        return view('orders.index',compact('orders'));
    }


    //订单详情页
    public function show(Order $order)
    {

        $this->authorize('own', $order);
        //load() 方法与上一章节介绍的 with() 预加载方法有些类似，称为 延迟预加载，
        //不同点在于 load() 是在已经查询出来的模型上调用，
        //而 with() 则是在 ORM 查询构造器上调用
        $order = $order->load(['items.sku','items.product']);
        return view('orders.show',compact('order'));
    }

    //用户确认收货
    public function received(Order $order)
    {
        $this->authorize('own', $order);

        //判断订单是否发货
        if($order->ship_status !== Order::SHIP_STATUS_DELIVERED){
            throw new InvalidRequestException('订单的支付状态不正确');
        }

        $order->update(['ship_status'=> Order::SHIP_STATUS_RECEIVED]);

        //返回订单信息
        return $order;
    }


    //显示商品评价
    public function review(Order $order)
    {

        $this->authorize('own', $order);

        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }

        $orders = $order->load(['items.sku', 'items.product']);
        return view('orders.review',compact('orders'));

    }

    //添加商品评价
    public function sendReview(Order $order, ReviewedRequest $request)
    {
        $this->authorize('own', $order);
        //如果未支付 不能评价
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        //判断是否评价
        if($order->reviewed){
            throw new InvalidRequestException('不可重复评价');
        }
        $reviews = $request->input('reviews');
        //开启事务
        \DB::transaction(function() use($order, $reviews){

            foreach($reviews as $review){
                // 遍历用户提交的数据
                $orderItem = $order->items()->find($review['id']);
                //添加订单评论与打分
                $orderItem->update([
                    'rating' => $review['rating'],
                    'review' => $review['review'],
                    'reviewed_at' => Carbon::now()
                ]);

            }
            //修改订单状态为已评论
            $order->update(['reviewed'=>true]);
            //更新商品的评论数
            event(new OrderReviewed($order));

        });

        //返回上一步操作
        return redirect()->back();
    }

}