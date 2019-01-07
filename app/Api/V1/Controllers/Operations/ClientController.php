<?php

namespace App\Api\V1\Controllers\Operations;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Model\Client;
use Dingo\Api\Routing\Helpers;

class ClientController extends Controller
{	
  	public function query()
	{
		$token = JWTAuth::getPayload()->toArray();
		$query = DB::table('clients as cl')
					->join('companies as c', 'c.id', '=', 'cl.company_id')
					->select('cl.id', 'cl.type', 'cl.salutation', 'cl.code', 'cl.name', 'cl.company_name', 'cl.mobile', 'cl.landline', 'cl.dobr', 'cl.email_id', 'cl.gender', 'cl.aadhar_no', 'cl.pan_no', 'cl.address1', 'cl.address2', 'cl.address3', 'cl.city', 'cl.pincode', 'cl.comments')
					->addSelect(DB::raw('IF(cl.gender = "M", "Male", "Female") as gender_full'))
					->addSelect(DB::raw('IF(cl.type = "0", "Individual", "Company") as client_type'))
					->addSelect(DB::raw('DATE_FORMAT(cl.dobr, "%d-%M-%Y") as dobr1'))
					->addSelect(DB::raw('concat(cl.name, " (", cl.code, ")") as combine_name'))
					->where('cl.company_id','=', $token['company_id'] );
		return $query;
	}

   	public function TableColumn()
    {         
        $TableColumn = array(
                       "code"=>"cl.code",
                       "name"=>"cl.name",
                       "company_name"=>"cl.company_name",
                       "mobile"=>"cl.mobile",
                       "type"=>"cl.type",
                       "email_id"=>"cl.email_id",
                       "pan_no"=>"cl.pan_no",
                       "aadhar_no"=>"cl.aadhar_no",
                       "comments"=>"cl.comments",
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
       else
       {
       		$query = $query->orderBy('cl.name', 'ASC');
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
				if($searchvalue != '')
				{
					$query =  $query->where($TableColumn[$key], 'LIKE', '%'.$searchvalue.'%');
				}
			}
		}

        return $query;
    }

	public function index()
	{
		$status = "success";
		$status_code = 200;
		$message = "Client List";
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
		$message = "Client Detail Information";
 		try {
	            $query = $this->query();
		        $query = $this->search($query);
		        $query = $this->sort($query);
		        $query = $query->where('cl.id', '=', $id );
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


	public function form(Request $request)
	{
		$status = "success";
		$status_code = 200;
		$message = "Data saved successfully !!";
		$token = JWTAuth::getPayload()->toArray();

		$id = $request->get('id');
		if($id == 'new')
		{
			$client = new Client;
			$client->inserted_by_id = $token['id'];
		}
		else
		{
			$client = Client::findOrFail($id);
		}

		$dobr = $request->get('dobr');
		if(!empty($dobr))
		{
			if($dobr != "null")
			{
				$dobr = date('Y-m-d', strtotime($dobr));
				$client->dobr = $dobr;
			}
		}

		$client->type = $request->get('type');
		$client->salutation = $request->get('salutation');
		$client->name = $request->get('name');
		$client->company_name = $request->get('company_name');
		$client->code = $request->get('code');
		$client->mobile = $request->get('mobile');
		$client->landline = $request->get('landline');
		$client->email_id = $request->get('email_id');
		$client->gender = $request->get('gender');
		$client->aadhar_no = $request->get('aadhar_no');
		$client->pan_no = $request->get('pan_no');
		$client->aadhar_no = $request->get('aadhar_no');
		$client->address1 = $request->get('address1');
		$client->address2 = $request->get('address2');
		$client->address3 = $request->get('address3');
		$client->city = $request->get('city');
		$client->pincode = $request->get('pincode');
		$client->comments = $request->get('comments');

		$client->company_id = $token['company_id'];
		$client->updated_by_id = $token['id'];
		
		try {
				$client->save();
		}
		catch (\Exception $e) {
				$client = [];
				$status = "error";
				$status_code = 500;
				$message = "Something is Wrong !!";
		}

		return response()->json([
                'status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data' => $client
            ]);

	}

	public function destroy($id)
	{
		$status = "success";
		$status_code = 200;
		$message = "Record Deleted Successfully";
		$token = JWTAuth::getPayload()->toArray();
		try {
				$client = Client::Where('company_id', '=', $token['company_id'])
								->Where('id', '=', $id)
								->first();
				if($client)
				{
					$client->delete();
				}
				else
				{
					$status = "error";
					$status_code = 200;
					$message = "Record not Found !!";	
				}
		}
		catch (\Exception $e) {
				$status = "error";
				$status_code = 500;
				$message = "Something is Wrong !!";
		}

		return response()->json([
                'status' => $status,
                'status_code' => $status_code,
                'message' => $message
            ]);
	}

 	public function search_client($client_id = NULL )
	{
		$status = "success";
		$status_code = 200;
		$message = "Client List ";
		$token = JWTAuth::getPayload()->toArray();
		DB::enableQueryLog();
		$query = DB::table('clients as cl')
					->join('companies as c', 'cl.company_id', '=', 'c.id')
					->select('cl.id', 'cl.name', 'cl.code', 'cl.company_name')
					->addSelect(DB::raw('CONCAT(COALESCE(cl.name,""), " / ", COALESCE(cl.company_name,""), " (", COALESCE(cl.code,""), ")") as combine_name'))
				// 	->where('cl.name', 'like', '%'.$client_id.'%')
				// 	->orWhere('cl.code', 'like', '%'.$client_id.'%')
				    ->where('cl.company_id','=', $token['company_id'] )
				    
				    // ->where('cl.name','LIKE', "% $client_id %" )
				    // ->orWhere('cl.code','LIKE', "% $client_id %" )
				    // ->orWhere('cl.company_name','LIKE', "% $client_id %" )
				    
				    
				    ->whereRaw("(cl.name LIKE  '%$client_id%' OR cl.code LIKE  '%$client_id%' OR cl.company_name LIKE  '%$client_id%' )")
				    
				    // ->whereRaw("concat(cl.name, cl.code, cl.company_name) LIKE  '%$client_id%' ")
					->limit(10);
		try {
				$result = $query->get();
				// dd(DB::getQueryLog());
				if($result->count() == 0)
		        {
		        	$result = [];
		        	$status = "error";
					$status_code = 200;
					$message = "Record not Found !!";
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


	/*   OLD CODE KEPT ONLY REFERENCE
	public function store(Request $request)
	{
    	$token = JWTAuth::getPayload()->toArray();
    	$dobr = $request->get('dobr');
		if(!is_null($dobr))
		{
			$dobr = date('Y-m-d', strtotime($dobr));
		}
		$doba = $request->get('doba');
		if(!is_null($doba))
		{
			$dobr = date('Y-m-d', strtotime($dobr));
    	}
		
    	$data = $request->all();
		$data['dobr'] = $dobr;      
		$data['doba'] = $doba;
		
		$client = new Client;
		
		foreach($data as $key=>$value)
		{
			$client->$key = $value;
		}
		$client->inserted_by_id = $token['id'];
		$client->updated_by_id = $token['id'];
		
		try {
			$client->save($data);
		}
		catch (\Exception $e) {
			$error = $e->errorInfo;
			if($error[1] == 1062)
			{
			  return response()->json([
					  'status' => 'error',
					  'message' => 'Duplicate entry found for similar Email ID !!'
					]);
			}
		}
		return response()->json([
		  'status' => 'ok',
		  'data' => $client
		]);
	}

 	
    public function update(Request $request, $id)
    {

    	$user = JWTAuth::parseToken()->toUser();
    	$task = Task::findOrFail($id);
    	$data = $request->all();
    	// $client_id = $data['client_id'];
    	// $data['client_id'] = $client_id['id'];

    	// $data['client_id'] = $data['client'];		LAST WORKING CODE

    	// $action_dt = $data['action_dt'];
    	// $action_dt = $action_dt['date'];
    	// $action_dt = $action_dt['year'].'-'.$action_dt['month'].'-'.$action_dt['day'];
    	// $data['action_dt'] = $action_dt;

    	$forward_dt = $data['forward_dt'];
    	$forward_dt = $forward_dt['date'];
    	$forward_dt = $forward_dt['year'].'-'.$forward_dt['month'].'-'.$forward_dt['day'];
    	$data['forward_dt'] = $forward_dt;
    	
    	$data['updated_by_id'] = $user->id;

    	$task->update($data);
    	return $task;
    }

    */



}


?>