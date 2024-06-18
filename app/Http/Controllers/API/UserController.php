<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Permission;
use App\Models\API\PageConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function loginUser(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return Response(['message' => $validator->errors()], 401);
        }
        if (Auth::attempt($request->all())) {
            $user = Auth::user();
            $accessToken = $user->createToken('MyApp', ['server:login'])->plainTextToken;
            $refreshToken = $user->createToken('MyAppRefreshToken', ['server:refresh'])->plainTextToken;

            return Response([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                // Access token expires in 60 minutes - Can be adjusted as needed
                'expires_at' => now()->addMinutes(60)->toDateTimeString(),
            ], 200);
        }

        return Response(['message' => 'Email or password wrong'], 401);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function userDetails(): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            return Response(['data' => $user], 200);
        }
        return Response(['data' => 'Unauthorized'], 401);
    }

    /**
     * Refresh access token end point.
     */
    public function refreshToken(Request $request): Response
    {
        $user = $request->user();
        $refreshToken = $request->user()->tokens()->where('name', 'MyAppRefreshToken')->first();

        if (!$refreshToken) {
            return Response(['message' => 'Refresh token not found'], 401);
        }
        //To revoke all existing tokens except refresh token
        $user->tokens()->where('id', '!=', $refreshToken->id)->delete();
        $accessToken = $user->createToken('MyApp', ['server:login'])->plainTextToken;
        return Response([
            'access_token' => $accessToken,
            // Access token expires in 60 minutes - Can be adjusted as needed
            'expires_at' => now()->addMinutes(60)->toDateTimeString(),
        ], 200);
    }

    /**
     * Logout user and invalidate tokens.
     */
    public function logout(Request $request): Response
    {
        $user = $request->user();
        $user->tokens()->delete(); // Invalidate all user tokens

        return Response(['message' => 'User logout successful.'], 200);
    }


 public function all(Request $request): JsonResponse
{
    $currentUserId = Auth::id();
    $tenantId = $request->header('tenant_id');

    if (!$this->hasAccess($currentUserId, ['R', 'RW'], $tenantId)) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized to view users.'
        ], 403);
    }

    // Eager load permissions and page configs for users
    $users = User::select("id", "name", "email", "phone_number")
        ->with(['permissions' => function ($query) use ($tenantId) {
            $query->whereHas('pageConfig', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            });
        }])
        ->get();

    if ($users->isNotEmpty()) {
        $securePageNames = PageConfig::where('tenant_id', $tenantId)
                                     ->where('page_type', 'secure')
                                     ->pluck('name')
                                     ->toArray();

        $usersData = $users->map(function ($user) use ($securePageNames) {
            $userArray = $user->only(['id', 'name', 'email', 'phone_number']);
            $permissions = $user->permissions->groupBy('pageConfig.name')->map(function ($item) {
                return [
                    'access_level' => $item->pluck('access_level')->first(),
                    'updated_at' => $item->pluck('updated_at')->max(),
                    'updated_by' => $item->pluck('updated_by')->first()
                ];
            });

            // Set updated_at and updated_by to null initially
            $userArray['updated_at'] = null;
            $userArray['updated_by'] = null;

            foreach ($permissions as $pageName => $details) {
                $userArray[str_replace(' ', '_', strtolower($pageName))] = $details['access_level'];
                if ($details['updated_at']) {
                    $userArray['updated_at'] = $details['updated_at'];
                }
                if ($details['updated_by']) {
                    $userArray['updated_by'] = User::where('id', $details['updated_by'])->value('name');
                }
            }

            // Include secure page names with 'access_level' if not already included
            foreach ($securePageNames as $securePageName) {
                $key = str_replace(' ', '_', strtolower($securePageName));
                if (!isset($userArray[$key])) {
                    $userArray[$key] = 'N'; // Default to 'N' for no access
                }
            }

            // Remove the 'permissions' key
            unset($userArray['permissions']);

            return $userArray;
        });

        $columns = [
            ["value" => "id", "name" => "Id"],
            ["value" => "name", "name" => "Name"],
            ["value" => "email", "name" => "Email"],
            ["value" => "phone_number", "name" => "Phone Number"]
        ];

        foreach ($securePageNames as $securePageName) {
            $columns[] = [
                "value" => str_replace(' ', '_', strtolower($securePageName)),
                "name" => $securePageName
            ];
        }

        $columns[] = ["value" => "updated_at", "name" => "Updated At"];
        $columns[] = ["value" => "updated_by", "name" => "Updated By"];

        return response()->json([
            'columns' => $columns,
            'data' => $usersData->toArray()
        ], 200);
    } else {
        return response()->json([
            'message' => 'Not Found'
        ], 404);
    }
}

    public function store(Request $request): JsonResponse
{
    $currentUserId = Auth::id();
    $tenantId = $request->header('tenant_id'); 

    if (!$this->hasAccess($currentUserId, 'RW', $tenantId)) { 
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized to add a new user.'
        ], 403);
    }

    $valRules = [
        'name' => 'required|string',
        'email' => 'required|string|email|unique:users,email',
        'phone_number' => 'required|string',
        'access' => 'required|array'
    ];

    $data = $request->input();
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

    DB::beginTransaction();

    try {
        $user = User::create($data);

        // Save permissions
        $this->savePermissions($user->id, $data['access'], $tenantId, $currentUserId ); 

        DB::commit();

        return response()->json([
            'data' => $user,
            'message' => 'Success, User added successfully'
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Failed to add user or permissions',
            'error' => $e->getMessage()
        ], 500);
    }
}


  public function update(Request $request): JsonResponse
{
    $currentUserId = Auth::id();
    $tenantId = $request->header('tenant_id'); 

    if (!$this->hasAccess($currentUserId, 'RW', $tenantId)) { 
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized to update the user.'
        ], 403);
    }

    $valRules = [
        'id' => 'required|integer',
        'name' => 'required|string',
        'email' => 'required|string|email|unique:users,email,' . $request->input('id'),
        'phone_number' => 'required|string',
        'access' => 'required|array'
    ];

    $data = $request->input();
    $data['updated_at'] = gmdate('Y-m-d H:i:s'); 

    $validator = Validator::make($data, $valRules);

    if (!$validator->passes()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()->all()
        ], 422);
    }

    DB::beginTransaction();

    try {
        $user = User::findOrFail($data['id']);
        $user->fill($data);
        $user->save();

       
        $this->savePermissions($user->id, $data['access'], $tenantId, $currentUserId ); 

        DB::commit();

        return response()->json([
            'data' => $user,
            'message' => 'Success, User updated successfully'
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Failed to update user or permissions',
            'error' => $e->getMessage()
        ], 500);
    }
}


 public function one(Request $request): JsonResponse
{
    $currentUserId = Auth::id();
    $tenantId = $request->header('tenant_id'); 

    if (!$this->hasAccess($currentUserId, ['R', 'RW'], $tenantId)) { 
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized to view user.'
        ], 403);
    }
    
    $request->validate([
        'id' => 'required|integer',
    ]);

    $id = $request->input('id');
    $user = User::where('id', $id)
        ->select("id", "name", "email", "phone_number")
        ->first();

    if ($user) {
        $securePageNames = PageConfig::where('tenant_id', $tenantId)
                                     ->where('page_type', 'secure')
                                     ->pluck('name')
                                     ->toArray();

        $permissions = Permission::where('user_id', $id)
            ->whereHas('pageConfig', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->get()
            ->groupBy('pageConfig.name')
            ->map(function ($item) {
                return $item->pluck('access_level')->first();
            });

        $accessArray = [];

        // Include permissions and default to 'N' for secure pages without access
        $uniquePages = array_unique(array_merge($securePageNames, $permissions->keys()->toArray()));

        foreach ($uniquePages as $pageName) {
            $key = str_replace(' ', '_', strtolower($pageName));
            $accessArray[] = [
                $key => $permissions->get($pageName, 'N')
            ];
        }

        $userArray = $user->toArray();
        $userArray['access'] = $accessArray;

        return response()->json($userArray, 200);
    } else {
        return response()->json(['message' => 'User not found'], 404);
    }
}


   public function destroy(Request $request): JsonResponse
{
    $currentUserId = Auth::id();
    $tenantId = $request->header('tenant_id'); 

    if (!$this->hasAccess($currentUserId, 'RW', $tenantId)) { 
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized to delete user.'
        ], 403);
    }

    $request->validate([
        'id' => 'required|integer|exists:users,id',
    ]);

    $id = $request->input('id');
    $user = User::find($id);

    if ($user) {
        DB::beginTransaction();

        try {
            // Delete permissions within the tenant context
            Permission::where('user_id', $id)
                ->whereHas('pageConfig', function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId);
                })
                ->delete();

            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    } else {
        return response()->json(['message' => 'User not found'], 404);
    }
}


    private function savePermissions($userId, $accessData, $tenantId, $currentUserId )
{
    if (!is_array($accessData)) {
        throw new \Exception("Access data must be an array.");
    }

    $pageConfigIds = [];
    $invalidPages = [];

    foreach ($accessData as $access) {
        if (!is_array($access) || count($access) !== 1) {
            throw new \Exception("Each access entry must be an object with a single key-value pair.");
        }
        foreach ($access as $key => $value) {
            $pageConfigIds[strtolower($key)] = $this->getPageConfigId(strtolower($key), $tenantId);
        }
    }

    foreach ($pageConfigIds as $pageConfigName => $pageConfigId) {
        if (!$pageConfigId) {
            $invalidPages[] = $pageConfigName;
        }
    }

    if (!empty($invalidPages)) {
        throw new \Exception("Page configuration(s) do not exist: " . implode(', ', $invalidPages));
    }

    // Clear existing permissions for the user
    Permission::where('user_id', $userId)->delete();

    $bulkInsertData = [];
    $timestamp = gmdate('Y-m-d H:i:s');

    foreach ($accessData as $access) {
        foreach ($access as $key => $value) {
            $bulkInsertData[] = [
                'user_id' => $userId,
                'page_config_id' => $pageConfigIds[strtolower($key)],
                'access_level' => $value,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'updated_by' => $currentUserId 
            ];
        }
    }

  
    Permission::insert($bulkInsertData);
}


    private function getPageConfigId($pageConfigName, $tenantId)
{
    return PageConfig::where(DB::raw('lower(name)'), strtolower($pageConfigName))
                     ->where('tenant_id', $tenantId)
                     ->value('id');
}

     private function hasAccess($userId, $requiredAccessLevel, $tenantId)
{
    return Permission::where('user_id', $userId)
        ->whereHas('pageConfig', function ($query) use ($tenantId) {
            $query->where(DB::raw('lower(name)'), 'user profile')
                  ->where('tenant_id', $tenantId);
        })
        ->whereIn('access_level', (array) $requiredAccessLevel)
        ->exists();
}

    



   
}
