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
        $lang = $request->input('lang');
        $start = $request->input('start');
        $content = Content::where("language", $lang)
            ->offset($start)
            ->limit(10)
            ->get();
        return $content;
    }
    public function one(Request $request)
    {
        $id = $request->input('id');
        $lang = $request->input('lang');
        $start = $request->input('start');
        $content = Content::where("id", $id)
            ->where("language", $lang)
            ->offset($start)
            ->limit(10)
            ->get();
        return $content;
    }
}
