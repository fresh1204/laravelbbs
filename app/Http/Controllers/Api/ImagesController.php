<?php

namespace App\Http\Controllers\Api;

use App\Models\Image;
use Illuminate\Http\Request;
use App\Handlers\ImageUploadHandler;
use App\Transformers\ImageTransformer;
use App\Http\Requests\Api\ImageRequest;

class ImagesController extends Controller
{
    //创建生成图片资源
    public function store(ImageRequest $request,ImageUploadHandler $uploader,Image $image)
    {
    	//获取头像对应的用户
    	$user = $this->user();
    	//图片大小尺寸
    	$size = ($request->type == 'avatar') ? 362 : 1024;
    	//上传图片
    	$result = $uploader->save($request->image, str_plural($request->type), $user->id, $size);

    	//赋值
    	$image->path = $result['path'];
    	$image->type = $request->type;
    	$image->user_id = $user->id;
        
    	//保存
    	$image->save();

    	return $this->response->item($image,new ImageTransformer())->setStatusCode(201);
    }
}
