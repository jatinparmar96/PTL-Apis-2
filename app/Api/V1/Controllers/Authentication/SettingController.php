<?php

namespace App\Api\V1\Controllers\Authentication;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Model\Setting;
use Dingo\Api\Routing\Helpers;

class SettingController extends Controller
{

    //use Helpers;
	public function query()
	{
		$token = JWTAuth::getPayload()->toArray();
		$query = DB::table('settings as s')
					->select('s.id', 's.detail', 's.option', 's.value', 's.created_at', 's.updated_at');
		return $query;
	}
	public function query_without_token()
	{
		$query = DB::table('settings as s')
					->select('s.id', 's.detail', 's.option', 's.value', 's.created_at', 's.updated_at');
		return $query;
	}

	public function TableColumn()
    {         
        $TableColumn = array(
                       "detail"=>"s.detail",
                       "option"=>"s.option",
                       "value"=>"s.value"
                       );
        return $TableColumn;
    }

    public function sort($query)
    {
       $sort = \Request::get('sort');
       if(!empty($sort))
       {
         	$TableColumn = $this->TableColumn();
         	$query = $query->orderBy($TableColumn[key($sort)], $sort[key($sort)]);
       }
       return $query;       
    }

    public function search($query)
    {      
		$search = \Request::get('search');
		if(!empty($search))
		{
			$TableColumn = $this->TableColumn();
			foreach($search as $key=>$searchvalue)
			{ 
				$query =  $query->Where($TableColumn[$key], 'LIKE', '%'.$searchvalue.'%');
			}
		}

        return $query;
    }

    public function index()
    {
    	$status = "success";
		$status_code = 200;
		$message = "Setting List";
		$limit = 10;
        $query = $this->query();
        $query = $this->search($query);
        $query = $this->sort($query);

        try{
	        	$result = $query->paginate($limit);
	        	if($result->count() == 0)
		        {
		        	$result = [];
		        	$status = "error";
					$status_code = 200;
					$message = "Records not Found !!";
		        }
        }
        catch (\Exception $e) {
        		$result = [];
        		$status = "error";
				$status_code = 500;
				$message = "Something is Wrong !!";
        }

        return response()->json([
				'status' => $status,
				'status_code' => $status_code,
				'message' => $message,
				'data' => $result
            ]);
    }

 	public function show($id)
 	{
 		$status = "success";
		$status_code = 200;
		$message = "Setting Detail Information";
 		try {
	            $query = $this->query();
		        $query = $this->search($query);
		        $query = $this->sort($query);
		        $query = $query->where('s.id', '=', $id );
		        $result = $query->first();
		        if(!$result)
		        {
		        	$result = [];
		        	$status = "error";
					$status_code = 200;
					$message = "Records not Found !!";
		        }
        } 

        catch (\Exception $e) {
        		$result = [];
        		$status = "error";
				$status_code = 500;
				$message = "Something is Wrong !!";
        }

 		return response()->json([
                'status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data' => $result
            ]);
 	}

 	public function by_option($option = NULL)
 	{
		$status = "success";
		$status_code = 200;
		$message = "Setting Detail Information";
 		try {
	            $query = $this->query();
		        $query = $this->search($query);
		        $query = $this->sort($query);
		        $query = $query->where('s.option', '=', $option );
		        $result = $query->first();
		        if(!$result)
		        {
		        	$result = [];
		        	$status = "error";
					$status_code = 200;
					$message = "Records not Found !!";
		        }
        } 

        catch (\Exception $e) {
        		$result = [];
        		$status = "error";
				$status_code = 500;
				$message = "Something is Wrong !! $e";
        }

 		return response()->json([
                'status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data' => $result
            ]);
 	}


 	public function app_version()
 	{
		$status = "success";
		$status_code = 200;
		$message = "Setting Detail Information";
 		try {
	            $query = $this->query_without_token();
		        $query = $this->search($query);
		        $query = $this->sort($query);
		        $query = $query->where('s.option', '=', 'app_version' );
		        $result = $query->first();
		        if(!$result)
		        {
		        	$result = [];
		        	$status = "error";
					$status_code = 200;
					$message = "Records not Found !!";
		        }
        } 

        catch (\Exception $e) {
        		$result = [];
        		$status = "error";
				$status_code = 500;
				$message = "Something is Wrong !! $e";
        }

 		return response()->json([
                'status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data' => $result
            ]);
 	}

}
