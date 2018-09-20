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


    });
</script>
@stop