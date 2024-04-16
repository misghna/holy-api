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
    
            $accessToken =  $user->createToken('MyApp')->plainTextToken; 
            $refreshToken = $user->createToken('RefreshToken')->plainTextToken;
    
            return response()->json([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken
            ], 200);
        }

        return response()->json(['message' => 'Email or password is wrong'], 401);

    }

    /**Adding a new API endpoint for refreshing tokens if needed. */
    public function refreshToken(Request $request): Response
    {
        $request->validate([
            'refresh_token' => 'required',
        ]);

        if (Auth::user()->tokens()->delete()) {
            $user = Auth::user();
            $accessToken = $user->createToken('MyApp')->plainTextToken;
        
            return response()->json([
                'access_token' => $accessToken,
            ], 200);
        }
        return response()->json(['message' => 'Invalid refresh token'], 401);
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
     * Display the specified resource.
     */
    public function logout(): Response
    {
        $user = Auth::user();

        $user->currentAccessToken()->delete();
        
        return Response(['data' => 'User Logout successfully.'],200);
    }
}