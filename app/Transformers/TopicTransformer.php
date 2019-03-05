<?php

namespace App\Transformers;

use App\Models\Topic;
use League\Fractal\TransformerAbstract;

class TopicTransformer extends TransformerAbstract
{
	//设置可以嵌套的额外资源
	protected $availableIncludes = ['user','category','topReplies'];

	public function transform(Topic $topic)
	{
		return [
			'id' => $topic->id,
			'title' => $topic->title,
			'body' => $topic->body,
			'user_id' => $topic->user_id,
			'category_id' => $topic->category_id,
			'reply_count' => $topic->reply_count,
			'view_count' => $topic->view_count,
			'last_reply_user_id' => $topic->last_reply_user_id,
			'excerpt' => $topic->excerpt,
			'slug' => $topic->slug,
			'created_at' => $topic->created_at->toDateTimeString(),
			'updated_at' => $topic->updated_at->toDateTimeString(),
		];
	}

	//获取，转换额外的资源
	public function includeUser(Topic $topic)
	{	
		//查询到用户数据 $topic->user ，通过 UserTransformer 格式化用户数据
		return $this->item($topic->user,new UserTransformer());
	}

	public function includeCategory(Topic $topic)
	{
		//查询到话题分类数据 $topic->category ，通过 UserTransformer 格式化话题分类数据
		return $this->item($topic->category,new CategoryTransformer());
	}

	// 话题的 5 条回复数据
	public function includeTopReplies(Topic $topic)
	{
		// 查询到话题回复5条数据 $topic->topReplies
		return $this->collection($topic->topReplies,new ReplyTransformer());
	}
}