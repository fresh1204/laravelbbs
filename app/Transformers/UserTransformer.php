<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

//数据转换层
class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['roles'];

    public function transform(User $user)
    {
        //返回给客户端的响应数据
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'introduction' => $user->introduction,
            'bound_phone' => $user->phone ? true : false,
            'bound_wechat' => ($user->weixin_unionid || $user->weixin_openid) ? true : false,
            'last_actived_at' => $user->last_actived_at->toDateTimeString(),
            'created_at' => $user->created_at->toDateTimeString(),
            'updated_at' => $user->updated_at->toDateTimeString(),
        ];
    }

    //包含用户角色
    public function includeRoles(User $user)
    {
        return $this->collection($user->roles,new RoleTransformer());
    }
    
}