<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\API\Content;

class ContentController extends Controller
{
    public function getContent(Request $request)
    {
        $id = $request->input('id');
        $lang = $request->input('lang');
        $start = $request->input('start');
        $content = Content::where("language", $lang)
            ->where(function (Builder $query) use ($id) {
                if ($id != null) {
                    $query->where("id", $id);
                }
            })
            ->offset($start)
            ->limit(10)
            ->get();
        return $content;
    }
}
