@extends('layouts.app')
@section('title','查看评价')
@section('content')
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    商品评价
                    <a class="pull-right" href="{{ route('orders.index') }}">返回订单列表</a>
                </div>
                <div class="panel-body">
                    <form action="{{ route('orders.review.store',$orders->id) }}" method="post">
                        {{ csrf_field() }}
                        <table class="table">
                            <thead>
                            <tr>
                                <th>商品信息</th>
                                <th>打分</th>
                                <th>评价</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($orders->items as $index => $item)
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
                                            <input name="reviews[{{ $index }}][id]" type="hidden" value="{{ $item->id }}">
                                        </td>
                                        <td class="vertical-middle">
                                            @if($orders->reviewed)
                                                <span class="rating-star-yes">{{ str_repeat('★', $item->rating) }}</span>
                                                <span class="rating-star-no">{{ str_repeat('★', 5-$item->rating) }}</span>
                                                @else
                                                <ul class="rate-area">
                                                    <input type="radio" id="5-star-{{$index}}" name="reviews[{{$index}}][rating]" value="5" checked /><label for="5-star-{{$index}}"></label>
                                                    <input type="radio" id="4-star-{{$index}}" name="reviews[{{$index}}][rating]" value="4" /><label for="4-star-{{$index}}"></label>
                                                    <input type="radio" id="3-star-{{$index}}" name="reviews[{{$index}}][rating]" value="3" /><label for="3-star-{{$index}}"></label>
                                                    <input type="radio" id="2-star-{{$index}}" name="reviews[{{$index}}][rating]" value="2" /><label for="2-star-{{$index}}"></label>
                                                    <input type="radio" id="1-star-{{$index}}" name="reviews[{{$index}}][rating]" value="1" /><label for="1-star-{{$index}}"></label>
                                                </ul>
                                            @endif
                                        </td>
                                        <td>
                                            @if($orders->reviewed)
                                                {{ $item->review }}
                                                @else
                                                <textarea class="form-control" name="reviews[{{$index}}][review]"></textarea>
                                                @if($errors->has('reviews'.$index.'.review'))
                                                    @foreach($errors->get('reviews'.$index.'.review') as $msg)
                                                        <span class="help-block">$msg</span>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="3">
                                    @if($orders->reviewed)
                                        <a class="btn btn-primary" href="{{ route('orders.show',$orders->id) }}">查看订单</a>
                                        @else
                                        <button type="submit" class="btn btn-primary  center-block">提交</button>
                                    @endif
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection