<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;
use App\Http\Requests\Api\UserRequest;

class UsersController extends Controller
{
    //用户手机注册
    public function store(UserRequest $request)
    {
        //从缓存中读取这个 key 对应的手机号码以及验证码
    	$verifyData = \Cache::get($request->verification_key);
       
        if(!$verifyData){
            $this->response->error('验证码已失效',422);
        }

        //对比验证码是否一致
    	if(!hash_equals($verifyData['code'],$request->verification_code)){
    		//返回401
    		return $this->response->errorUnauthorized('验证码错误');
    	}
    	
        //保存入库
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

    //小程序用户手机注册
    public function weappStore(UserRequest $request)
    {
        //从缓存中读取这个 key 对应的手机号码以及验证码
        $verifyData = \Cache::get($request->verification_key);
        if(! $verifyData){
            return $this->response->error('验证码已失效',422);
        }

        // 判断验证码是否相等，不相等返回 401 错误
        if(!hash_equals($request->verification_code,$verifyData['code'])){
            return $this->response->errorUnauthorized('验证码错误');
        }

        // 获取微信的 openid 和 session_key
        $miniProgram = \EasyWeChat::miniProgram();
        //获取提交的微信的授权码 Code
        $data = $miniProgram->auth->session($request->code);

        if(isset($data['errcode'])){
            return $this->response->errorUnauthorized('code 不正确');
        }

        // 如果微信 openid 对应的用户已存在，报错403
        $user = User::find('weapp_openid',$data['openid'])->first();
        if($user){
            return $this->response->errorForbidden('微信已绑定该用户，请直接登录')
        }

        //创建用户,同时绑定微信 openid
        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password),
            'weapp_openid' => $data['openid'],
            'weixin_session_key' => $data['session_key'],
        ]);

        // 清除验证码缓存
        \Cache::forget($request->verification_key);

        // meta 中返回 Token 信息
        return $this->response->item($user,new UserTransformer())
            ->setMeta([
                'access_token' => \Auth::guard('api')->fromUser($user),
                'token_type' => 'Bearer',
                'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
            ])->setStatusCode(201);

    }

    //获取用户信息
    public function me()
    {	
    	/*
    		Dingo\Api\Routing\Helpers 这个 trait 提供了user方法,方便我们获取当前登录的用户，
    		也就是token所对应的用户。$this->user() 等同于 \Auth::guard('api')->user()

    		我们返回的是一个单一资源，所以使用$this->response->item,第一个参数是模型实例，第二个参数是刚刚创建的transformer.
    	*/
    	return $this->response->item($this->user(), new UserTransformer());
    }

    //编辑个人资料
    public function update(UserRequest $request)
    {   
        //获取用户对象
        $user = $this->user();

        $attributes = $request->only(['name', 'email', 'introduction']);

        //修改头像时，我们先创建 avatar 类型的图片资源，然后提交 avatar_image_id 即可
        if($request->avatar_image_id){//判断是否提交头像
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
