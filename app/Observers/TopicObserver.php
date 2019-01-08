<?php

namespace App\Observers;

use App\Models\Topic;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored
use App\Handlers\SlugTranslateHandler;
use App\Jobs\TranslateSlug;
class TopicObserver
{
    public function creating(Topic $topic)
    {
        //
    }

    public function updating(Topic $topic)
    {
        //
    }

    public function saving(Topic $topic)
    {
        // XSS 过滤
    	$topic->body = clean($topic->body,'user_topic_body');
    	
        // 生成话题摘录
    	$topic->excerpt = make_excerpt($topic->body);

        /*
        // 如 slug 字段无内容，即使用翻译器对 title 进行翻译
        if(! $topic->slug){

            //$topic->slug = app(SlugTranslateHandler::class)->translate($topic->title);
            //将 Slug 翻译的调用修改为队列执行的方式
            //推送任务到队列
            dispatch(new TranslateSlug($topic));
        }
        */
    }

    public function saved(Topic $topic)
    {
        // 如 slug 字段无内容，即使用翻译器对 title 进行翻译
        if(! $topic->slug){

            //$topic->slug = app(SlugTranslateHandler::class)->translate($topic->title);
            //将 Slug 翻译的调用修改为队列执行的方式
            //推送任务到队列
            dispatch(new TranslateSlug($topic));
        }
    }
}