<?php
namespace App\Api\V1\Controllers\Authentication;

use JWTAuth ;

class TokenController {

    public static function getUser()
    {
        return JWTAuth::parseToken()->toUser();
    }
    
    public static function getCompanyId()
	{
		$token = JWTAuth::getPayload()->toArray();
		$current_company_id = $token['company_info']['id'];
		return $current_company_id;
	}
	public static function createTokenFromPayload($payload)
	{
		$user = static::getUser();
		$token = JWTAuth::fromUser($user,$payload);
		return $token;
	}
}
