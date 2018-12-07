<?php

namespace App\Admin\Controllers;

use App\Http\Requests\Admin\HandleRefundRequest;
use App\Models\Order;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\InternalException;

class OrdersController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('订单管理');
            $content->description('列表');


            $content->body($this->grid());
        });
    }


    //展示详细订单消息
    public function show(Order $order)
    {
        return Admin::content(function(Content $content) use ($order){
            $content->header('订单详情');
            $content->body(view('admin.orders.show',['order'=>$order]));
        });
    }
    
    //发货
    public function ship(Order $order, Request $request)
    {

        //判断是否支付
        if(!$order->paid_at){
            throw new InvalidRequestException('订单未付款');
        }
        //判断是否发货
        if($order->ship_status !== Order::SHIP_STATUS_PENDING){
            throw new InvalidRequestException('该订单已发货');
        }
        $data = $this->validate($request,[
            'express_company' => ['required'],
            'express_no'      => ['required'],
        ],[], [
            'express_company' => '物流公司',
            'express_no'      => '物流单号',
        ]);
        // 我们在 Order 模型的 $casts 属性里指明了 ship_data 是一个数组
        // 因此这里可以直接把数组传过去
        $order->update(['ship_status'=>Order::SHIP_STATUS_DELIVERED,'ship_data'=>$data]);
        // 返回上一页
        return redirect()->back();
        
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Order::class, function (Grid $grid) {

            // 只展示已支付的订单，并且默认按支付时间倒序排序
            //$grid->model()->whereNotNull('paid_at')->orderBy('paid_at', 'desc');

            $grid->id('ID')->sortable();
            $grid->no('订单流水号');
            // 展示关联关系的字段时，使用 column 方法
            $grid->column('user.name','买家');
            $grid->total_amount('支付金额')->sortable();
            $grid->paid_at('支付时间')->sortable();
            $grid->ship_status('物流')->display(function($value){
                return Order::$shipStatusMap[$value];
            });
            $grid->refund_status('退款状态')->display(function($value){
                return Order::$refundStatusMap[$value];
            });
            // 禁用创建按钮，后台不需要创建订单
            $grid->disableCreateButton();
            $grid->actions(function ($actions) {
                // 禁用删除和编辑按钮
                $actions->disableDelete();
                $actions->disableEdit();
            });
            $grid->tools(function ($tools) {
                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });

            //$grid->created_at();
            //$grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Order::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }


    //处理用户退款
    public function handleRefund(Order $order, HandleRefundRequest $request)
    {
        //判断用户退款状态
        if($order->refund_status !== Order::REFUND_STATUS_APPLIED){
            throw new InvalidRequestException('订单状态不正确');
        }

        if($request->input('agree')){
            //同意退款
            //情况拒绝退款数据
            $extra = $order->extra?:[];
            unset($extra['refund_disagree_reason']);
            $order->update([
                'extra' => $extra
            ]);

            //处理退款逻辑
            $this->_refundOrder($order);

        }else{
            //不同意退款
            $extra = $order->extra?:[];
            $extra['refund_disagree_reason'] = $request->input('reason');
            // 将订单的退款状态改为未退款
            $order->update([
                'refund_status' => Order::REFUND_STATUS_PENDING,
                'extra'    => $extra
            ]);
        }
        return $order;
    }


    public function _refundOrder(Order $order)
    {
        //判断订单的支付方式
        switch($order->payment_method){
            //如果是微信支付
            case  'wechat' :
                //todo
                break;
            //如果是支付宝支付
            case 'alipay' :
                //生成退款订单号
                $refundNo = Order::getAvailableRefundNo();
                //调用支付宝支付实例的refund方法
                $ret = app('alipay')->refund([
                    'out_trade_no' => $order->no, //之前的订单流水号
                    'refund_amount' => $order->total_amount, //退款总额
                    'out_request_no' => $refundNo, //退款订单号
                ]);


                //根据支付宝文档，如果返回值有sub_code字段说明退款失败
                if($ret->sub_code){
                    //将退款失败的保存存入extra字段
                    $extra = $order->extra;
                    $extra['refund_failed_code'] = $ret->sub_code;
                    //将订单的退款状态标记为退款失败
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_FAILED,
                        'extra' => $extra
                    ]);
                }else{
                    //将订单的退款状态标记为退款成功并保存退款订单号
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_SUCCESS,
                    ]);
                }
                break;
            default:
                // 原则上不可能出现，这个只是为了代码健壮性
                throw new InternalException('未知订单支付方式：'.$order->payment_method);
                break;

        }
    }


}
