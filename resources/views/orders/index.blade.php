@extends('layouts.app')
@section('title','订单列表')
@section('content')
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    订单列表
                </div>
                <div class="panel-body">
                    <ul class="list-group">

                        @if(count($orders)>0)
                            @foreach($orders as $order)

                                <li class="list-group-item">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">订单号：{{ $order->no }}<span class="pull-right">{{ $order->created_at }}</span></div>
                                        <div class="panel-body">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>商品信息</th>
                                                    <th>单价</th>
                                                    <th>数量</th>
                                                    <th>订单总价</th>
                                                    <th>状态</th>
                                                    <th>操作</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($order->items as $index => $item)
                                                    <tr>
                                                       <td class="product-info">
                                                           <div class="preview">
                                                               <a href="{{ route('products.show', $item->product->id) }}" target="_blank">
                                                                   <img src="{{ $item->product->image_url }}">
                                                               </a>
                                                           </div>
                                                           <div>
                                                               <span class="product-title text-center">
                                                                   <a href="{{ route('products.show', $item->product->id) }}" target="_blank">{{ $item->product->title }}</a>
                                                               </span>
                                                               <span class="sub-title text-center">
                                                                   <a href="{{ route('products.show', $item->product->id) }}" target="_blank">{{ $item->sku->title }}</a>
                                                               </span>
                                                           </div>
                                                       </td>
                                                       <td class="sku-price">¥{{ $item->price }}</td>
                                                       <td class="sku-amount">{{ $item->amount }}</td>
                                                        @if($index === 0)
                                                            <td rowspan="{{ count($order->items) }}" class="text-center">¥{{ $order->total_amount }}</td>
                                                            <td rowspan="{{ count($order->items) }}" class="text-center">
                                                                @if($order->paid_at)
                                                                    @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                                                        已支付
                                                                    @else
                                                                        {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
                                                                    @endif
                                                                @elseif($order->closed)
                                                                    已关闭
                                                                @else
                                                                    未支付<br>
                                                                    请于 {{ $order->created_at->addSecond(config('app.order_ttl'))->format('H:i') }}前完成，<br>
                                                                    否则将自动失效
                                                                @endif
                                                            </td>
                                                            <td rowspan="{{ count($order->items) }}">
                                                                <a href="{{ route('orders.show',$order->id) }}" class="btn btn-primary btn-xs">查看订单</a>
                                                                <a href="{{ route('orders.review.show',$order->id) }}" class="btn btn-primary btn-xs">{{ $order->reviewed?'查看评价':'评价' }}</a>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @else
                            没有订单
                        @endif

                    </ul>
                    <div class="pull-right">{{ $orders->render() }}</div>
                </div>
            </div>
        </div>
    </div>

@endsection