<?php

namespace App\Admin\Controllers;

use App\Models\product;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ProductsController extends Controller
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

            $content->header('商品列表');
            //$content->description('description');



            $content->body($this->grid());
        });
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

            $content->header('编辑商品');
            //$content->description('description');

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

            $content->header('添加商品');
            //$content->description('description');

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
        return Admin::grid(product::class, function (Grid $grid) {

            //第一列为id,并将此设置为可排序列
            $grid->id('ID')->sortable();


            $grid->title('商品名称');

            //是否上架
            $grid->on_sale('是否上架')->dispaly(function($value){
                return $value ? '是' : '否';
            });

            //价格
            $grid->price('价格');

            //评分
            $grid->rating('评分');

            //销量
            $grid->sold_count('销量');

            //评论数
            $grid->review_count('评论数');

            $grid->created_at('添加时间');

            $grid->actions(function ($actions) {
                //$actions->disableView();
                $actions->disableDelete();
            });
            $grid->tools(function ($tools) {
                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
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
        return Admin::form(product::class, function (Form $form) {

            $form->text('title', '商品名称')->rules('required');
            $form->image('image', '封面图片')->rules('required|image');
            $form->editor('description', '商品描述')->rules('required');
            $form->radio('on_sale','上架')->options(['1'=>'是','0'=>'否'])->default(0);

            // 直接添加一对多的关联模型
            /*
             * $form->hasMany('skus', 'SKU 列表', ) 可以在表单中直接添加一对多的关联模型，商品和商品 SKU 的
             *关系就是一对多，第一个参数必须和主模型中定义此关联关系的方法同名，我们之前在 App\Models\Product
             * 类中定义了 skus() 方法来关联 SKU，因此这里我们需要填入 skus，第二个参数是对这个关联关系的描述，
             * 第三个参数是一个匿名函数，用来定义关联模型的字段。
             */
            $form->hasMany('skus', 'SKU 列表', function (Form\NestedForm $form) {
                $form->text('title', 'SKU 名称')->rules('required');
                $form->text('description', 'SKU 描述')->rules('required');
                $form->text('price', '单价')->rules('required|numeric|min:0.01');
                $form->text('stock', '剩余库存')->rules('required|integer|min:0');
            });


            //当模型被保存时触发
            //$form->saving() 用来定义一个事件回调，当模型即将保存时会触发这个回调。
            //我们需要在保存商品之前拿到所有 SKU 中最低的价格作为商品的价格，
            //然后通过 $form->model()->price 存入到商品模型中。
            $form->saving(function(Form $form){

                //collect() 函数是 Laravel 提供的一个辅助函数，
                //可以快速创建一个 Collection 对象。
                //在这里我们把用户提交上来的 SKU 数据放到 Collection 中，
                //利用 Collection 提供的 min() 方法求出所有 SKU 中最小的 price，
                //后面的 ?: 0 则是保证当 SKU 数据为空时 price 字段被赋值 0
                $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME,0)->min('price')?:0;
            });
        });
    }
}
