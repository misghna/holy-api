<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\API\Content;
use App\Models\API\File;
use App\Models\FileMapper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class ContentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $tenantId = $request->header('tenant_id');
        $valRules = [
            'type' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'background_image' => 'required|array',
            'content_text' => 'required|string',
            'media_link' => 'required|string',
            'content_category' => 'required|string',
            'lang' => 'required|string',
            'is_original' => 'required|boolean',
            'auto_translate' => 'required|boolean',
            'is_draft' => 'required|boolean',
            'tenant_id' => 'required|integer|exists:tenants,id'
        ];
        $data = $request->all();
        $data['tenant_id'] = $tenantId;
        $data['created_at'] = gmdate('Y-m-d H:i:s');
        $data['updated_at'] = gmdate('Y-m-d H:i:s');
        $validator = Validator::make($data, $valRules);

        if (!$validator->passes()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()->all()
            ], 422);
        }

        // Extract background_image from data
        $backgroundImg = $data['background_image'];
        unset($data['background_image']);

        $data['updated_by'] = Auth::user()->id;
        $content = Content::create($data);

        

        $backgroundImgData = array_map(function ($fileId) use ($content, $data) {
            return [
                'ref_id' => $content->id,
                'ref_type' => 'content',
                'file_id' => $fileId,
                'updated_by' => $data['updated_by'],
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at']
            ];
        }, $backgroundImg);
    
        // Bulk insert background_image data into FileMapper
        FileMapper::insert($backgroundImgData);
    
        $contentArray = $content->toArray();
        $contentArray['background_image'] = $backgroundImg;

        return response()->json([
            'content' => $contentArray,
            'message' => 'Success'
        ], 200);
    }

    public function update(Request $request): JsonResponse
    {
        
        $tenantId = $request->header('tenant_id');
        $valRules = [
            'id' => 'required|integer',
            'type' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'background_image' => 'required|array',
            'content_text' => 'required|string',
            'media_link' => 'required|string',
            'content_category' => 'required|string',
            'lang' => 'required|string',
            'is_original' => 'required|boolean',
            'auto_translate' => 'required|boolean',
            'is_draft' => 'required|boolean',
            'tenant_id' => 'required|integer|exists:tenants,id',
        ];
        $data = $request->all();
        $data['tenant_id'] = $tenantId;
        $data['created_at'] = gmdate('Y-m-d H:i:s');
        $data['updated_at'] = gmdate('Y-m-d H:i:s');
        $validator = Validator::make($data, $valRules);

        if (!$validator->passes()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $data['updated_by'] = Auth::user()->id;

        // Extract background_image from data
        $backgroundImg = $data['background_image'];
        unset($data['background_image']);

        $id = $request->input('id');
        $content = Content::findOrFail($id);
        $content->fill($data);
        $content->save();

        // Fetch existing background_image entries
        $existingBgImages = FileMapper::where('ref_id', $content->id)
        ->where('ref_type', 'content')
        ->pluck('file_id')
        ->toArray();

        // Determine entries to add and remove
        $imagesToAdd = array_diff($backgroundImg, $existingBgImages);
        $imagesToRemove = array_diff($existingBgImages, $backgroundImg);
        
        // Delete obsolete background_image entries if there are any
        if (count($imagesToRemove) > 0) {
            FileMapper::where('ref_id', $content->id)
                ->where('ref_type', 'content')
                ->whereIn('file_id', $imagesToRemove)
                ->delete();
        }
        // Add new background_image entries if there are any
        if (count($imagesToAdd) > 0) {
            foreach ($imagesToAdd as $fileId) {
                FileMapper::create([
                    'ref_id' => $content->id,
                    'ref_type' => 'content',
                    'file_id' => $fileId,
                    'updated_by' => $data['updated_by'],
                ]);
            }
        }

    
        $contentArray = $content->toArray();
        $contentArray['background_image'] = $backgroundImg;


        return response()->json([
            'contentConfig' => $contentArray,
            'message' => 'Success, Content Config updated successfully'
        ], 200);

    }

    public function all(Request $request)
    {
        $tenantId = $request->header('tenant_id');
        // Validate the tenant_id
        $validator = Validator::make(['tenant_id' => $tenantId], [
            'tenant_id' => 'required|integer|exists:tenants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $request->validate([
            'content_category' => 'required|string',
            'lang' => 'required|string',
        ]);
        
        $content_category = $request->input("content_category");
        $lang = $request->input('lang','english');
        $start = $request->input('start', 0); // Default to 0 if not provided
        $limit = $request->input('limit', 10); // Default to 10 if not provided

        $content = Content::with('media_link')->where("lang", $lang)
            ->select('content.id', 'content.lang', 'content.type','content.title','content.description','content.content_text','content.content_category',DB::raw('UNIX_TIMESTAMP(content.created_at)*1000 AS release_date_time'),
            DB::raw('concat("[",COALESCE(GROUP_CONCAT(file_mapper.file_id), ""),"]") as background_image'))
            ->leftJoin('file_mapper', function($join) {
                $join->on('file_mapper.ref_id', '=', 'content.id');
              })
            ->where([["content_category", $content_category],['lang',$lang],['tenant_id',$tenantId]])
            ->offset($start)
            ->limit($limit)
            ->groupBy("content.id")
            ->get();


        return $content;
    }

    public function one(Request $request)
    {
        $tenantId = $request->header('tenant_id');
        $headerValidator = Validator::make(['tenant_id' => $tenantId], [
            'tenant_id' => 'required|integer|exists:tenants,id',
        ]);

        if ($headerValidator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $headerValidator->errors()
            ], 422);
        }

        $id = $request->input('id');
        
        $request->validate([
            'id' => 'required|integer',
            'content_category' => 'required|string',
            'lang' => 'required|string',
        ]);
        $content_category = $request->input("content_category");
        $id = $request->input('id');
        $lang = $request->input('lang');
        $tenantId = $request->header('tenant_id',0); 

        $content = Content::with('media_link')
            ->select('content.id', 'content.lang', 'content.type','content.title','content.media_link','content.description','content.content_category',DB::raw('UNIX_TIMESTAMP(content.created_at)*1000 AS release_date_time'),DB::raw('concat("[",COALESCE(GROUP_CONCAT(file_mapper.file_id), ""),"]") as background_image'))
            ->leftJoin('file_mapper', function($join) {
                $join->on('file_mapper.ref_id', '=', 'content.id');
              })
              ->where([["content.id", $id],["content_category", $content_category],['lang',$lang],['tenant_id',$tenantId]])
            ->groupBy("content.id")
            ->first();

        return $content;
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $id = $request->input('id');
        $response = Content::where('id', $id)->delete();
        if ($response)
            return "Content deleted successfully.";
        else return "Content not found";
    }

}
