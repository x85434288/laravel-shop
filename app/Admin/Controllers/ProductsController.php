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
        return Admin::grid(product::class, function (Grid $grid) {

            //第一列为id,并将此设置为可排序列
            $grid->id('ID')->sortable();

            //第二列显示title字段，由于title字段名和Grid对象的title方法冲突，所以用Grid的column()方法代替
            $grid->column('商品名称');

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

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
