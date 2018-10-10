<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Exceptions\InvalidRequestException;
use Endroid\QrCode\QrCode;
use App\Events\OrderPaid;

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


    //微信支付
    public function payByWechat(Order $order, Request $request) {
        // 校验权限
        $this->authorize('own', $order);
        // 校验订单状态
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }
        // scan 方法为拉起微信扫码支付
        $wechatOrder = app('wechat_pay')->scan([
            'out_trade_no' => $order->no,  // 商户订单流水号，与支付宝 out_trade_no 一样
            'total_fee' => $order->total_amount * 100, // 与支付宝不同，微信支付的金额单位是分。
            'body'      => '支付 Laravel Shop 的订单：'.$order->no, // 订单描述
        ]);
        // 把要转换的字符串作为 QrCode 的构造函数参数  生成二维码
        $qrCode = new QrCode($wechatOrder->code_url);
        return response($qrCode->writeString(), 200, ['Content-Type' => $qrCode->getContentType()]);
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

        //触发支付完成后事件
        $this->afterPaid($order);
        // 返回数据给支付宝
        return app('alipay')->success();
    }


    //微信支付服务器端回调
    public function wechatNotify()
    {
        // 校验回调参数是否正确
        $data  = app('wechat_pay')->verify();
        // 找到对应的订单
        $order = Order::where('no', $data->out_trade_no)->first();
        // 订单不存在则告知微信支付
        if (!$order) {
            return 'fail';
        }
        // 订单已支付
        if ($order->paid_at) {
            // 告知微信支付此订单已处理
            return app('wechat_pay')->success();
        }

        // 将订单标记为已支付
        $order->update([
            'paid_at'        => Carbon::now(),
            'payment_method' => 'wechat',
            'payment_no'     => $data->transaction_id,
        ]);

        //触发支付完成后事件
        $this->afterPaid($order);
        return app('wechat_pay')->success();
    }


    protected function afterPaid(Order $order){

        event(new OrderPaid($order));
    }

    //测试
//    public function test(Order $order)
//    {
//        $this->afterPaid($order);
//    }
}
