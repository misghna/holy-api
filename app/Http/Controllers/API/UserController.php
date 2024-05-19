<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $valRules = [
            'name' => 'required|string',
            'email' => 'required|string',
            'phone_number' => 'required|string',
            'access' => 'required|json'
        ];
        $data = $request->input();
        $data['created_at'] = gmdate('Y-m-d H:i:s');
        $data['updated_at'] = gmdate('Y-m-d H:i:s');
        $data['access_config'] = $request->input("access");


        $validator = Validator::make($data, $valRules);

        if (!$validator->passes()) {
            dd($validator->errors()->all());
        }

        $user = User::create($data);

        return response()->json([
            'data' => $user,
            'message' => 'Success, User added successfully'
        ], 200);
    }

    public function update(Request $request): JsonResponse
    {  
        $valRules = [
            'id' => 'required|integer',
            'name' => 'required|string',
            'email' => 'required|string',
            'phone_number' => 'required|string',
            'access' => 'required|json'
        ];

        $data = $request->input();
        $data['created_at'] = gmdate('Y-m-d H:i:s');
        $data['updated_at'] = gmdate('Y-m-d H:i:s');
        $data['access_config'] = $request->input("access");

        $validator = Validator::make($data, $valRules);

        if (!$validator->passes()) {
            dd($validator->errors()->all());
        }

        $user = User::findOrFail($data['id']);
        $user->fill($data);
        $user->save();

        return response()->json([
            'pageConfig' => $user,
            'message' => 'Success, User updated successfully'
        ], 200);

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

    public function one(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $id = $request->input('id');
        $user = User::where([["id", $id]])
            ->select("id", "name", "email", "phone_number", "access_config AS access")
            ->first();
        // DB::raw("JSON_ARRAY(JSON_OBJECT('content_manager',content_manager,'finance',finance,'admin_settings',admin_settings)) AS access")    
        return $user;
    }
    public function all(Request $request): Response
    {
        // $users = User::select("id", "name", "email", "phone_number", "content_manager", "finance", "admin_settings")
        $users = User::select("id", "name", "email", "phone_number", DB::raw("JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(access_config,'$[*].content_manager'),'$[0]')) as content_manager"), DB::raw("JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(access_config,'$[*].finance'),'$[0]')) as finance"), DB::raw("JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(access_config,'$[*].admin_settings'),'$[0]')) as admin_settings"))
            ->get()->toArray();
        if ($users) {
            $columns = [];
            $arrayKeys = array_keys($users[0]);
            foreach ($arrayKeys as $col) {
                $column["value"] = $col;
                $column["name"] = ucwords(str_replace("_", " ", $col));
                $columns[] = $column;
            }
            return Response(['columns' => $columns, 'data' => $users], 200);
        } else return Response(['message' => 'Not Found'], 200);
    }
}
