<?php

namespace App\Api\V1\Controllers\Authentication;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use JWTAuth as JWTAuth1;
use App\Model\User;
use App\Model\Session;


class LoginController extends Controller
{
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email', 'password']);
        
        try {
            $user = User::where('email', $credentials['email'])->firstOrFail();
        } 
        catch (\Exception $e) {
            return response()->json([
                        'status' => false,
                        'status_code' => 200,
                        'message' => "Incorrect Email or Password !",
                    ]);
        }
/*
        $user_info = app('App\Api\V1\Controllers\Authentication\UserController')->user_info($user['id']);
        $user_info = $user_info[0];
        if($user_info->is_active == 'No')
        {
			return response()->json([
                        'status' => 'error',
                        'status_code' => 200,
                        'message' => "Account is Inactive !!",
                    ]);
        }

        $today = date('Y-m-d');
        if($today > $user_info->expiry_date)
        {
            return response()->json([
                        'status' => 'error',
                        'status_code' => 200,
                        'message' => "Your application has been expired. Kindly renew it !!",
                    ]);
        }
*/
        //TODO: type update as per ROLE
        $companies = $user->getCompanies()->get(['id','display_name']);
        $user_payload = [
            'id' => (int)$user['id'],
            'name' => $user['display_name'],
            'companies'=>$companies
        ];

        try {
                $token = $JWTAuth->attempt($credentials,$user_payload);
                if(!$token) {;
                    return response()->json([
                        'status' => false,
                        'status_code' => 200,
                        'message' => "Incorrect Email or Password !!"
                    ]);
                }

            } 
            catch (JWTException $e) {
                return response()->json([
                        'status' => 'error',
                        'status_code' => 500,
                        'message' => "Something is Wrong !!"
                ]);
            }

      //  $result = $this->save_token($request, $token);
        return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => "Login Successully !!",
                'data' => $user_payload,
                'token' => $token
            ]);
    }
    
    public function refresh_token(Request $request, JWTAuth $JWTAuth){
        try {
            return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => "Token Refresh !!",
                    'token' => $JWTAuth->refresh($JWTAuth->getToken())
                ]);
        } 
        catch (JWTException $e) {
            return response()->json([
                    'status' => false,
                    'status_code' => 401,
                    'message' => "Something is Wrong !!",
                    'token' => $e->getMessage()
                ], 401);
        }
    }

    public function save_token(Request $request, $token)
    {
        $ip_address = $request->ip();
        $token_value = JWTAuth1::getPayload($token)->toArray();
        $session = Session::create([
                    "user_id"                =>  $token_value['id'],
                    "token"                  =>  $token,
                    "ip_address"             =>  $ip_address,
                    "timestamp"              =>  date('Y-m-d H:i:s'),
                    "one_signal_user_id"     =>  $request->get('one_signal_user_id'),
                    "one_signal_token"       =>  $request->get('one_signal_token'),
        ]);
    }
}


?>