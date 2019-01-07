<?php

namespace App\Api\V1\Controllers\Others;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
// use App\SmsCampaign;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\ValidationHttpException;

// for SMS
use Ixudra\Curl\Facades\Curl;

class NotificationController extends Controller
{
      public function send()
      {
              $content = array(
                "en" => 'Internet off testing'
                );
              
              $fields = array(
                'app_id' => "a7e478ce-7cef-4e5e-b113-669d7140991b",
                'include_player_ids' => array("36be0436-cf9a-48a2-978e-af661d97dcaf","5b1f286d-dc39-42ff-9321-fa1ae104a6c2"),
                'data' => array("title_mohit" => "body by Mohit"),
                'contents' => $content
              );
              
              $fields = json_encode($fields);
                // print("\nJSON sent:\n");
                print($fields);
              
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
              curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                     'Authorization: Basic NGEwMGZmMjItY2NkNy0xMWUzLTk5ZDUtMDAwYzI5NDBlNjJj'));
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
              curl_setopt($ch, CURLOPT_HEADER, FALSE);
              curl_setopt($ch, CURLOPT_POST, TRUE);
              curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

              $response = curl_exec($ch);
              curl_close($ch);
              
              return $response;
      }

      public function singleNotification()
      {
            $response = $this->send();
            $return["allresponses"] = $response;
            $return = json_encode( $return);
            
            print("\n\nJSON received:\n");
            print($return);
            print("\n");
      }
}
