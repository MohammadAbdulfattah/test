<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Configuration;

class CustomTokenAuth
{
    public function handle($request, Closure $next)
    {
        // if ($request->has('token')) {



        //     $tokenString = $request->token;

        //     try {
        //         $config = Configuration::forUnsecuredSigner();
        //         $token = $config->parser()->parse($tokenString);

        //         $id = $token->claims()->get('jti');
        //         $tokenModel = app(TokenRepository::class)->find($id);

        //         if ($tokenModel && !$tokenModel->revoked) {
        //             Auth::loginUsingId($tokenModel->user_id);


        //             return $next($request);
        //         }
        //     } catch (\Exception $e) {
        //         return redirect()->route('login');
        //     }
        // } ط
      

        $tokenString = null;

    if ($request->bearerToken()) {
        $tokenString = $request->bearerToken();
    }
    if ($tokenString) {
        try {
            $config = Configuration::forUnsecuredSigner();
            $token = $config->parser()->parse($tokenString);

            $id = $token->claims()->get('jti');
            $tokenModel = app(TokenRepository::class)->find($id);

            if ($tokenModel && !$tokenModel->revoked) {
                Auth::loginUsingId($tokenModel->user_id);
                
                return $next($request);
            }
        } catch (\Exception $e) {
            return redirect()->route('login');
        }
    }
        else {
            if (Auth::check()) {
             //   Auth::logout();
                return $next($request);
            }
            return redirect()->route('login');
        }
    }
}
