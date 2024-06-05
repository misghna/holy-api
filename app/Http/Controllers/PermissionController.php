<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log; 

class PermissionController extends Controller
{
  
    public function grantAccess(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer|exists:users,id',
        'permissions' => 'required|array',
        'permissions.*.page_config_id' => 'required|integer|exists:page_config,id',
        'permissions.*.access_level' => 'required|string|in:READ,WRITE'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()->all()
        ], 422);
    }

    $userId = $request->input('user_id');
    $permissions = $request->input('permissions');
    $currentUserId = Auth::id();

    // Check if the current user has ADMIN access on all specified pages
    $pageConfigIds = array_column($permissions, 'page_config_id');
    $adminAccessPageIds = Permission::whereIn('page_config_id', $pageConfigIds)
                                ->where('user_id', $currentUserId)
                                ->where('access_level', 'ADMIN')
                                ->pluck('page_config_id')
                                ->toArray();

    $missingAdminAccess = array_diff($pageConfigIds, $adminAccessPageIds);

    if (!empty($missingAdminAccess)) {
      return response()->json([
        'success' => false,
        'message' => 'Unauthorized to grant access on one or more pages',
        'missing_access' => $missingAdminAccess
    ], 403);
}


    $timestamp = gmdate('Y-m-d H:i:s');
    $bulkInsertData = [];

    foreach ($permissions as $permission) {
        $bulkInsertData[] = [
            'user_id' => $userId,
            'page_config_id' => $permission['page_config_id'],
            'access_level' => $permission['access_level'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    DB::transaction(function () use ($bulkInsertData, $userId) {
        // Delete existing permissions for the user and specified pages
        $pageConfigIds = array_column($bulkInsertData, 'page_config_id');
        Permission::where('user_id', $userId)
                  ->whereIn('page_config_id', $pageConfigIds)
                  ->delete();

        
        Permission::insert($bulkInsertData);
    });

    return response()->json([
        'success' => true,
        'message' => 'Permissions granted successfully'
    ], 200);
}


    public function revokeAccess(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'page_config_id' => 'required|integer|exists:page_config,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $userId = $request->input('user_id');
        $pageConfigId = $request->input('page_config_id');
        $currentUserId = Auth::id();

        // Check if the current user has ADMIN access on the specified page
        $hasAdminAccess = Permission::where('user_id', $currentUserId)
                                    ->where('page_config_id', $pageConfigId)
                                    ->where('access_level', 'ADMIN')
                                    ->exists();

        if (!$hasAdminAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to revoke access on page ' . $pageConfigId
            ], 403);
        }

        // Revoke the access
        Permission::where('user_id', $userId)
                  ->where('page_config_id', $pageConfigId)
                  ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Access revoked successfully'
        ], 200);
    }

    
    public function updateAccessLevel(Request $request): JsonResponse
{   
    Log::info('updateAccessLevel called', $request->all());
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer|exists:users,id',
        'page_config_id' => 'required|integer|exists:page_config,id',
        'access_level' => 'required|string|in:READ,WRITE'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()->all()
        ], 422);
    }

    $userId = $request->input('user_id');
    $pageConfigId = $request->input('page_config_id');
    $accessLevel = $request->input('access_level');
    $currentUserId = Auth::id();

    // Check if the current user has ADMIN access on the specified page
    $hasAdminAccess = Permission::where('user_id', $currentUserId)
                                ->where('page_config_id', $pageConfigId)
                                ->where('access_level', 'ADMIN')
                                ->exists();

    if (!$hasAdminAccess) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized to update access level on page ' . $pageConfigId
        ], 403);
    }

   
    $permission = Permission::where('user_id', $userId)
                            ->where('page_config_id', $pageConfigId)
                            ->first();

    if ($permission) {
        $permission->access_level = $accessLevel;
        $permission->save();

        return response()->json([
            'success' => true,
            'message' => 'Access level updated successfully'
        ], 200);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Permission not found for the specified user and page'
        ], 404);
    }
}

}
