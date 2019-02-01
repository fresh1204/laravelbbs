<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Transformers\linkTransformer;
use App\Models\Link;

class LinksController extends Controller
{
    //获取推荐资源列表
    public function index(Link $link)
    {
    	$links = $link->getAllCached();

    	return $this->response->collection($links,new linkTransformer());
    }
}
