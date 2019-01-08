<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Auth;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use MustVerifyEmailTrait;

    use Notifiable{
        notify as protected laravelNotify;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','introduction','avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //一个用户拥有多个主题
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    //一个用户可以拥有多条回复评论
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function notify($instance)
    {
         // 如果要通知的人是当前用户，就不必通知了！
        if($this->id == Auth::id()){
            return;
        }

        // 只有数据库类型通知才需提醒，直接发送 Email 或者其他的都 Pass
        if(method_exists($instance, 'toDatabase')){

            $this->increment('notification_count');
        }

        $this->laravelNotify($instance);
    }
}
