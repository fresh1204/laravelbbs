<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use App\Models\Category;
use Auth;
class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	public function index(Request $request,Topic $topic)
	{
		//$topics = Topic::paginate(15);
		//预加载
		//方法 with() 提前加载了我们后面需要用到的关联属性 user 和 category，并做了缓存
		//$topics = Topic::with('user','category')->paginate(15);

		$topics = Topic::withOrder($request->order)->paginate(15);
		return view('topics.index', compact('topics'));
	}

    public function show(Topic $topic)
    {
        return view('topics.show', compact('topic'));
    }

    //发布帖子表单
	public function create(Topic $topic)
	{	
		//获取分类列表
		$categories = Category::all();
		return view('topics.create_and_edit', compact('topic','categories'));
	}

	//对发布帖子进行数据处理
	public function store(TopicRequest $request,Topic $topic)
	{
		$topic->fill($request->all());
		$topic->user_id = Auth::id();
		$topic->save();

		return redirect()->route('topics.show', $topic->id)->with('success', '帖子创建成功!');
	}

	public function edit(Topic $topic)
	{
        $this->authorize('update', $topic);
        //获取分类列表
        $categories = Category::all();
		return view('topics.create_and_edit', compact('topic','categories'));
	}

	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);
		$topic->update($request->all());

		return redirect()->route('topics.show', $topic->id)->with('success', '帖子更新成功.');
	}

	//删除帖子
	public function destroy(Topic $topic)
	{
		$this->authorize('destroy', $topic);
		$topic->delete();

		return redirect()->route('topics.index')->with('success', '删除帖子成功.');
	}
}