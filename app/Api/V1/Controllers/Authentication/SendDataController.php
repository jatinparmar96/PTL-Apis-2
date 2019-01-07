<?php

namespace App\Api\V1\Controllers\Authentication;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Model\Client;
use Dingo\Api\Routing\Helpers;

class SendDataController extends Controller
{
	public function sendData(
								$status = "success", 
								$status_code = 200, 
								$message = " ", 
								$data = " "
							)
	{
		return response()->json([
                'status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data' => $data
            ]);
	}



}

?>