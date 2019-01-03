<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Handlers\ImageUploadHandler;
class UsersController extends Controller
{
    //个人页面展示
    public function show(User $user)
    {
    	return view('users.show',compact('user'));
    }

    //编辑个人页面表单
    public function edit(User $user)
    {
    	return view('users.edit',compact('user'));
    }

    //对个人页面表单进行数据处理
    public function update(UserRequest $request, ImageUploadHandler $uploader,User $user)
    {
    	//接收表单提交的数据
    	$data = $request->all();

    	if($request->avatar){
    		$result = $uploader->save($request->avatar,'avatars',$user->id);
    		if($result){
    			$data['avatar'] = $result['path'];
    		}
    	}

    	$user->update($data);

    	return redirect()->route('users.show',$user->id)->with('success','个人资料更新成功！');
    }

}
