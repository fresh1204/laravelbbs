<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Handlers\ImageUploadHandler;
class UsersController extends Controller
{
	//调用了 middleware 方法，该方法接收两个参数，第一个为中间件的名称，第二个为要进行过滤的动作
	public function __construct()
	{
		$this->middleware('auth',['except'=>['show']]);
	}

    //个人页面展示
    public function show(User $user)
    {
    	return view('users.show',compact('user'));
    }

    //编辑个人页面表单
    public function edit(User $user)
    {
    	//authorize 方法接收两个参数，第一个为授权策略的名称，第二个为进行授权验证的数据
    	$this->authorize('update',$user);

    	return view('users.edit',compact('user'));
    }

    //对个人页面表单进行数据处理
    public function update(UserRequest $request, ImageUploadHandler $uploader,User $user)
    {
    	//authorize 方法接收两个参数，第一个为授权策略的名称，第二个为进行授权验证的数据
    	$this->authorize('update',$user);

    	//接收表单提交的数据
    	$data = $request->all();

    	if($request->avatar){
    		$result = $uploader->save($request->avatar,'avatars',$user->id,416);
    		if($result){
    			$data['avatar'] = $result['path'];
    		}
    	}

    	$user->update($data);

    	return redirect()->route('users.show',$user->id)->with('success','个人资料更新成功！');
    }

}
