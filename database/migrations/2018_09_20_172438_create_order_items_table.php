<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');  //与order表进行关联
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedInteger('sku_id');
            $table->foreign('sku_id')->references('id')->on('product_skus')->onDelete('cascade');
            $table->integer('amount');     //数量
            $table->decimal('price',10,2);   //单价
            $table->unsignedInteger('rating')->nullable();  //用户打分
            $table->text('review')->nullable();   //用户评价
            $table->timestamp('reviewed_at')->nullable();   //评价时间
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
