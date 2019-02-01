<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;
use App\Http\Requests\Api\UserRequest;

class UsersController extends Controller
{
    public function store(UserRequest $request)
    {
    	$verifyData = \Cache::get($request->verification_key);

    	if(!$verifyData){
    		$this->response->error('验证码已失效', 422);
    	}

    	if(!hash_equals($verifyData['code'],$request->verification_code)){
    		//返回401
    		return $this->response->errorUnauthorized('验证码错误');
    	}
    	//return $verifyData;
    	$user = User::create([
    		'name' => $request->name,
    		'phone' => $verifyData['phone'],
    		'password' => bcrypt($request->password),
    	]);

    	// 清除验证码缓存
    	\Cache::forget($request->verification_key);

    	//return $this->response->created();
    	return $this->response->item($user,new UserTransformer())->setStatusCode(201);
    }

    //获取用户信息
    public function me()
    {	
    	/*
    		Dingo\Api\Routing\Helpers 这个 trait 提供了user方法,方便我们获取当前登录的用户，
    		也就是token所对应的用户。$this->user() 等同于 \Auth::guard('api')->user()

    		我们返回的是一个单一资源，所以使用$this->response->item,第一个参数是模型实例，第二个参数是刚刚创建的
    		transformer.
    	*/
    	return $this->response->item($this->user(), new UserTransformer());
    }

    //编辑个人资料
    public function update(UserRequest $request)
    {   
        //获取用户对象
        $user = $this->user();

        $attributes = $request->only(['name', 'email', 'introduction']);

        if($request->avatar_image_id){
            $image = Image::find($request->avatar_image_id);
            $attributes['avatar'] = $image->path;
        }

        $user->update($attributes);

        return $this->response->item($user, new UserTransformer());
    }

    //活跃用户
    public function activedIndex(User $user)
    {
        return $this->response->collection($user->getActiveUsers(),new UserTransformer());
    }
}
