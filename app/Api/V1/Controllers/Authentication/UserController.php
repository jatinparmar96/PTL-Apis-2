<?php

namespace App\Api\V1\Controllers\Authentication;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Model\User;
use Dingo\Api\Routing\Helpers;

class UserController extends Controller
{
    /**
	* List all users
	*/
	public function index()
	{
	
		$user = JWTAuth::parseToken()->toUser();
		$limit = 10;
		// return User::paginate($limit);
		return DB::table('users as u')
					->join('companies as c', 'u.company_id', '=', 'c.id')
					->leftJoin('departments as d', 'd.id', '=', 'u.department_id')
					->join('userlevels as ul', 'u.userlevel_id', '=', 'ul.id')

					->select('u.id', 'u.company_id', 'u.userlevel_id', 'u.is_active', 'u.fullname', 'u.username', 'u.mobile_2', 'u.landline', 'u.address', 'u.pincode', 'u.photo', 'u.comments')
					->addSelect('ul.name as user_level', 'ul.alias')
					->addSelect('d.name as department_name', 'ul.alias')

					->where('u.company_id', '=', $user->company_id )
					->paginate($limit);
	}

	public function user_info($id)
	{
		// $token = JWTAuth::getPayload()->toArray();
		$url = url('/data/company_logo/');
		return DB::table('users as u')
					->join('companies as c', 'u.company_id', '=', 'c.id')
					->join('userlevels as ul', 'u.userlevel_id', '=', 'ul.id')
					->select('u.id', 'u.company_id', 'u.userlevel_id', 'u.department_id', 
						'u.is_active', 'u.fullname', 'u.username', 'u.mobile_2', 'u.landline', 'u.address', 'u.pincode', 'u.photo', 'u.comments')
					->addSelect('ul.name as user_level', 'ul.alias')
					->addSelect('c.expiry_date')
					->addSelect(DB::raw("CONCAT('$url/', c.logo) as logo1"))
					// ->where('u.company_id', '=', $token['company_id'] )
					->where('u.id', '=', $id )
					->get();
	}

	public function user_by_dept($department_id = NULL )
	{
		$token = JWTAuth::getPayload()->toArray();
		$user_level = $token['user_level'];
		$query = DB::table('users as u')
					->join('companies as c', 'u.company_id', '=', 'c.id')
					->join('departments as d', 'u.department_id', '=', 'd.id')
					->select('u.id', 'u.company_id', 'u.userlevel_id', 'u.department_id', 
						'u.is_active', 'u.fullname', 'u.username', 'u.mobile_2', 'u.landline', 'u.address', 'u.pincode', 'u.photo', 'u.comments')
					->addSelect('d.name as department_name')
					->where('u.company_id', '=', $token['company_id'] );

		if($department_id != NULL)
		{
			$query->where('u.department_id', '=', $department_id);
		}
		if($user_level < 4)
		{
			// display all user's list if upper hierarchy logged in
		}
		else if($user_level == 4)
		{
			if($token['department_id'] == $department_id)
			{
				// if department head is logged in then display all user's list from same department
				// do nothing
			}
			else
			{
				// display employee's of same department
				$query->where('u.userlevel_id', '<=', '3');
			}
		}
		else
		{
			if($token['department_id'] == $department_id)
			{
				$query->where('u.userlevel_id', '<=', '3');
				$query->Where('u.id', '<=', $token['id']);
			}
			else
			{
				$query->orWhere('u.userlevel_id', '<=', '3');
			}
		}

		$result = $query->get();

		return response()->json([
        			'status'	=>	'success',
        			'status_code' => 200,
        			'message'	=>	'Department wise Users Full List',
        			'data'		=>	$result
        		]);
	}

	public function users_list()
	{
	  	$token = JWTAuth::getPayload()->toArray();
	  	$result = DB::table('users as u')
	                ->join('companies as c', 'u.company_id', '=', 'c.id')
	                ->join('departments as d', 'u.department_id', '=', 'd.id')
	                ->select('u.id', 'u.company_id', 'u.userlevel_id', 'u.department_id', 'u.fullname', 'u.username')
	                ->addSelect('d.name as department_name')
	                ->where('u.company_id', '=', $token['company_id'] )
	                ->where('u.is_active', '=', 'Yes' )
	                ->get();

	 return response()->json([
				'status' => 'success',
				'status_code' => 200,
				'message' => 'Users Full List',
				'data' => $result
				]);
	}

	public function office_boy()
	{
		$user = JWTAuth::parseToken()->toUser();
		return DB::table('users as u')
					->join('companies as c', 'u.company_id', '=', 'c.id')
					->join('departments as d', 'u.department_id', '=', 'd.id')
					->select('u.id', 'u.company_id', 'u.userlevel_id', 'u.department_id', 
						'u.is_active', 'u.fullname', 'u.username', 'u.mobile_2', 'u.landline', 'u.address', 'u.pincode', 'u.photo', 'u.comments')
					->addSelect('d.name as department_name')
					->where('u.company_id', '=', $user->company_id )
					->where('u.userlevel_id', '=', '6' )
					->paginate();

					
	}

	public function store(Request $request)
	{
    	$token = JWTAuth::getPayload()->toArray();
		$user = new User;
		$user->department_id = $request->get('department_id');
		$user->userlevel_id = $request->get('userlevel_id');
		$user->fullname = $request->get('fullname');
		$user->username = $request->get('username');
		$user->mobile_2 = $request->get('mobile_2');
		$user->landline = $request->get('landline');
		$user->address = $request->get('address');
		$user->pincode = $request->get('pincode');
		$user->is_active = $request->get('is_active');
		
		$user->company_id = $token['company_id'];
		$user->inserted_by_id = $token['id'];
		$user->updated_by_id = $token['id'];

		if($request->get('password'))
			$user->password = $request->get('password');

		$user->save();

		return $user;

	}

	public function update(Request $request, $id)
    {
    	$token = JWTAuth::getPayload()->toArray();
    	$user = User::findOrFail($id);
    	// $data = $request->all();
    	$data = [

    		"department_id"=>$request->get('department_id'),
			"userlevel_id"=>$request->get('userlevel_id'),
			"fullname"=>$request->get('fullname'),
			"username"=>$request->get('username'),
			"mobile_2"=>$request->get('mobile_2'),
			"landline"=>$request->get('landline'),
			"address"=>$request->get('address'),
			"pincode"=>$request->get('pincode'),
			"is_active"=>$request->get('is_active'),
			"comments"=>$request->get('comments'),
		];
		if($request->get('password'))
			$data['password'] = $request->get('password');

    	$data['updated_by_id'] = $token['id'];
    	$user->update($data);
    	return $user;
    }
}
