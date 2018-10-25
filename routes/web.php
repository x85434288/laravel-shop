<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});



Auth::routes();

Route::group(['middleware' => 'auth'], function() {
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');
    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');
    Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');

    // 开始
    Route::group(['middleware' => 'email_verified'], function() {

//        Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
//        Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
//        Route::post('user_addresses/store', 'UserAddressesController@store')->name('user_addresses.store');
//        Route::get('user_addresses/{user_address}/edit', 'UserAddressesController@edit')->name('user_addresses.edit');
//        Route::patch('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
//        Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');

        //用户详细地址路由
        Route::resource('user_addresses','UserAddressesController')->except('show');

        //用户添加收藏
        Route::post('/products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
        Route::delete('/products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
        Route::get('/products/favorites', 'ProductsController@favorites')->name('products.favorites');

        //用户添加购物车
        Route::post('/cart_items','CartItemsController@store')->name('cart_items.store');
        //购物车展示页面
        Route::get('/cart_items','CartItemsController@index')->name('cart_items.index');

        //移除购物车中的商品
        Route::delete('/cart_items/{sku}','CartItemsController@remove')->name('cart_items.remove');

        Route::post('/orders','OrdersController@store')->name('orders.store');
        Route::get('/orders','OrdersController@index')->name('orders.index');
        Route::get('/orders/{order}','OrdersController@show')->name('orders.show');

        //显示商品评价
        Route::get('/orders/{order}/review','OrdersController@review')->name('orders.review.show');
        //提交商品评价
        Route::post('/orders/{order}/review','OrdersController@sendReview')->name('orders.review.store');


        //用户确认收货
        Route::post('/orders/{order}/received','OrdersController@received')->name('orders.received');

        //支付宝支付
        Route::get('/payment/{order}/alipay','PaymentController@payByalipay')->name('payment.alipay');

        //支付宝前端回调
        Route::get('/payment/alipay/return','PaymentController@alipayReturn')->name('payment.alipay.return');

        //微信支付
        Route::get('payment/{order}/wechat', 'PaymentController@payByWechat')->name('payment.wechat');


    });
    // 结束
});

//首页重定向到商品列表页
Route::redirect('/', '/products')->name('root');

//商品列表页
Route::get('/products','ProductsController@index')->name('products.index');

//商品详细页
Route::get('/products/{product}','ProductsController@show')->name('products.show');

//支付宝服务器端回调  此url要避免csrf验证  修改app/middleware/VerifyCsrfToken.php
Route::post('/payment/alipay/notify','PaymentController@alipayNotify')->name('payment.alipay.notify');
//微信支付服务器端回调
Route::post('payment/wechat/notify', 'PaymentController@wechatNotify')->name('payment.wechat.notify');

//Route::get('alipay',function(){
//    return app('alipay')->web([
//        'out_trade_no' => time(),
//        'total_amount' => '1',
//        'subject' => '测试',
//    ]);
//});

//Route::get('/payment/{order}/test', 'PaymentController@test')->name('payment.test');