<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InvalidRequestException extends Exception
{
    //
    public function __construct($msg='', $code=400)
    {
        parent::__construct($msg, $code);
    }


    public function render(Request $request)
    {

        //判断是否为json请求
        if($request->expectsJson()){
            //返回json数组
            return response()->json(['msg'=>$this->message],$this->code);
        }
        //不是返回字符串渲染到错误模板
        return view('pages.error',['msg' => $this->message]);

    }
}
