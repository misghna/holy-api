<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\API\File;
use Intervention\Image\ImageManagerStatic as Image;
use Log;

class FileController extends Controller
{
    
    public function getOne(Request $request)
    {
        $Id = $request->input('id');
        $isThumbnail = $request->input('thumbnail');
        $fileDir = storage_path() . "/uploaded/";
        $path = $isThumbnail ? $fileDir . "thumbnails/" : $fileDir ;
        $tenantId = $request->header('tenant_id',0); 

        if(is_null($Id) || is_null($tenantId)){
          //  return abort(400,"Missing Id or tenantId!"); 
            return response()->json(['message' => 'Missing Id or tenantId!'], 400);
        } 

        $file = File::where([["tenant_id", $tenantId],["id", $Id]])
            ->first();
        
        if ($file){
            $filePath = $path . $file->file_id;
            if(file_exists($filePath))
                return response()->download($path . $file->file_id, $file->file_name);
            else
                return abort(404,"file not found"); // return 404      
        }else
            return abort(404,"file(s) not found"); // return 404
    }

    public function getList(Request $request)
    {
        $fileType = strtolower($request->input('type', ''));
        $start = $request->input('start', 0);
        $limit = $request->input('limit', 10); 
        $tenantId = $request->header('tenant_id',0); 
        $content = File::where([["tenant_id", $tenantId],['file_type',$fileType]])
            ->offset($start)
            ->limit($limit)
            ->orderBy('created_by', 'DESC')
            ->get();
        return $content;
    }

    public function store(Request $request)
    {

        $uuid = (string) Str::uuid();
        $files = [];
        $tenantId = $request->header('tenant_id',0); 
        $images = array("png", "jpg", "jpeg", "svg","gif","webp");
        
        if ($request->file('files')) {

            if(is_array($request->file('files')))Log::info("true"); 
            $index = 1;
            foreach ($request->file('files') as $file) {
                
                $ext = $fileType = strtolower($file->extension());
                if(in_array ($fileType,$images)) $fileType ="image";

                $fileId = $uuid . '_' . $index . '.' . $ext;   
                
                //save thumbnail         
                $bgImage = storage_path() . '/app/public/uploaded/';  
                if (!file_exists($bgImage)) {
                    mkdir($bgImage, 0777, true);
                } 
                $bgThumbnailImage = storage_path() . '/app/public/uploaded/thumbnails/';  
                if (!file_exists($bgThumbnailImage)) {
                    mkdir($bgThumbnailImage, 0777, true);
                }        
                Log::info("path : " . storage_path()); 
                $img = Image::make($file->getRealPath());
                $img->resize(150, 150, function ($const) {
                    $const->aspectRatio();
                })->save(storage_path() . '/app/public/uploaded/thumbnails/' . $fileId);
                // // end of TN

                $fileName = $file->getClientOriginalName();
                $file->move(storage_path() . '/app/public/uploaded/', $fileId);

                $onefile['group_id'] = $uuid;
                $onefile['file_id'] = $fileId;
                $onefile['file_name'] = $fileName;
                $onefile['file_type'] = $fileType;
                $onefile['tenant_id'] = $tenantId;    
                $onefile['created_at'] = gmdate('Y-m-d H:i:s'); 
                $onefile['created_by'] = Auth::user()->id;                
                $files[] = $onefile;
                $index++;
            }
        }else if($request->input("url")){

            $fileId = $request->input("url");
            $index = 1;
            $fileType = 'url';
            $onefile['group_id'] = $uuid;
            $onefile['file_id'] = $fileId;
            $onefile['file_name'] = $fileId;
            $onefile['file_type'] = $fileType;
            $onefile['tenant_id'] = $tenantId;    
            $onefile['created_at'] = gmdate('Y-m-d H:i:s'); 
            $onefile['created_by'] = Auth::user()->id;                
            $files[] = $onefile;
        }
        foreach ($files as $key => $file) {
            File::create($file);
        }
        return $uuid;
    }
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $group_id = $request->input('group_id');
        $tenantId = $request->header('tenant_id',0); 
        $response = is_null($group_id) ? 
                    File::where([["tenant_id", $tenantId],["file_id", $id]])->delete() : 
                    File::where([["tenant_id", $tenantId],["group_id", $group_id]])->delete() ;
        if ($response)
            return "File(s) deleted successfully.";
        else return abort(404,"file(s) not found");; // return 400
    }
}
