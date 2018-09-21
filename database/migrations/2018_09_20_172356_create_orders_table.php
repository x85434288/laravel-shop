<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no')->unique();  //订单流水号
            $table->unsignedInteger('user_id'); //下单用户id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');  //与user表进行关联
            $table->text('address'); //下单用户地址
            $table->decimal('total_amount'); //订单总金额
            $table->text('remark')->nullable(); //订单备注
            $table->dateTime('paid_at')->nullable(); //支付时间
            $table->string('payment_method')->nullable();  //支付方式
            $table->string('payment_no')->bullable();  //支付平台订单号
            $table->string('refund_status')->default(\App\Models\Order::REFUND_STATUS_PENDING); //退款状态
            $table->string('refund_no')->bullable();  //退款单号
            $table->boolean('closed')->default(false);  //订单是否关闭 默认打开
            $table->boolean('reviewed')->default(false); //订单是否回复  默认未回复
            $table->string('ship_status')->default(\App\Models\Order::SHIP_STATUS_PENDING);  //物流状态
            $table->text('ship_data')->nullable();   //物流数据
            $table->text('extra');    //订单备注
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
