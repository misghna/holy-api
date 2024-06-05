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

   
    public function store(Request $request): JsonResponse
    {
        $currentUserId = Auth::id();

        if (!$this->hasRWAccess($currentUserId)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $valRules = [
            'name' => 'required|string',
            'email' => 'required|string',
            'phone_number' => 'required|string'
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

        $user = User::create($data);

        return response()->json([
            'data' => $user,
            'message' => 'Success, User added successfully'
        ], 200);
    }
     public function update(Request $request): JsonResponse
    {
        $currentUserId = Auth::id();

        if (!$this->hasRWAccess($currentUserId)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update the user.'
            ], 403);
        }

        $valRules = [
            'id' => 'required|integer',
            'name' => 'required|string',
            'email' => 'required|string',
            'phone_number' => 'required|string'
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

        $user = User::findOrFail($data['id']);
        $user->fill($data);
        $user->save();

        return response()->json([
            'data' => $user,
            'message' => 'Success, User updated successfully'
        ], 200);
    }

    public function one(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $id = $request->input('id');
        $user = User::where('id', $id)
            ->select("id", "name", "email", "phone_number")
            ->first();

        if ($user) {
            $user->access = Permission::where('user_id', $id)
                ->get()
                ->groupBy('page_config_id')
                ->map(function ($item) {
                    return $item->pluck('access_level')->first();
                });

            return response()->json($user, 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

     public function all(Request $request): Response
    {
        $users = User::select("id", "name", "email", "phone_number")->get()->toArray();

        if ($users) {
            foreach ($users as &$user) {
                $permissions = Permission::where('user_id', $user['id'])
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $pageConfig = PageConfig::find($item->page_config_id);
                        return [$pageConfig->name => $item->access_level];
                    });

                // Add permissions to the user data
                $user = array_merge($user, $permissions->toArray());
            }

            $columns = [
                ["value" => "id", "name" => "Id"],
                ["value" => "name", "name" => "Name"],
                ["value" => "email", "name" => "Email"],
                ["value" => "phone_number", "name" => "Phone Number"]
            ];

            // Add permission columns dynamically
            $permissionColumns = array_keys($users[0]);
            foreach ($permissionColumns as $col) {
                if (!in_array($col, ['id', 'name', 'email', 'phone_number'])) {
                    $columns[] = ["value" => $col, "name" => ucwords(str_replace("_", " ", $col))];
                }
            }

            return Response(['columns' => $columns, 'data' => $users], 200);
        } else {
            return Response(['message' => 'Not Found'], 200);
        }
    }
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $id = $request->input('id');
        $response = User::where([["id", $id]])->delete();
        if ($response)
            return "User deleted successfully.";
        else return "User not found";
    }

     private function savePermissions($userId, $accessData)
{
    $pageConfigIds = [];
    $invalidPages = [];

   
    foreach ($accessData as $access) {
        foreach ($access as $key => $value) {
            $pageConfigIds[$key] = $this->getPageConfigId($key);
        }
    }

    // Check if all page configuration IDs exist
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
    foreach ($accessData as $access) {
        foreach ($access as $key => $value) {
            $bulkInsertData[] = [
                'user_id' => $userId,
                'page_config_id' => $pageConfigIds[$key],
                'access_level' => $value,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    }

    if (!empty($bulkInsertData)) {
        Permission::insert($bulkInsertData);
    }
}

    private function getPageConfigId($pageConfigName)
    {
        $pageConfig = PageConfig::where('name', $pageConfigName)->first();
        return $pageConfig ? $pageConfig->id : null;
    }

   private function hasRWAccess($userId)
{
    return Permission::where('user_id', $userId)
        ->whereHas('pageConfig', function ($query) {
            $query->where('name', 'User Profile');
        })
        ->where('access_level', 'RW')
        ->exists();
}


   
}
