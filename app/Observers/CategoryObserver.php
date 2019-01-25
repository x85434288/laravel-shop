<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 11:10
 */

namespace App\Observers;

use App\Models\Category;

class CategoryObserver
{

    // 监听 Category 的创建事件，用于初始化 path 和 level 字段值
    public function creating(Category $category)
    {

        //如果创建的是一个根类目
        if(is_null($category->parent_id)){
            //将层级设置为0
            $category->level = 0;
            //将path设置为'-'
            $category->path = '-';
        }else{

            //将层级设置为父类目层级+1
            $category->level = $category->parent->level+1;
            //将path设置父类目path加上父栏目id
            $category->path = $category->parent->path.$category->parent_id.'-';

        }
    }

}