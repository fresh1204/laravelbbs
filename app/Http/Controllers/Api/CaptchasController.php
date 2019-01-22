<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\CaptchaRequest;
use Gregwar\Captcha\CaptchaBuilder;

class CaptchasController extends Controller
{
    public function store(CaptchaRequest $request,CaptchaBuilder $captchaBuilder)
    {
    	$key = 'captcha-' . str_random(15);
    	$phone = $request->phone;

    	//创建验证码图片
    	$captcha = $captchaBuilder->build();
    	//设置2分钟过期
    	$expiredAt = now()->addMinutes(5);

    	//使用 getPhrase 方法获取验证码文本
    	\Cache::put($key,['phone' => $phone,'code' => $captcha->getPhrase()],$expiredAt);

    	//inline 方法获取的 base64 图片验证码
    	$result = [
    		'captcha_key' => $key,
    		'captcha_image_content' => $captcha->inline(),
    		'expired_at' => $expiredAt->toDateTimeString(),
    	];

    	return $this->response->array($result)->setStatusCode(201);
    }
}
