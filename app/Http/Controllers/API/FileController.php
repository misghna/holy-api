<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\API\File;

class FileController extends Controller
{
    
    public function index(Request $request)
    {
        $fileId = $request->input('file_id');
        $file = File::where("file_id", $fileId)
            ->first();
        if ($file)
            return response()->download(env("FILE_UPLOAD_PATH") . "/" . $file->file_name, $file->file_name);
        else
            return "file not found";
    }

    public function store(Request $request)
    {
        // $request->validate([
        //     'files' => 'required',
        //     'files.*' => 'required|mimes:pdf,xlx,csv|max:2048',

        // ]);
        // $uuid = time() . rand(1, 99);
        $uuid = $request->group_id;
        $files = [];
        if ($request->file('files')) {
            $index = 1;
            foreach ($request->file('files') as $key => $file) {
                $fileId = $uuid . "_" . $index;
                $fileExtention = $file->extension();
                $fileName = $fileId . '.' . $fileExtention;
                $file->move(env("FILE_UPLOAD_PATH"), $fileName);
                $onefile['group_id'] = $uuid;
                $onefile['file_id'] = $fileId;
                $onefile['file_name'] = $fileName;
                $files[] = $onefile;
                $index++;
            }
        }
        foreach ($files as $key => $file) {
            File::create($file);
        }
        return $uuid;
    }
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $response = File::where('file_id', $id)->orWhere('group_id', $id)->delete();
        if ($response)
            return "File deleted successfully.";
        else return "file not found";
    }
}
