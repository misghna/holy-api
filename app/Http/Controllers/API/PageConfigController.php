<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\API\PageConfig;
use App\Models\FileMapper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    
    $headerImgData = array_map(function ($fileId) use ($pageConfig, $data) {
        return [
            'ref_id' => $pageConfig->id,
            'ref_type' => 'page_config',
            'file_id' => $fileId,
            'updated_by' => $data['updated_by'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at']
        ];
    }, $headerImg);

    // Bulk insert header_img data into FileMapper
    FileMapper::insert($headerImgData);

    $pageConfigArray = $pageConfig->toArray();
    $pageConfigArray['header_img'] = $headerImg;

    return response()->json([
        'data' => $pageConfigArray,
        'message' => 'Success'
    ], 200);
}

  public function update(Request $request): JsonResponse
{
    $tenantId = $request->header('tenant_id');

    $valRules = [
        'id' => 'integer',
        'page_type' => 'string',
        'name' => 'string',
        'description' => 'string',
        'parent' => 'integer',
        'header_img' => 'array',
        'header_text' => 'string',
        'page_url' => 'string',
        'seq_no' => 'integer',
        'language' => 'string',
        'tenant_id' => 'required|integer|exists:tenants,id'
    ];


    $data = $request->only(array_keys($valRules));

    $data['tenant_id'] = $tenantId;
    $data['updated_at'] = gmdate('Y-m-d H:i:s');

    $validator = Validator::make($data, $valRules);

    if (!$validator->passes()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()->all()
        ], 422);
    }

  
    $headerImg = $data['header_img'] ?? null;
    if (isset($headerImg)) {
        unset($data['header_img']);
    }

    $pageConfig = PageConfig::findOrFail($request->input('id'));
    $pageConfig->fill($data);
    $pageConfig->save();

    if (isset($headerImg)) {
        $existingHeaderImages = FileMapper::where('ref_id', $pageConfig->id)
            ->where('ref_type', 'page_config')
            ->pluck('file_id')
            ->toArray();

        $imagesToAdd = array_diff($headerImg, $existingHeaderImages);
        $imagesToRemove = array_diff($existingHeaderImages, $headerImg);

        if (count($imagesToRemove) > 0) {
            FileMapper::where('ref_id', $pageConfig->id)
                ->where('ref_type', 'page_config')
                ->whereIn('file_id', $imagesToRemove)
                ->delete();
        }

        if (count($imagesToAdd) > 0) {
            foreach ($imagesToAdd as $fileId) {
                FileMapper::create([
                    'ref_id' => $pageConfig->id,
                    'ref_type' => 'page_config',
                    'file_id' => $fileId,
                    'updated_by' => $request->input('updated_by'),
                ]);
            }
        }

        $pageConfigArray = $pageConfig->toArray();
        $pageConfigArray['header_img'] = $headerImg;
    } else {
        $pageConfigArray = $pageConfig->toArray();
    }

    return response()->json([
        'data' => $pageConfigArray,
        'message' => 'Success, Page Config updated successfully'
    ], 200);
}

  public function all(Request $request)
{
    $tenantId = $request->header('tenant_id');


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

    $totalRows = DB::table('page_config')
        ->where('tenant_id', $tenantId)
        ->count();

    // Check if start exceeds total rows
    if ($start >= $totalRows) {
        return response()->json([
            'data' => [],
            'totalRows' => $totalRows
        ]);
    }

    $pageConfigs = DB::select("
        SELECT pc.*, 
               CONCAT('[', COALESCE(GROUP_CONCAT(fm.file_id), ''), ']') AS header_img,
               u.name AS updated_by_name
        FROM page_config pc
        LEFT JOIN file_mapper fm ON fm.ref_id = pc.id AND fm.ref_type = 'page_config'
        LEFT JOIN users u ON u.id = pc.updated_by
        WHERE pc.tenant_id = ?
        GROUP BY pc.id, pc.name, pc.page_type, pc.description, pc.parent, pc.header_text, 
                 pc.page_url, pc.tenant_id, pc.created_at, pc.updated_at, pc.seq_no, 
                 pc.language, pc.updated_by, u.name
        LIMIT ? OFFSET ?
    ", [$tenantId, $limit, $start]);

    if ($pageConfigs == null) {
        return response()->json([
            'success' => false,
            'message' => 'No records found for the provided tenant ID',
        ], 404);
    }

    foreach ($pageConfigs as $r) {
        $r->header_img = json_decode($r->header_img);
        $r->updated_by = $r->updated_by_name; 
        unset($r->updated_by_name); 
    }

    return response()->json([
        'data' => $pageConfigs,
        'totalRows' => $totalRows
    ]);
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