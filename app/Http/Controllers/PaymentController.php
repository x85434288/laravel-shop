<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Exceptions\InvalidRequestException;

class PaymentController extends Controller
{
    //
    public function payByAlipay(Order $order, Request $request)
    {
        //判断是否为本人支付
        $this->authorize('own', $order);
        if($order->paid_at || $order->closed){
            throw new InvalidRequestException('数据错误');
        }

        return app('alipay')->web([
            'out_trade_no' => $order->no,
            'total_amount' => $order->total_amount,
            'subject' => '测试:'.$order->no,
        ]);

    }


    //前端回调
    public function alipayReturn()
    {
        try{
            //检验回调的参数
            app('alipay')->verify();
        }catch(\Exception $e){
            return view('pages.error',['msg'=>'数据错误']);
        }
        return view('pages.success',['msg'=>'支付成功']);
    }

    //服务器端回调
    public function alipayNotify()
    {
        //检验回调参数
        $data = app('alipay')->verify();
        $order = Order::where('no',$data->out_trade_no)->first();
        // 正常来说不太可能出现支付了一笔不存在的订单，这个判断只是加强系统健壮性。
        if(!$order){
            return 'fail';
        }
        // 如果这笔订单的状态已经是已支付
        if($order->paid_at){
            // 返回数据给支付宝
            return app('alipay')->success();
        }

        //修改订单支付状态
        $order->update([

            'paid_at' => Carbon::now(),             //支付时间
            'payment_method' => 'alipay',           //支付方式
            'payment_no'     => $data->trade_no,    //支付宝订单号

        ]);


        // 返回数据给支付宝
        return app('alipay')->success();
    }
}
