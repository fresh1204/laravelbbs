<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Transformers\NotificationTransformer;

class NotificationsController extends Controller
{
    //消息通知列表
    public function index()
    {
    	//用户模型的 notifications 方法是 Laravel 的消息通知系统 为我们提供的方法，按通知创建时间倒叙排序
    	$notifications = $this->user->notifications()->paginate(20);

    	return $this->response->paginator($notifications,new NotificationTransformer());
    }
}
