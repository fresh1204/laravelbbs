<?php

namespace App\Observers;

use App\Models\Reply;
use App\Notifications\TopicReplied;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class ReplyObserver
{
    public function creating(Reply $reply)
    {
        //对 content 字段进行净化处理(防止XSS注入)
        $reply->content = clean($reply->content,'user_topic_body');
    }

    public function updating(Reply $reply)
    {
        //
    }

    //成功发表回复后评论数加1
    public function created(Reply $reply)
    {
    	//$reply->topic->increment('reply_count',1);

    	/*
    	$reply->topic->reply_count = $reply->topic->replies->count();
    	$reply->topic->save();
    	*/

    	//优化上面的
    	$reply->topic->updateReplyCount();

    	// 通知话题作者有新的评论
    	$reply->topic->user->notify(new TopicReplied($reply));
    }

    //成功删除评论后回复数减1
    public function deleted(Reply $reply)
    {
    	//更新评论数目
    	/*
    	$reply->topic->reply_count = $reply->topic->replies->count();
    	$reply->topic->save();
    	*/
    	
    	$reply->topic->updateReplyCount();
    }

}