<?php

namespace Modules\Connector\Http\Controllers\Api\Auth;

use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => __('connector::lang.auth_failed')], 401);
        }

        // Continue to request the token from Passport
        $response = Http::post(env('APP_URL') . '/oauth/token', [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
            'username' => $request->username,
            'password' => $request->password,
            'scope' => '',
        ]);

        if ($response->failed()) {
            return response()->json(['message' => __('connector::lang.auth_failed')], 401);
        }

        return response()->json($response->json());
    }

    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = Http::asForm()->post(env('APP_URL') . '/oauth/token', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
            'scope'         => '',
        ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Failed to refresh token'], 401);
        }

        return response()->json($response->json());
    }

    /**
     * Logout and revoke token
     */
    public function logout(Request $request)
    {
        if (!$request->user('api')) {
            return response()->json(['message' => "Unauthenticated."], 401);
        }
        $request->user('api')->token()->revoke();
        return response()->json(['message' =>  __('connector::lang.logout_success')]);
    }
}
