@extends('layouts.app')
@section('title','商品列表')
@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">我的收藏</div>
                <div class="panel-body">
                    <div class="row products-list">
                        @if(count($products)>0)
                            @foreach($products as $product)
                                <div class="col-xs-3 product-item">
                                    <div class="product-content">
                                        <div class="top">
                                            <div class="img"><a href="{{ route('products.show', $product->id) }}"><img src="{{ $product->image_url }}" alt=""></a></div>
                                            <div class="price">¥{{ $product->price }}</div>
                                            <div class="title">{{ $product->title }}</div>
                                        </div>

                                        <div class="bottom">
                                            <div class="sold_count">销量：<span>{{ $product->sold_count }}</span>笔</div>
                                            <div class="review_count">评论：<span>{{ $product->review_count }}</span></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <h3>暂时没有商品上架...</h3>
                        @endif
                    </div>
                    <div class="paginate">{{ $products->render() }}</div>
                </div>
            </div>
        </div>
    </div>
@stop
