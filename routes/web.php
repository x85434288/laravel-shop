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

    });
    // 结束
});

//首页重定向到商品列表页
Route::redirect('/', '/products')->name('root');

//商品列表页
Route::get('/products','ProductsController@index')->name('products.index');

//商品详细页
Route::get('/products/{product}','ProductsController@show')->name('products.show');
