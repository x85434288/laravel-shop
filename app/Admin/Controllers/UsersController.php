<?php

namespace App\Admin\Controllers;

use App\Models\user;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class UsersController extends Controller
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

            $content->header('用户列表');
            //$content->description('description');

            $content->body($this->grid());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        // 根据回调函数，在页面上用表格的形式展示用户记录
        return Admin::grid(user::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name('姓名');
            $grid->email('邮箱');
            $grid->email_verified('已验证邮箱')->display(function($value){

                return $value ? '是' : '否';
            });

            $grid->created_at('注册时间');
            //$grid->updated_at();
            // 不在页面显示 `新建` 按钮，因为我们不需要在后台新建用户
            $grid->disableCreateButton();

//            $grid->actions(function ($actions) {
//                // 不在每一行后面展示查看按钮
//                $actions->disableView();
//
//                // 不在每一行后面展示删除按钮
//                $actions->disableDelete();
//
//                // 不在每一行后面展示编辑按钮
//                $actions->disableEdit();
//            });

            $grid->disableActions();  //禁用操作列

            $grid->tools(function($tools){

                //禁用批量实处按钮
                $tools->batch(function($batch){
                    $batch->disableDelete();
                });

            });
        });
    }


}
