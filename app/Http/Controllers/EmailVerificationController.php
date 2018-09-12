<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/12
 * Time: 15:58
 */

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\EmailVarificationNotification;
use Email;
use Auth;
use Cache;
use Exception;

class EmailVerificationController
{
    public function verify(Request $request)
    {
        $email = $request->input('email');
        $token = $request->input('token');
        if(!$email||!$token){

            throw new Exception('验证链接不正确');
        }

        if(!Cache::get('Email_verify_'.$email) == $token){

            throw new Exception('token无效或者过期');
        }

        if(!$user = User::where('email',$email)->first()){

            throw new Exception('此用户不存在');
        }

        Cache::forget('Email_verify_'.$email);
        $user->update(['email_verified'=>true]);

        return view('pages.success', ['msg' => '邮箱验证成功']);
    }


    public function send()
    {
        $user = Auth::user();
        //$user = $request->user();
        // 判断用户是否已经激活
        if ($user->email_verified) {
            throw new Exception('你已经验证过邮箱了');
        }
        // 调用 notify() 方法用来发送我们定义好的通知类
        $user->notify(new EmailVarificationNotification());

        return view('pages.success', ['msg' => '邮件发送成功']);
    }

}