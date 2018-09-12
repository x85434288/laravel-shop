<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\EmailVarificationNotification;
use Illuminate\Auth\Events\Registered;

// implements ShouldQueue 让这个监听器异步执行
class RegisterListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */

    // 当事件被触发时，对应该事件的监听器的 handle() 方法就会被调用
    public function handle($event)
    {
        //获取到刚刚注册的用户
        $user = $event->user;
        // 调用 notify 发送通知
        $user->notify(new EmailVarificationNotification());

    }
}
