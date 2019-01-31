<?php

namespace App\Http\Controllers\Api;

//use Illuminate\Http\Request;
use App\Models\Topic;
use App\Models\Reply;
use App\Models\User;
use App\Http\Requests\Api\ReplyRequest;
use App\Transformers\ReplyTransformer;

class RepliesController extends Controller
{
	//某个话题的回复列表
	public function index(Topic $topic)
	{
		//$replies = $topic->replies()->paginate(20);
		$replies = $topic->replies()->orderBy('created_at','desc')->paginate(20);

		return $this->response->paginator($replies,new ReplyTransformer());
	}

	//某个用户的回复列表
	public function userIndex(User $user)
	{
		//$replies = $user->replies()->paginate(20);
		$replies = $user->replies()->orderBy('created_at','desc')->paginate(20);

		return $this->response->paginator($replies,new ReplyTransformer());
	}

    //对话题添加回复
    public function store(ReplyRequest $request,Topic $topic,Reply $reply)
    {
    	$reply->content = $request->content;
    	$reply->topic_id = $topic->id;
    	$reply->user_id = $this->user()->id;

    	$reply->save();

    	return $this->response->item($reply,new ReplyTransformer())->setStatusCode(201);
    }

    //对话题某条回复进行删除
    public function destroy(Topic $topic,Reply $reply)
    {
    	//判断回复话题和话题ID是否一致
    	if($reply->topic_id != $topic->id){
    		return $this->response->errorBadRequest();
    	}

    	$this->authorize('destroy',$reply);
    	$reply->delete();

    	return $this->response->noContent();
    }

}
