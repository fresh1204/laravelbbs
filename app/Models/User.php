<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Auth;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

//class User extends Authenticatable implements MustVerifyEmailContract,JWTSubject
class User extends Authenticatable implements JWTSubject
{
    use MustVerifyEmailTrait;

    use Notifiable{
        notify as protected laravelNotify;
    }

    use HasRoles;

    use Traits\ActiveUserHelper;
    use Traits\LastActivedAtHelper;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','introduction','avatar','phone','weixin_openid', 'weixin_unionid'
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

    //当用户访问通知列表时，将所有通知状态设定为已读，并清空未读消息数。
    public function markAsRead()
    {
        $this->notification_count = 0;
        $this->save();
        $this->unreadNotifications->markAsRead();
    }

    //修改密码
    public function setPasswordAttribute($value)
    {
        // 如果值的长度等于 60，即认为是已经做过加密的情况
        if(strlen($value) != 60){
            // 不等于 60，做密码加密处理
            $value = bcrypt($value);
        }

        $this->attributes['password'] = $value;
    }

    //修改图像
    public function setAvatarAttribute($path)
    {
        // 如果不是 `http` 子串开头，那就是从后台上传的，需要补全 URL
        if(! starts_with($path,'http')){
            // 拼接完整的 URL
            $path = config('app.url')."/uploads/images/avatars/$path";
        }
        
        $this->attributes['avatar'] = $path;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
