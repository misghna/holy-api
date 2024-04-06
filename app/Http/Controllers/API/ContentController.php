<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\API\Content;
use App\Models\API\File;

class ContentController extends Controller
{
    public function all(Request $request)
    {
        $page = $request->input("page");
        $lang = $request->input('lang');
        $start = $request->input('start', 0); // Default to 0 if not provided
        $limit = $request->input('limit', 10); // Default to 10 if not provided

        $content = Content::where("language", $lang)
            ->where("content_category", $page)
            ->offset($start)
            ->limit($limit)
            ->get();

        return $content;
    }

    public function one(Request $request)
    {
        $page = $request->input("page");
        $id = $request->input('id');
        $lang = $request->input('lang');
        $start = $request->input('start', 0); // Default to 0 if not provided
        $limit = $request->input('limit', 10); // Default to 10 if not provided

        $content = Content::where("id", $id)
            ->where("language", $lang)
            ->where("content_category", $page)
            ->offset($start)
            ->limit($limit)
            ->get();

        return $content;
    }

    public function getAllDocumentsWithFiles(Request $request)
    {
        $lang = $request->input('lang');
        $start = $request->input('start', 0); // Default to 0 if not provided
        $limit = $request->input('limit', 10); // Default to 10 if not provided

        // Using table join to retrieve documents with associated files
        $documents = Content::leftJoin('files', 'content.id', '=', 'files.group_id')
            ->where("content.language", $lang)
            ->select('content.id', 'content.type', 'content.description', 'content.created_by', 'content.created_at', 'files.file_name')
            ->offset($start)
            ->limit($limit)
            ->get();

        return $documents;
    }
}
