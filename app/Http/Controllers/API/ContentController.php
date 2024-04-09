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

    //GET Call:
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

    //UPDATE Call:
    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|string',
            'type' => 'required|string',
            'description' => 'required|string',
        ]);

        $id = $request->input('id');
        $type = $request->input('type');
        $description = $request->input('description');

        $content = Content::find($id);

        if (!$content) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        // Update the document
        $content->type = $type;
        $content->description = $description;
        $content->save();

        return response()->json($content, 200);
    }

    //POST call:
    public function insert(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|string',
            'type' => 'required|string',
            'description' => 'required|string',
        ]);

        $id = $request->input('id');
        $type = $request->input('type');
        $description = $request->input('description');

        // Create a new content instance
        $content = new Content();
        $content->id = $id;
        $content->type = $type;
        $content->description = $description;

        // Save the new content to the database
        $content->save();

        return response()->json($content, 200);
    }

    // DELETE call:
    public function delete(Request $request)
    {
        $id = $request->input('id');

        // Find the content document by its ID
        $content = Content::find($id);

        if (!$content) {
            return response()->json(['error' => 'Document not found'], 404);
        }
        
        // Delete associated files
        File::where('group_id', $id)->delete();

        // Delete the content document
        $content->delete();

        return response()->json(['message' => 'Document deleted successfully'], 200);
    }

}
