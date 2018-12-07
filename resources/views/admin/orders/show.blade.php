<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">订单流水号：{{ $order->no }}</h3>
        <div class="box-tools">
            <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-default"><i class="fa fa-list"></i> 列表</a>
            </div>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td>买家：</td>
                <td>{{ $order->user->name }}</td>
                <td>支付时间：</td>
                <td>{{ $order->paid_at->format('Y-m-d H:i:s') }}</td>
            </tr>
            <tr>
                <td>支付方式：</td>
                <td>{{ $order->payment_method }}</td>
                <td>支付渠道单号：</td>
                <td>{{ $order->payment_no }}</td>
            </tr>
            <tr>
                <td>收货地址</td>
                <td colspan="3">{{ $order->address['address'] }} {{ $order->address['zip'] }} {{ $order->address['contact_name'] }} {{ $order->address['contact_phone'] }}</td>
            </tr>
            <tr>
                <td rowspan="{{ $order->items->count() + 1 }}">商品列表</td>
                <td>商品名称</td>
                <td>单价</td>
                <td>数量</td>
            </tr>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->title }} {{ $item->sku->title }}</td>
                    <td>￥{{ $item->price }}</td>
                    <td>{{ $item->amount }}</td>
                </tr>
            @endforeach
            <tr>
                <td>订单金额：</td>
                <td>￥{{ $order->total_amount }}</td>
                <td>发货状态：</td>
                <td>{{ \App\Models\Order::$shipStatusMap[$order->ship_status] }}</td>
            </tr>
            <!-- 未发货显示物流表单  -->
            @if($order->ship_status === \App\Models\Order::SHIP_STATUS_PENDING)
                @if($order->refund_status !== \App\Models\Order::REFUND_STATUS_SUCCESS)
                <tr>
                    <td colspan="4">
                        <form action="{{ route('admin.orders.ship',$order->id) }}" method="post" class="form-inline">
                            <div class="form-group" {{ $errors->has('express_company')?'has_error':'' }}>
                                <label class="control-label">物流公司:</label>
                                <input class="form-control" name="express_company" type="text">
                                @if($errors->has('express_company'))
                                    @foreach($errors->get('express_company') as $msg)
                                        <span class="help-block">{{ $msg }}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="form-group" {{ $errors->has('express_no')?'has_error':'' }}>
                                <label class="control-label">物流订单:</label>
                                <input class="form-control" name="express_no" type="text">
                                @if($errors->has('express_no'))
                                    @foreach($errors->get('express_no') as $msg)
                                        <span class="help-block">{{ $msg }}</span>
                                    @endforeach
                                @endif
                            </div>
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-primary">提交</button>
                        </form>
                    </td>
                </tr>
                @endif
                <!-- 发货显示物流状态和订单号  -->
                @else
                <tr>
                    <td>快递公司</td>
                    <td>{{ $order->ship_data['express_company'] }}</td>
                    <td>快递单号</td>
                    <td>{{ $order->ship_data['express_no'] }}</td>
                </tr>
            @endif

            @if($order->refund_status !== \App\Models\Order::REFUND_STATUS_PENDING)
                <tr>
                    <td>退款状态：</td>
                    <td colspan="2">{{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}，理由：{{ $order->extra['refund_reason'] }}</td>
                    <td>
                        <!-- 如果订单退款状态是已申请，则展示处理按钮 -->
                        @if($order->refund_status === \App\Models\Order::REFUND_STATUS_APPLIED)
                            <button class="btn btn-sm btn-success" id="btn-refund-agree">同意</button>
                            <button class="btn btn-sm btn-danger" id="btn-refund-disagree">不同意</button>
                        @endif
                    </td>
                </tr>
            @endif

            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function(){

        //点击不同意按钮
        $("#btn-refund-disagree").click(function(){
            swal({
                title: '输入拒绝退款理由',
                type: 'input',
                showCancelButton: true,
                closeOnConfirm: false,
                confirmButtonText: "确认",
                cancelButtonText: "取消"
            },function(inputValue){
                if(inputValue === false){
                    return;
                }
                if(!inputValue){
                    swal('拒绝退款理由不能为空','','error');
                    return;
                }

                $.ajax({
                    url : "{{ route('admin.orders.handle_refund',$order->id) }}",
                    type : 'post',
                    data : JSON.stringify({   // 将请求变成 JSON 字符串
                        agree: false,  // 拒绝申请
                        reason: inputValue,
                        // 带上 CSRF Token
                        // Laravel-Admin 页面里可以通过 LA.token 获得 CSRF Token
                        _token: LA.token
                    }),
                    contentType : "application/json",
                    success: function(data){
                        swal({
                            title : '成功',
                            type : 'success'
                        },function(){
                            location.reload();
                        });
                    }

                });
            });
        });

        //点击同意按钮
        $("#btn-refund-agree").click(function(){
            swal({
                title: '确认要将款项退还给用户？',
                type: 'warning',
                showCancelButton: true,
                closeOnConfirm: false,
                confirmButtonText: "确认",
                cancelButtonText: "取消"
            },function(ret){
                //如果点击取消，则返回
                if(!ret){
                    return;
                }
                //点击确定，ajax提交请求到接口
                $.ajax({
                    url : "{{ route('admin.orders.handle_refund',$order->id) }}",
                    type : "post",
                    data : JSON.stringify({
                        agree : true,  // 接受申请
                        // 带上 CSRF Token
                        // Laravel-Admin 页面里可以通过 LA.token 获得 CSRF Token
                        _token : LA.token
                    }),
                    contentType : 'application/json',
                    success : function(data){
                        swal({
                            title : '成功',
                            type : 'success'
                        },function(){
                            location.reload();
                        });
                    }
                });
            });
        });
    });
</script>