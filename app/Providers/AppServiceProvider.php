<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
	{
		\App\Models\User::observe(\App\Observers\UserObserver::class);
		\App\Models\Reply::observe(\App\Observers\ReplyObserver::class);
		\App\Models\Topic::observe(\App\Observers\TopicObserver::class);
        \App\Models\Link::observe(\App\Observers\LinkObserver::class);

        //对Carbon进行本地化设置
        \Carbon\Carbon::setLocale('zh');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        if (app()->isLocal()) {
            $this->app->register(\VIACreative\SudoSu\ServiceProvider::class);
        }

        //处理404 not found 异常
        \API::error(function (\Illuminate\Database\Eloquent\ModelNotFoundException $exception ){
            abort(404);
        });

        //处理403权限异常
        \API::error(function (\Illuminate\Auth\Access\AuthorizationException $exception){
            abort(403,$exception->getMessage());
        });
    }
}
