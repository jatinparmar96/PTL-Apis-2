<?php

namespace App\Api\V1\Controllers\Authentication;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;

use Auth;

class LogoutController extends Controller
{
    public function logout(Request $request, JWTAuth $JWTAuth)
    {
        $status = 'success';
        $status_code = 200;

        $token_expire = $JWTAuth->parseToken()->invalidate();
        if(!$token_expire)
        {
            $status = 'error';
            $status_code = 500;
        }
        
        return response()->json([
                'status' => $status,
                'status_code' => $status_code,
                'message' => "You're Logged Out Successully !!"
            ]);
    }
}
