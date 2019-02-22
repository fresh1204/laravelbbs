<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Models\User;
use App\Http\Requests\Api\AuthorizationRequest;
use Auth;
use App\Http\Requests\Api\WeappAuthorizationRequest;

class AuthorizationsController extends Controller
{
    //第三方登录
    public function socialStore($type,SocialAuthorizationRequest $request)
    {
    	if(!in_array($type, ['weixin'])){
    		return $this->response->errorBadRequest();
    	}

    	$driver = \Socialite::driver($type);

    	try{
    		if($code = $request->code){//授权码
    			$response = $driver->getAccessTokenResponse($code);
    			$token = array_get($response,'access_token');
    		}else{
    			$token = $request->access_token;
    			if($type == 'weixin'){
    				$driver->setOpenId($request->openid);
    			}
    		}
            //获取第三方用户信息
    		$oauthUser = $driver->userFromToken($token);
    	}catch (\Exception $e) {
            return $this->response->errorUnauthorized('参数错误，未获取用户信息');
        }

        switch ($type) {
        	case 'weixin':
        		$unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;
        		if($unionid){
        			$user = User::where('weixin_unionid',$unionid)->first();
        		}else{
        			$user = User::where('weixin_openid',$oauthUser->getId())->first();
        		}

        		// 数据库中没有微信用户信息，默认创建一个用户
        		if(!$user){
        			$user = User::create([
                        'name' => $oauthUser->getNickname(),
                        'avatar' => $oauthUser->getAvatar(),
                        'weixin_openid' => $oauthUser->getId(),
                        'weixin_unionid' => $unionid,
        			]);
        		}
        		break;
        }


        //第三方登录获取 user 后，我们可以使用 fromUser 方法为某一个用户模型生成token
        $token = Auth::guard('api')->fromUser($user);

        return $this->respondWithToken($token)->setStatusCode(201);
        //return $this->response->array(['token' => $user->id]);
    }

    //用户账号密码登录
    public function store(AuthorizationRequest $request)
    {
    	$username = $request->username;

        //判断是手机登录还是邮箱登录
    	filter_var($username,FILTER_VALIDATE_EMAIL) ? 
    	$credentials['email'] = $username :
    	$credentials['phone'] = $username;

    	$credentials['password'] = $request->password;
        /*
            处理登录认证
            attempt 方法接收键值数组对作为第一个参数，数组中的值被用于从数据库表中查找用户，因此，在上面的例子中，用户将会通过email 的值获取，如果用户被找到，经哈希运算后存储在数据库表中的密码将会和传递过来的经哈希运算处理的密码值进行比较。如果两个经哈希运算的密码相匹配那么将会为这个用户开启一个认证Session。

            如果认证成功的话 attempt 方法将会返回 true。否则，返回 false。
        */
    	if(!$token = Auth::guard('api')->attempt($credentials)){ 
    		//return $this->response->errorUnauthorized('用户名或密码错误');
            return $this->response->errorUnauthorized(trans('auth.failed'));
    	}

    	/*
    	return $this->response->array([
    		'access_token' => $token,
    		'token_type' => 'Bearer',
    		'expires_in' => \Auth::guard('api')->factory()->getTTL()*60
    	])->setStatusCode(201);
    	*/

    	return $this->respondWithToken($token)->setStatusCode(201);
    }

    //封装带$token的响应返回
    protected function respondWithToken($token)
    {
        //var_dump(Auth::guard('api')->factory()->getTTL());exit;
    	return $this->response->array([
    		'access_token' => $token,
    		'token_type' => 'Bearer',
    		'expires_in' => Auth::guard('api')->factory()->getTTL()*60
    	]);
    }

    //刷新token
    public function update()
    {
    	$token = Auth::guard('api')->refresh();

    	return $this->respondWithToken($token);
    }

    //删除token
    public function destroy()
    {
    	Auth::guard('api')->logout();

    	return $this->response->noContent();
    }

    //微信小程序登录
    public function weappStore(WeappAuthorizationRequest $request)
    {
        $code = $request->code;

        // 根据 code 获取微信 openid 和 session_key
        $miniProgram = \EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($code);

        // 如果结果错误，说明 code 已过期或不正确，返回 401 错误
        if(isset($data['errcode'])){
            return $this->response->errorUnauthorized('code 错误');
        }

        
        $openid = $data['openid'];

        // 数据库中找 openid 对应的用户
        $user = User::where('weapp_openid',$openid)->first();
        $attributes['weixin_session_key'] = $data['session_key'];

        // 未找到对应用户，则需要提交用户名密码进行用户绑定(openid 绑定 用户)
        if(!$user){
            // 如果未提交用户名密码，403 错误提示
            if(!$username = $request->username){
                return $this->response->errorForbidden('用户不存在');
            }

            // 用户名可以是邮箱或电话
            filter_var($username, FILTER_VALIDATE_EMAIL) ?
                $credentials['email'] = $username :
                $credentials['phone'] = $username;

            $credentials['password'] = $request->password;

            // 验证用户名和密码是否正确
            if(!Auth::guard('api')->once($credentials)){
                return $this->response->errorUnauthorized('用户名或密码错误');
            }

            // 获取对应的用户
            $user = Auth::guard('api')->getUser();

            $attributes['weapp_openid'] = $openid;
        }

        // 更新用户数据
        $user->update($attributes);

        // 为对应用户创建 JWT
        $token = Auth::guard('api')->fromUser($user);

        return $this->respondWithToken($token)->setStatusCode(201);
    }
}
