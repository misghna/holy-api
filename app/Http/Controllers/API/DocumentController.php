<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\Document;
use App\Models\API\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as fileup; 
use Illuminate\Support\Facades\DB;


class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::leftJoin('files', 'documents.id', '=', 'files.group_id')
        ->select('documents.id', 'documents.type', 'documents.description', DB::raw('GROUP_CONCAT(files.file_name) AS links'))
        ->groupBy('documents.id')
        ->get();;
        return $documents;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|string',
            'description' => 'required|string',
            'files' => 'required',
        ]);

        $id = $request->input('id');
        $type = $request->input('type');
        $description = $request->input('description');

        // Create a new document instance
        $document = new Document();
        $document->id = $id;
        $document->type = $type;
        $document->description = $description;

        // Save the new document to the database
        $document->save();
        $request->request->add(['group_id' => $document->id]);
        //store uploaded files
        if ($request->file('files')) {
            $fileController = new FileController();
            $fileController->store($request);
        }

        return response()->json($document, 200);
    }
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

        $document = Document::find($id);

        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        // Update the document
        $document->type = $type;
        $document->description = $description;
        $document->save();

        return response()->json($document, 200);
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');

        // Find the content document by its ID
        $document = Document::find($id);

        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }
        
        // Delete associated files
        $files = File::where('group_id', $id)->get();
        foreach ($files as $file) {
            fileup::delete(env("FILE_UPLOAD_PATH") ."/". $file->file_name);
            $file->delete();
        }

        // Delete the content document
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully'], 200);
    }
}
