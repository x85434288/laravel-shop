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

Route::get('/','PagesController@root')->name('root');

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

        Route::resource('user_addresses','UserAddressesController')->except('show');

    });
    // 结束
});