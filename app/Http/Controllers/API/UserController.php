<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;
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
   
        if($validator->fails()){
            return Response(['message' => $validator->errors()],401);
        }
        if(Auth::attempt($request->all())){
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
            return Response(['data' => $user],200);
        }
        return Response(['data' => 'Unauthorized'],401);
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
}