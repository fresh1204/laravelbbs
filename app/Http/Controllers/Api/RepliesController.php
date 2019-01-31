<?php

namespace App\Http\Controllers\Api;

//use Illuminate\Http\Request;
use App\Models\Topic;
use App\Models\Reply;

use App\Http\Requests\Api\ReplyRequest;
use App\Transformers\ReplyTransformer;

class RepliesController extends Controller
{
    //对话题添加回复
    public function store(ReplyRequest $request,Topic $topic,Reply $reply)
    {
    	$reply->content = $request->content;
    	$reply->topic_id = $topic->id;
    	$reply->user_id = $this->user()->id;

    	$reply->save();

    	return $this->response->item($reply,new ReplyTransformer())->setStatusCode(201);
    }
}
