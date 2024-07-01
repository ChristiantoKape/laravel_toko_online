<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /**
     * Handles user login
     * 
     * Validates the request data for email and password, attempts to authenticate the user,
     * and return a JSON response with the authentication token if successful.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');
        if (!$token = auth()->guard('api_admin')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email or Password is incorrect'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => auth()->guard('api_admin')->user(),
            'token' => $token
        ], 200);
    }

    /**
     * Retrieves the currently authenticated user.
     * Returns a JSON response with the authenticated user's data.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser()
    {
        // response data "user" yang sedang login
        return response()->json([
            'success' => true,
            'user' => auth()->guard('api_admin')->user()
        ], 200);
    }

    /**
     * Refreshes the authentication token.
     * Generates a new token for the authenticated user and sets it in the request header
     * Returns a JSON response with the noew token and user's data.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        // refresh token
        $refreshToken = JWTAuth::refresh(JWTAuth::getToken());

        // set user dengan "token" baru
        $user = JWTAUth::setToken($refreshToken)->toUser();

        // set header "Authorization" dengan type Bearer + "token" baru
        $request->headers->set('Authorization', 'Bearer ' . $refreshToken);

        // response data "user" dengan "token" baru
        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $refreshToken
        ], 200);
    }

    /**
     * logs out the authenticated user.
     * Invalidates the current authentication token and returns a JSON response indicating success.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // remove "token" JWT
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        // response "success" logout
        return response()->json([
            'success' => true,
        ], 200);
    }
}
