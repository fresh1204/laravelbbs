<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;  
use App\Http\Controllers\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Controller extends BaseController
{
    //这个 trait 可以帮助我们处理接口响应
    use Helpers;

    public function errorResponse($statusCode,$message=null,$code=0)
    {
    	throw new HttpException($statusCode,$message,null,[],$code);
    }
}
