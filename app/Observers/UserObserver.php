<?php

namespace App\Observers;

use App\Models\User;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored
//用户监控器类
class UserObserver
{
    public function creating(User $user)
    {
        //
    }

    public function updating(User $user)
    {
        //
    }

    //在用户数据即将入库之前，将为 avatar 字段设置一张默认的头像
    public function saving(User $user)
    {
    	// 这样写扩展性更高，只有空的时候才指定默认头像
    	if(empty($user->avatar)){
    		$user->avatar = 'https://iocaffcdn.phphub.org/uploads/images/201710/30/1/TrJS40Ey5k.png';
    	}
    }
}