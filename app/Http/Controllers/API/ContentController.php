<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\API\Content;

class ContentController extends Controller
{
    public function all(Request $request)
    {
        $page = $request->input("page");
        $lang = $request->input('lang');
        $start = $request->input('start');
        $content = Content::where("language", $lang)
            ->where("content_category", $page)
            ->offset($start)
            ->limit(10)
            ->get();
        return $content;
    }
    public function one(Request $request)
    {
        $page = $request->input("page");
        $id = $request->input('id');
        $lang = $request->input('lang');
        $start = $request->input('start');
        $content = Content::where("id", $id)
            ->where("language", $lang)
            ->where("content_category", $page)
            ->offset($start)
            ->limit(10)
            ->get();
        return $content;
    }
}
