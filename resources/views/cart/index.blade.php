@extends('layouts.app')
@section('title','购物车')
@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">我的购物车</div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>商品信息</th>
                                <th>单价</th>
                                <th>数量</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody class="product_list">
                        @if(count($carts)>0)
                            @foreach($carts as $cart)
                                <tr data-id = '{{ $cart->productSku->id }}'>
                                    <td><input type="checkbox" name="select" value="{{ $cart->productSku->id }}" {{ $cart->productSku->product->on_sale?'checked':'disable' }}></td>
                                    <td class="product_info">
                                        <div class="preview">
                                            <a href="{{ route('products.show', $cart->productSku->product->id) }}"><img src="{{ $cart->productSku->product->image_url }}"> </a>
                                        </div>
                                        <div @if(!$cart->productSku->product->on_sale) class="not_on_sale" @endif>
                                            <span class="product_title">
                                                <a target="_blank" href="{{ route('products.show', $cart->productSku->product->id) }}">{{ $cart->productSku->title }}</a>
                                            </span>
                                            @if(!$cart->productSku->product->on_sale)
                                                <span class="warning">此商品已下架</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td><span class="price">{{ $cart->productSku->price }}</span></td>
                                    <td>
                                        <input type="text" class="form-control input-sm amount" @if(!$cart->productSku->product->on_sale) disabled @endif name="amount" value="{{ $cart->amount }}">
                                    </td>
                                    <td><button type="submit" class="btn btn-xs btn-danger btn-remove">移除</button></td>
                                </tr>
                            @endforeach
                        @else
                            未收藏商品
                        @endif
                        </tbody>
                    </table>
                    <div>
                        <form class="form-horizontal" role="form" id="order-form">
                            <div class="form-group">
                                <label class="control-label col-sm-3">
                                    请选择收货地址：
                                </label>
                                <div class="col-sm-9 col-md-7">
                                    <select class="form-control" name="address">
                                        @foreach($addresses as $address)
                                            <option value="{{ $address->id }}">{{ $address->full_address }} {{ $address->contact_name }} {{ $address->contact_phone }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-3">订单备注：</label>
                                <div class="col-sm-9 col-md-7">
                                    <textarea name="extra" class="form-control" cols="3"></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-3">
                                    <button type="button" class="btn btn-primary btn-create-order">提交订单</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scriptsAfterJs')
<script>
    $(document).ready(function(){
        //监听移除按钮事件
        //$(this)获取当前点击 移除 按钮的 jquery对象
        //closest('tr') 可以获取到匹配选择器第一个祖先元素，在这里就是当前点击的 移除 按钮之上的 <tr> 标签
        $('.btn-remove').click(function(){
            var id = $(this).closest('tr').data('id');
            //console.log(id);
            swal({
                title:'确认要删除吗',
                icon:'error',
                buttons:['取消','确定'],
                dangerMode:true
            }).then(function(willDelete){
                // 用户点击 确定 按钮，willDelete 的值就会是 true，否则为 false
                if(!willDelete){
                    return;
                }

                axios.delete('/cart_items/'+id).then(function(){
                    swal('删除成功','','success').then(
                            function(){
                                location.reload();
                            }
                    );
                })

            });
        });


        // 监听 全选/取消全选 单选框的变更事件
        $('#select-all').change(function(){
            // 获取单选框的选中状态
            // prop() 方法可以知道标签中是否包含某个属性，当单选框被勾选时，对应的标签就会新增一个 checked 的属性
            var checked = $(this).prop('checked');
            // 获取所有 name=select 并且不带有 disabled 属性的勾选框
            // 对于已经下架的商品我们不希望对应的勾选框会被选中，因此我们需要加上 :not([disabled]) 这个条件
            $('input[name=select][type=checkbox]:not([disabled])').each(function(){
                // 将其勾选状态设为与目标单选框一致
                $(this).prop('checked',checked);
            });
        });

        //监听提交订单按钮点击事件
        $('.btn-create-order').click(function(){
            var req = {

                'address_id': $('#order-form').find('select[name=address]').val(),
                'items'  : [],
                'extra'  : $('#order-form').find('textarea[name=extra]').val()
            };
            $('table tr[data-id]').each(function(){
                //获取当行单选框
                var checkbox = $(this).find('input[type=checkbox][name=select]');
                //如果当前单选框被禁用或者未被选择，返回
                if(checkbox.prop('disable')||!checkbox.prop('checked')){
                    return;
                }
                //获取当前输入的数量
                var amount = $(this).find('input[name=amount]').val();
                // 如果用户将数量设为 0 或者不是一个数字，则也跳过
                if(isNaN(amount)||amount==0){
                    return;
                }
                //吧sku_id和amount加入数组
                req.items.push({
                    'sku_id':$(this).data('id'),
                    'amount':amount
                });
            });
            axios.post('{{ route('orders.store') }}', req).then(function(response){
                //成功
                swal('提交订单成功','','success').then(
                        function(){
                            //location.reload();
                            location.href =  '/orders/' + response.data.id;
                        }
                );
            },function(error){
                //请求失败
                if(error.response.status === 401){
                    swal('请先登录','','error');
                }else if(error.response.status == 422){
                    var html = '<div>';
                    _.each(error.response.data.errors,function(errors){
                        _.each(errors,function(error){
                            html += error + '<br/>';
                        })
                    });
                    html = html+'</div>';
                    swal({content: $(html)[0], icon: 'error'})
                }else{
                    swal('系统错误','','error')
                }
            });
        });


    });
</script>
@stop