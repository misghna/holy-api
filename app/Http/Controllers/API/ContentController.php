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

    /**
     * Get all documents with associated files.
     * Merges the documents table with the files table where document.id = files.group_id.
     * @param  Request  $request
     * @return array
     */
    public function getAllDocumentsWithFiles(Request $request)
    {
        $lang = $request->input('lang');
        $start = $request->input('start');
        
        // Retrieve documents from the database
        $documents = Content::where("language", $lang)
            ->offset($start)
            ->limit(10)
            ->get();
        
        // Merge documents with files
        $mergedDocuments = [];
        foreach ($documents as $document) {
            $files = File::where('group_id', $document->id)->pluck('file_name')->toArray();
            $mergedDocument = [
                'id' => $document->id,
                'type' => $document->type,
                'description' => $document->description,
                'links' => $files,
                'created_by' => $document->created_by,
                'created_at' => $document->created_at,
            ];
            $mergedDocuments[] = $mergedDocument;
        }
        return $mergedDocuments;
    }
}
