<?php

namespace App\Api\V1\Controllers\Operations;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Model\Leave;
use Dingo\Api\Routing\Helpers;

class LeaveController extends Controller
{
	public function query()
	{
		$token = JWTAuth::getPayload()->toArray();
		$query = DB::table('leaves as l')
					->join('leave_types as lt', 'lt.id', '=', 'l.leave_type_id')
					->join('users as u', 'l.user_id', '=', 'u.id')
					->select('l.id', 'l.user_id', 'l.application_date', 'l.leave_start_date', 'l.leave_end_date', 'l.leave_type_id', 'l.approve', 'l.comments')
					->addSelect(DB::raw('DATE_FORMAT(l.application_date, "%d-%M-%Y") as application_date1'))
					->addSelect(DB::raw('DATE_FORMAT(l.leave_start_date, "%d-%M-%Y") as leave_start_date1'))
					->addSelect(DB::raw('DATE_FORMAT(l.leave_end_date, "%d-%M-%Y") as leave_end_date1'))
					->addSelect('lt.name')
					->addSelect('u.fullname')
					->where('u.company_id', '=', $token['company_id'] );

		return $query;
	}

	public function TableColumn()
    {         
        $TableColumn = array(
                       "application_date"=>"l.application_date",
                       "leave_start_date"=>"l.leave_start_date",
                       "leave_end_date"=>"l.leave_end_date",
                       "approve"=>"l.approve",
                       "comments"=>"l.comments",

                       "name"=>"lt.name",
                       "fullname"=>"u.fullname",
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

    public function conditions($query)
    {
        $token = JWTAuth::getPayload()->toArray();
        $user_level = $token['user_level'];
        if($user_level == 4)
        {
            $query = $query->Where('u.userlevel_id', '>=', $user_level);
            $query = $query->Where('u.department_id', '=', $token['department_id']);
        }
        if($user_level > 4)
        {
            $query = $query->Where('l.user_id', '=', $token['id']);
        }
        return $query;
      
    }

	public function index()
	{
		$limit = 10;
        $query = $this->query();
        $query = $this->search($query);
        $query = $this->sort($query);
        $query = $this->conditions($query);
        $result = $query->paginate($limit);
        return response()->json([
				'status' => 'success',
				'status_code' => 200,
				'message' => 'Leave Types List',
				'data' => $result
				]);
	}

	public function store(Request $request)
	{
		$status = "success";
		$status_code = 200;
		$message = "Data saved successfully !!";
		$token = JWTAuth::getPayload()->toArray();

		$id = $request->get('id');
		if($id == 'new')
		{
			$leave = new Leave;
			$leave->inserted_by_id = $token['id'];
		}
		else
		{
			$leave = Leave::findOrFail($id);
		}

		$today = date('Y-m-d H:i:s');

		$leave_start_date = $request->get('leave_start_date');
		$leave_end_date = $request->get('leave_end_date');

		$count = $this->check_task_leave($leave_start_date, $leave_end_date);
		if($count > 0)
		{
           	return response()->json([
						'status' => "error",
						'status_code' => $status_code,
						'message' => 'Task Assigned, Can Not Apply for Leave !!',
						'data' => []
              ]);
      	}

		$leave->user_id = $token['id'];
		$leave->application_date = $today;
		$leave->leave_start_date = $leave_start_date;
		$leave->leave_end_date = $leave_end_date;
		$leave->leave_type_id = $request->get('leave_type_id');
		$leave->comments = $request->get('comments');

		$leave->updated_by_id = $token['id'];

		try {
				$leave->save();
		}
		catch (\Exception $e) {
				$leave = [];
				$status = "error";
				$status_code = 500;
				$message = "Something is Wrong !!";
		}

		return response()->json([
                'status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data' => $leave
            ]);

	}

	public function check_task_leave($leave_start_date, $leave_end_date, $user_id = NULL)
	{
		$token = JWTAuth::getPayload()->toArray();
		if($user_id == NULL)
			$user_id = $token['id'];

		$count = DB::table('tasks')
					->Where('forward_date', '>=', $leave_start_date)
					->Where('forward_date', '<=', $leave_end_date)
					->Where('user_id', '=', $user_id)
					->count();

		return $count;
	}

 	public function show($id)
 	{
 		try {
            $leave = Leave::findOrFail($id);
        } 
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! Leave not found');
        }
 		return $leave;
 	}

 	public function leave_approve($id, $approve = '0')
  	{
		$status = "success";
		$status_code = 200;
		$message = "Status Updated Successfully";

		$token = JWTAuth::getPayload()->toArray();
		$leave = Leave::findOrFail($id);

		$leave_start_date = $leave->leave_start_date;
		$leave_end_date = $leave->leave_end_date;
		$user_id = $leave->user_id;

		if($approve == "0")
		{
			$approve = "Rejected";
			$approve_date = NULL;
			$approve_by = NULL;
		}
		else
		{
			$count = $this->check_task_leave($leave_start_date, $leave_end_date, $user_id);
			if($count > 0)
			{
				return response()->json([
							'status' => "error",
							'status_code' => $status_code,
							'message' => 'Task Assigned, Can Not Approve Leave !!',
							'data' => []
	              ]);
			}
			
			$approve = "Approved";
			$approve_date = date('Y-m-d H:i:s');
			$approve_by = $token['id'];
		}

		/*$approve = $leave['approve'];

		if($approve == "No")
		{
			$approve = "Yes";
			$approve_date = date('Y-m-d H:i:s');
			$approve_by = $token['id'];
		}
		else
		{
			$approve = "No";
			$approve_date = NULL;
			$approve_by = NULL;
		}*/

		$data = [];
		$data['approve'] = $approve;
		$data['approve_date'] = $approve_date;
		$data['approve_by'] = $approve_by;
		$data['updated_by_id'] = $token['id'];

		try{
		    	$leave->update($data);
		}
		catch (\Exception $e) {
			  $status = "error";
			  $status_code = 500;
			  $message = "Something is Wrong !! $e";
		}

		return response()->json([
		            'status' => $status,
		            'status_code' => $status_code,
		            'message' => $message,
		            'data' => $leave
		  ]);
  }

    public function update(Request $request, $id)
    {
    	$user = JWTAuth::parseToken()->toUser();
    	$leave = Leave::findOrFail($id);
    	$data = $request->all();
    	$data['updated_by_id'] = $user->id;
    	$leave->update($data);
    	return $leave;
    }

	public function destroy($id)
	{
		if(Leave::destroy($id)){
			return response()->json([
				'status' => 'ok'
				]);
		}
		else{
			throw new NotFoundHttpException('Oops! Leave not found.');
		}
	}

}
