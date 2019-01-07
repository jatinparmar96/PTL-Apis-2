<?php

namespace App\Api\V1\Controllers\Authentication;

use Config;
use App\Model\User;
use App\Model\Company;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\SignUpRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Dingo\Api\Exception\ValidationHttpException;

class SignUpController extends Controller
{
    public function signUp(SignUpRequest $request, JWTAuth $JWTAuth)
    {
        $user = new User($request->all());
      
        /*if(!$user->save()) {
            throw new HttpException(500);
        }*/
        try {
            $user->save();
        }
        catch (\Exception $e) {
            $error = $e->errorInfo;
            if($error[1] == 1062)
            {
                throw new ValidationHttpException([ 'username' => 'Duplicate entry found for similar Email ID !!']);
            }
            else
            {
              throw new NotFoundHttpException ($e);
            }
        }

        // $company = Company::create([
        //                 "name"  =>  $request->get('name'),
        //                 "email"  => $request->get('username'),
        //                 "expiry_date"  => date('Y-m-d', strtotime("+7 day")),
        //             ]);
        // $user->company_id = $company->id;
        // $user->userlevel_id = 2;
        // $user->is_active = 'Yes';
        // $user->update();

        if(!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'status' => 'ok'
            ], 201);
        }

        $token = $JWTAuth->fromUser($user);
        return response()->json([
            'status' => 'ok',
            'token' => $token
        ], 201);
    }
}
