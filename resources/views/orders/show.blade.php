@extends('layouts.app')
@section('title', '查看订单')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>订单详情</h4>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>商品信息</th>
                            <th class="text-center">单价</th>
                            <th class="text-center">数量</th>
                            <th class="text-right item-amount">小计</th>
                        </tr>
                        </thead>
                        @foreach($order->items as $index => $item)
                            <tr>
                                <td class="product-info">
                                    <div class="preview">
                                        <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">
                                            <img src="{{ $item->product->image_url }}">
                                        </a>
                                    </div>
                                    <div>
            <span class="product-title">
               <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">{{ $item->product->title }}</a>
             </span>
                                        <span class="sku-title">{{ $item->sku->title }}</span>
                                    </div>
                                </td>
                                <td class="sku-price text-center vertical-middle">￥{{ $item->price }}</td>
                                <td class="sku-amount text-center vertical-middle">{{ $item->amount }}</td>
                                <td class="item-amount text-right vertical-middle">￥{{ number_format($item->price * $item->amount, 2, '.', '') }}</td>
                            </tr>
                        @endforeach
                        <tr><td colspan="4"></td></tr>
                    </table>
                    <div class="order-bottom">
                        <div class="order-info">

                            <div class="line"><div class="line-label">收货地址：</div><div class="line-value">{{ join(' ', $order->address) }}</div></div>
                            <div class="line"><div class="line-label">订单备注：</div><div class="line-value">{{ $order->remark ?: '-' }}</div></div>
                            <div class="line"><div class="line-label">订单编号：</div><div class="line-value">{{ $order->no }}</div></div>
                            <div class="line"><div class="line-label">物流状态：</div><div class="line-value">{{ \App\Models\Order::$shipStatusMap[$order->ship_status] }}</div></div>
                            @if($order->ship_data)
                                <div class="line"><div class="line-label">快递公司：</div><div class="line-value">{{ $order->ship_data['express_company'] }}   快递单号:{{ $order->ship_data['express_no'] }}</div></div>
                            @endif
                            <!-- 订单已支付，且退款状态不是未退款时展示退款信息 -->
                            @if($order->paid_at&&$order->refund_status !== \App\Models\Order::REFUND_STATUS_PENDING)
                                    <div class="line"><div class="line-label">退款状态：</div><div class="line-value">{{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}</div></div>
                                    <div class="line"><div class="line-label">退款理由：</div><div class="line-value">{{ $order->extra['refund_reason'] }}</div></div>
                            @endif

                        </div>
                        <div class="order-summary text-right">
                            <div class="total-amount">
                                <span>订单总价：</span>
                                <div class="value">￥{{ $order->total_amount }}</div>
                            </div>
                            <div>
                                <span>订单状态：</span>
                                <div class="value">
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

                                </div>

                                @if(!$order->closed && !$order->paid_at)
                                    <div class="payment-buttons">
                                        <a class="btn btn-primary btn-sm" href="{{ route('payment.alipay', $order) }}">支付宝支付</a>
                                        {{--<a class="btn btn-success btn-sm" href="{{ route('payment.wechat', $order) }}">微信支付</a>--}}
                                        <button class="btn btn-primary btn-sm" id="btn-wechat">微信支付</button>
                                    </div>
                                @endif

                            </div>
                            @if(isset($order->extra['refund_disagree_reason']))
                            <div>
                                <span>拒绝退款理由</span>
                                <div class="value">
                                    {{ $order->extra['refund_disagree_reason'] }}
                                </div>
                            </div>
                            @endif
                            <div class="receive-button">
                                @if($order->ship_status === \App\Models\Order::SHIP_STATUS_DELIVERED)
                                    <button class="btn btn-primary" id="order_received">确认收货</button>
                                @endif
                            </div>
                            <div class="refund-button">
                                @if($order->paid_at && $order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                    <button class="btn btn-primary" id="order_refund">申请退款</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scriptsAfterJs')
    <script>
        $(document).ready(function(){
            $('#btn-wechat').click(function(){
                swal({
                    // content 参数可以是一个 DOM 元素，这里我们用 jQuery 动态生成一个 img 标签，并通过 [0] 的方式获取到 DOM 元素
                    content : $('<img src="{{ route('payment.wechat',$order->id) }}" />')[0],
                    // buttons 参数可以设置按钮显示的文案
                    buttons : ['关闭','已完成付款']
                }).then(function(result){
                    // 如果用户点击了 已完成付款 按钮，则重新加载页面
                    if(result){
                        location.reload();
                    }
                });
            });

            $('#order_received').click(function(){
                swal({

                    title: "确认已经收到商品？",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    buttons: ['取消', '确认收到'],

                }).then(function(willReceived){
                    if(!willReceived){
                        return;
                    }
                    axios.post('{{ route("orders.received",$order->id) }}').then(
                            function(){
                                swal('成功','','success').then(
                                        function(){
                                            location.reload();
                                        }
                                );
                            }
                    );
                });
            });

            $("#order_refund").click(function(){
                swal({
                    text:'请输入退款理由',
                    content: 'input'
                }).then(function(input){
                    if(!input){
                        swal('退款理由不能为空','','error');
                        return;
                    }
                    axios.post('{{route("orders.apply_refund",$order->id)}}',{reason:input}).then(
                            function(){
                                swal('申请退款成功','','success').then(
                                        function(){
                                            location.reload();
                                        }
                                );
                            }
                    );
                });
            });




        });
    </script>
@endsection