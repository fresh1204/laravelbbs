<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;  
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    //这个 trait 可以帮助我们处理接口响应
    use Helpers;
}
