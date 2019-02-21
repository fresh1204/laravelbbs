<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Overtrue\EasySms\EasySms;//发送短信组件
use App\Http\Requests\Api\VerificationCodeRequest;

class VerificationCodesController extends Controller
{
    //发送短信验证码
    public function store(VerificationCodeRequest $request,EasySms $easysms)
    {
    	//$phone = $request->phone;
        //从缓存中读取 key 对应的存储手机号码和图片验证码文本的信息
    	$captchaData = \Cache::get($request->captcha_key);

        //如果为空说明图片验证码已失效
    	if(!$captchaData){
    		return $this->response->error('图片验证码已失效',422);
    	}

        //对比图片验证码文本内容和输入的验证码是否一致
    	if(!hash_equals($captchaData['code'],$request->captcha_code)){
    		// 验证错误就清除缓存
    		\Cache::forget($request->captcha_key);
    		
    		return $this->response->errorUnauthorized('验证码错误');
    	}

    	$phone = $captchaData['phone'];
    	
    	//如果不是线上正式环境
        if(!app()->environment('production')){
        	$code = '1234'; //默认测试短信验证码
        }else{
        	// 生成4位随机数，左侧补0
        	$code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
	        try{
                //发送短信到用户手机
	        	$result = $easysms->send(
                    $phone,
                    ['content'  =>  "【任运广test】您的验证码是{$code}。如非本人操作，请忽略本短信"]
                );
	        }catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
	            $message = $exception->getException('yunpian')->getMessage();
	            
                return $this->response->errorInternal($message ?: '短信发送异常');
	        }
        }

        $key = 'verificationCode_'.str_random(15); //生成一个随机key
        $expiredAt = now()->addMinutes(10);
        
        // 在缓存中存储这个 key 对应的手机号码以及验证码，10分钟过期
        \Cache::put($key,['phone' => $phone,'code' => $code],$expiredAt);

        //返回 key 以及 过期时间
    	return $this->response->array([
    		'key' => $key,
    		'expired_at' => $expiredAt->toDateTimeString(),
    	])->setStatusCode(201);
    }

    
}
