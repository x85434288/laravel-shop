<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InternalException extends Exception
{
    //
    protected $userMsg;

    public function __construct($msg, $userMsg = '系统内部错误', $code)
    {

        parent::__construct($msg, $code);
        $this->userMsg = $userMsg;
    }


    public function render(Request $request)
    {

        if($request->expectsJson()){

            return response()->json(['msg'=>$this->userMsg], $this->code);
        }

        return view('pages.error',['msg' => $this->userMsg]);


    }
}
