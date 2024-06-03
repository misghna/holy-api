<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\API\PageConfig;
use App\Models\FileMapper;
use App\Models\Permission;
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

    try {
        DB::transaction(function () use ($data, $headerImg, &$pageConfig) {
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

            // Assign ADMIN access to the creator of the page
            Permission::create([
                'user_id' => $data['updated_by'],
                'page_config_id' => $pageConfig->id,
                'access_level' => 'ADMIN'
            ]);
        });

        $pageConfigArray = $pageConfig->toArray();
        $pageConfigArray['header_img'] = $headerImg;

        return response()->json([
            'data' => $pageConfigArray,
            'message' => 'Success'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while creating the page configuration',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function update(Request $request): JsonResponse
{
    $tenantId = $request->header('tenant_id');

    $valRules = [
        'id' => 'required|integer',
        'page_type' => 'required|string',
        'name' => 'required|string',
        'description' => 'required|string',
        'parent' => 'required|integer',
        'header_img' => 'required|array',
        'header_text' => 'required|string',
        'page_url' => 'required|string',
        'tenant_id' => 'required|integer|exists:tenants,id',
        'seq_no' => 'required|integer',
        'language' => 'required|string'
    ];

    $data = $request->all();
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

    $data['updated_by'] = Auth::user()->id;

    // Check if the user has ADMIN or WRITE access
    $hasPermission = Permission::where('user_id', $data['updated_by'])
                                ->where('page_config_id', $data['id'])
                                ->whereIn('access_level', ['ADMIN', 'WRITE'])
                                ->exists();

    if (!$hasPermission) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Extract header_img from data
    $headerImg = $data['header_img'];
    unset($data['header_img']);

    try {
        DB::transaction(function () use ($data, $headerImg, &$pageConfig) {
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
                $headerImgData = array_map(function ($fileId) use ($pageConfig, $data) {
                    return [
                        'ref_id' => $pageConfig->id,
                        'ref_type' => 'page_config',
                        'file_id' => $fileId,
                        'updated_by' => $data['updated_by'],
                        'created_at' => $data['updated_at'],
                        'updated_at' => $data['updated_at']
                    ];
                }, $imagesToAdd);

                FileMapper::insert($headerImgData);
            }
        });

        $pageConfigArray = $pageConfig->toArray();
        $pageConfigArray['header_img'] = $headerImg;

        return response()->json([
            'data' => $pageConfigArray,
            'message' => 'Success, Page Config updated successfully'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while updating the page configuration',
            'error' => $e->getMessage()
        ], 500);
    }
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

    $userId = Auth::id();

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

    $pageConfigs = DB::select("SELECT pc.*, concat('[', COALESCE(GROUP_CONCAT(fm.file_id), ''), ']') as header_img
        FROM page_config pc
        LEFT JOIN file_mapper fm ON fm.ref_id = pc.id AND fm.ref_type = 'page_config'
        LEFT JOIN permissions p ON p.page_config_id = pc.id AND p.user_id = ?
        WHERE pc.tenant_id = ?
        AND (pc.page_type = 'public' OR p.access_level IN ('READ', 'WRITE', 'ADMIN'))
        GROUP BY pc.id, name, page_type, description, parent, header_text, page_url, tenant_id, created_at, updated_at, seq_no, language, updated_by
        LIMIT ? OFFSET ? ", [$userId, $tenantId, $limit, $start]);

    if (empty($pageConfigs)) {
        return response()->json([
            'success' => false,
            'message' => 'No records found for the provided tenant ID',
        ], 404);
    }

    foreach ($pageConfigs as $r) {
        $r->header_img = json_decode($r->header_img);
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
    $userId = Auth::id();

    $pageConfig = DB::selectOne("SELECT pc.*, CONCAT('[', COALESCE(GROUP_CONCAT(fm.file_id), ''), ']') as header_img
        FROM page_config pc
        LEFT JOIN file_mapper fm ON fm.ref_id = pc.id AND fm.ref_type = 'page_config'
        LEFT JOIN permissions p ON p.page_config_id = pc.id AND p.user_id = ?
        WHERE pc.tenant_id = ?
        AND pc.id = ?
        AND (pc.page_type = 'public' OR p.access_level IN ('READ', 'WRITE', 'ADMIN'))
        GROUP BY pc.id, name, page_type, description, parent, header_text, page_url, tenant_id, created_at, updated_at, seq_no, language, updated_by ", [$userId, $tenantId, $id]);

    if (!$pageConfig) {
        return response()->json([
            'success' => false,
            'message' => 'Page configuration not found or access denied for the provided tenant ID and ID',
        ], 404); 
    }

    $pageConfig->header_img = json_decode($pageConfig->header_img);

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
    $userId = Auth::id();

 
    $hasAdminAccess = Permission::where('user_id', $userId)
                                ->where('page_config_id', $id)
                                ->where('access_level', 'ADMIN')
                                ->exists();

    if (!$hasAdminAccess) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

  
    $pageConfig = PageConfig::where([["tenant_id", $tenantId], ["id", $id]])->first();

    if (!$pageConfig) {
        return response()->json([
            'success' => false,
            'message' => 'Page Config not found for the provided tenant ID and ID',
        ], 404); 
    }

    try {
        DB::transaction(function () use ($id, $pageConfig) {
            // Delete related entries in the file_mapper table
            FileMapper::where('ref_id', $id)->where('ref_type', 'page_config')->delete();

         
            $pageConfig->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Page Config deleted successfully.',
        ], 200); 
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while deleting the page configuration',
            'error' => $e->getMessage()
        ], 500);
    }
}


}
