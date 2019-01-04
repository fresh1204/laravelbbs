<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Topic;
class CategoriesController extends Controller
{
    //分类下的话题列表
    public function show(Category $category,Request $request,Topic $topic)
    {
    	// 读取分类 ID 关联的话题，并按每 15 条分页
    	//$topics = Topic::where('category_id',$category->id)->paginate(15);

    	$topics = $topic->withOrder($request->order)->where('category_id',$category->id)->paginate(15);
    	//echo '<pre>';print_r($topics->toArray());
    	// 传参变量话题和分类到模板中
    	return view('topics.index',compact('topics','category'));
    }
}
