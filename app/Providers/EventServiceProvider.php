<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\RegisterListener;
use Illuminate\Auth\Events\Registered;
use App\Listeners\UpdateProductSoldCount;
use App\Events\OrderPaid;
use App\Listeners\SendOrderPaidEmail;
use App\Events\OrderReviewed;
use App\Listeners\UpdateProductReview;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener'
        ],
        //用户注册成功触发
        Registered::class => [
            RegisterListener::class,
        ],
        //订单支持成功触发
        OrderPaid::class => [
            UpdateProductSoldCount::class,   //增加商品销量
            SendOrderPaidEmail::class,     //发送支付成功邮件
        ],
        //订单评论成功是触发
        OrderReviewed::class => [
            UpdateProductReview::class,   //修改商品评论数和评分
        ]



    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
