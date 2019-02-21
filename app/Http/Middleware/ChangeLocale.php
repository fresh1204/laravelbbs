<?php

namespace App\Http\Middleware;

use Closure;

class ChangeLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //获取请求头中的 accept-language，然后设置语言。
        $language = $request->header('accept-language');

        if($language){
            \App::setLocale($language);
        }

        return $next($request);
    }
}
