<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\API\PageConfig;
use App\Models\FileMapper;
use Illuminate\Support\Facades\Auth;
use Validator;
use Log;

class PageConfigController extends Controller
{

   public function store(Request $request): JsonResponse
{
    $tenantId = $request->header('tenant_id');
    $valRules = [
        'page_type' => 'required|string',
        'name' => 'required|string',
        'description' => 'required|string',
        'parent' => 'required|integer',
        'header_img' => 'required|array',
        'header_text' => 'required|string',
        'seq_no' => 'required|integer',
        'language' => 'required|string',
        'page_url' => 'required|string',
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

    $data['updated_by'] = Auth::user()->id;

    // Extract header_img from data
    $headerImg = $data['header_img'];
    unset($data['header_img']);

    $pageConfig = PageConfig::create($data);

    // Store header_img in FileMapper
    foreach ($headerImg as $fileId) {
        FileMapper::create([
            'ref_id' => $pageConfig->id,
            'ref_type' => 'page_config',
            'file_id' => $fileId,
            'updated_by' => $data['updated_by'],
        ]);
    }

    
    $pageConfigArray = $pageConfig->toArray();
    $pageConfigArray['header_img'] = $headerImg;

    return response()->json([
        'data' => $pageConfigArray,
        'message' => 'Success'
    ], 200);
}



   public function update(Request $request): JsonResponse
{
    $valRules = [
        'id' => 'required|integer',
        'page_type' => 'required|string',
        'name' => 'required|string',
        'description' => 'required|string',
        'parent' => 'required|integer',
        'header_img' => 'required|array',
        'header_text' => 'required|string',
        'page_url' => 'required|string',
        'tenant_id' => 'required|integer',
        'seq_no' => 'required|integer',
        'language' => 'required|string'
    ];

    $data = $request->all();
    $data['updated_at'] = gmdate('Y-m-d H:i:s');

    $validator = Validator::make($data, $valRules);

    if (!$validator->passes()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()->all()
        ], 422);
    }

    // Extract header_img from data
    $headerImg = $data['header_img'];
    unset($data['header_img']);

    $pageConfig = PageConfig::findOrFail($data['id']);
    $pageConfig->fill($data);
    $pageConfig->save();

    // Fetch existing header_img entries
    $existingHeaderImages = FileMapper::where('ref_id', $pageConfig->id)
        ->where('ref_type', 'page_config')
        ->pluck('file_id')
        ->toArray();

    // Determine entries to add and remove
    $imagesToAdd = array_diff($headerImg, $existingHeaderImages);
    $imagesToRemove = array_diff($existingHeaderImages, $headerImg);

    // Delete obsolete header_img entries if there are any
    if (count($imagesToRemove) > 0) {
        FileMapper::where('ref_id', $pageConfig->id)
            ->where('ref_type', 'page_config')
            ->whereIn('file_id', $imagesToRemove)
            ->delete();
    }

    // Add new header_img entries if there are any
    if (count($imagesToAdd) > 0) {
        foreach ($imagesToAdd as $fileId) {
            FileMapper::create([
                'ref_id' => $pageConfig->id,
                'ref_type' => 'page_config',
                'file_id' => $fileId,
                'updated_by' => $data['updated_by'],
            ]);
        }
    }

  
    $pageConfigArray = $pageConfig->toArray();
    $pageConfigArray['header_img'] = $headerImg;

    return response()->json([
        'data' => $pageConfigArray,
        'message' => 'Success, Page Config updated successfully'
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

    $start = $request->input('start', 0);
    $limit = $request->input('limit', 10); 

    $pageConfigs = PageConfig::where('tenant_id', $tenantId)
        ->offset($start)
        ->limit($limit)
        ->get();

    if ($pageConfigs->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No records found for the provided tenant ID',
        ], 404);
    }

    $pageConfigs->each(function ($pageConfig) {
        $headerImages = FileMapper::where('ref_id', $pageConfig->id)
            ->where('ref_type', 'page_config')
            ->pluck('file_id')
            ->toArray();
        $pageConfig->header_img = $headerImages;
    });

    return response()->json($pageConfigs);
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

    
    $request->validate([
        'id' => 'required|integer|exists:page_config,id',
    ]);

    $id = $request->input('id');


    $pageConfig = PageConfig::where([["tenant_id", $tenantId], ["id", $id]])->first();

    if (!$pageConfig) {
        return response()->json([
            'success' => false,
            'message' => 'Page configuration not found for the provided tenant ID and ID',
        ], 404); 
    }

    // Fetch header images
    $headerImages = FileMapper::where('ref_id', $pageConfig->id)
        ->where('ref_type', 'page_config')
        ->pluck('file_id')
        ->toArray();
    $pageConfig->header_img = $headerImages;

    return response()->json($pageConfig);
}

    public function destroy(Request $request)
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

   
    $request->validate([
        'id' => 'required|integer|exists:page_config,id',
    ]);

  
    $id = $request->input('id');

    // Check if the PageConfig exists
    $pageConfig = PageConfig::where([["tenant_id", $tenantId], ["id", $id]])->first();

    if (!$pageConfig) {
        return response()->json([
            'success' => false,
            'message' => 'Page Config not found for the provided tenant ID and ID',
        ], 404); 
    }

    // Delete related entries in the file_mapper table
    FileMapper::where('ref_id', $id)->where('ref_type', 'page_config')->delete();

    // Delete the PageConfig record
    $response = $pageConfig->delete();

    if ($response) {
        return response()->json([
            'success' => true,
            'message' => 'Page Config deleted successfully.',
        ], 200); 
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Page Config could not be deleted',
        ], 500); 
    }
}

}
