<?php

namespace App\Api\V1\Controllers\Reports;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Model\Client;
use Dingo\Api\Routing\Helpers;

class DashboardController extends Controller
{

    //use Helpers;
	public function index()
	{
		return "No records found !!";
	}

	public function TableColumn()
    {         
        $TableColumn = array(
                       "department_id"=>"u.department_id",
                       "priority"=>"t.priority",
                       "client_name"=>"cl.name",
                       "task"=>"t.task",
                       "comments"=>"t.comments",
                       "work_types"=>"wt.name",
                       "employee"=>"u.fullname"
                       );
        return $TableColumn;
    }

    public function search($query)
    {      
        $search = \Request::get('search');
        if(!empty($search))
        {
            $TableColumn = $this->TableColumn();
            foreach($search as $key=>$searchvalue)
            {
            	if(!empty($searchvalue))
            	{
            		if($key == 'department_id')
            		{
            			$query =  $query->Where($TableColumn[$key], '=', $searchvalue);	
            		}
            		else
            		{
            			$query =  $query->Where($TableColumn[$key], 'LIKE', '%'.$searchvalue.'%');
            		}
                	
            	}
            }
        }

        return $query;
    }

	public function all_data()
 	{
 		$total_task = $this->overview();
 		$completed_task = $this->completed();
 		$pending_task = $this->pending();
 		$incomplete_task = $this->incomplete();
 		$today_task = $this->today_task();
 		$overdue_task = $this->overdue_task();
 		$future_task = $this->future_task();


 		$data['total_task'] = $total_task;
 		$data['completed_task'] = $completed_task;
 		$data['pending_task'] = $pending_task;
 		$data['incomplete_task'] = $incomplete_task;
 		$data['today_task'] = $today_task;
 		$data['overdue_task'] = $overdue_task;
 		$data['future_task'] = $future_task;

 		return response()->json([
				'data' => $data
				]);

 	}

 	public function overview()
 	{

 		$user = JWTAuth::parseToken()->toUser();
 		try {
 			$today = date('Y-m-d');
            $query = DB::table('tasks as t')
					->select('t.*')
					->join('users as u', 't.user_id', '=', 'u.id')
					->join('companies as c', 'u.company_id', '=', 'c.id')
					->where('t.forward_date','=',$today )
					->where('u.company_id', '=', $user->company_id );
					
			$query = $this->conditions($query);
			$result = $query->count();

			return $result;			
        }  
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! some error found !!'.$e);
        }
 	}

 	public function completed()
 	{

 		$user = JWTAuth::parseToken()->toUser();
 		try {
 			$today = date('Y-m-d');
            $query = DB::table('tasks as t')
					->select('t.*')
					->join('users as u', 't.user_id', '=', 'u.id')
					->join('companies as c', 'u.company_id', '=', 'c.id')
					->where('t.forward_date','=',$today )
					->where('t.completed_date','like', '%'.$today.'%' )
					->where('t.status','=', 'Done' )
					->where('u.company_id', '=', $user->company_id );
					
			$query = $this->conditions($query);
			$result = $query->count();

			return $result;	
        }  
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! some error found !!'.$e);
        }
 	}

 	public function pending()
 	{

 		$user = JWTAuth::parseToken()->toUser();
 		try {
 			$today = date('Y-m-d');
            $query = DB::table('tasks as t')
					->select('t.*')
					->join('users as u', 't.user_id', '=', 'u.id')
					->join('companies as c', 'u.company_id', '=', 'c.id')
					->where('t.forward_date','=',$today )
					->where('t.status','=', 'Pending' )
					->where('u.company_id', '=', $user->company_id );

			$query = $this->conditions($query);
			$result = $query->count();

			return $result;
        }  
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! some error found !!'.$e);
        }
 	}

 	public function incomplete()
 	{

 		$user = JWTAuth::parseToken()->toUser();
 		try {
 			$today = date('Y-m-d');
            $query = DB::table('tasks as t')
					->select('t.*')
					->join('users as u', 't.user_id', '=', 'u.id')
					->join('companies as c', 'u.company_id', '=', 'c.id')
					->where('t.status','=', 'Pending' )
					->where('t.forward_date','<',$today )
					->where('u.company_id', '=', $user->company_id );

			$query = $this->conditions($query);
			$result = $query->count();

			return $result;
        }  
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! some error found !!'.$e);
        }
 	}

 	public function today_task()
 	{
 		$token = JWTAuth::getPayload()->toArray();
 		$today = date('Y-m-d');
 		$query = DB::table('tasks as t')
					->leftJoin('users as u', 't.user_id', '=', 'u.id')
					->leftJoin('companies as c', 'u.company_id', '=', 'c.id')
					->leftJoin('clients as cl', 't.client_id', '=', 'cl.id')
					->leftJoin('departments as d', 'u.department_id', '=', 'd.id')
					->leftJoin('work_types as wt', 't.work_type_id', '=', 'wt.id')

					->select('t.id', 't.user_id', 't.client_id', 't.work_type_id', 't.task', 't.deadline_date', 't.deadline_time', 't.forward_date', 't.forward_time', 't.priority', 't.status', 't.completed_date', 't.approve', 't.approve_by_id', 't.approve_date', 't.created_at', 't.updated_at', 't.inserted_by_id', 't.updated_by_id')

					->addSelect(DB::raw('DATE_FORMAT(t.deadline_date, "%d-%M-%Y") as deadline_date1'))
					->addSelect('u.fullname')
					->addSelect('cl.name as client_name')
					->addSelect('d.name as department_name')
					->addSelect('wt.name as work_types')

					->where('u.company_id', '=', $token['company_id'] )
					->where('t.forward_date', '=', $today )
					->where('t.status', '=', 'Pending')
					->orderBy('t.priority', 'ASC');
		
		$query = $this->search($query);
		$query = $this->conditions($query);
		$today_task = $query->get();
		return $today_task;
 	}

 	public function overdue_task()
 	{
 		$token = JWTAuth::getPayload()->toArray();
 		$today = date('Y-m-d');
 		$query = DB::table('tasks as t')
					->leftJoin('users as u', 't.user_id', '=', 'u.id')
					->leftJoin('companies as c', 'u.company_id', '=', 'c.id')
					->leftJoin('clients as cl', 't.client_id', '=', 'cl.id')
					->leftJoin('departments as d', 'u.department_id', '=', 'd.id')
					->leftJoin('work_types as wt', 't.work_type_id', '=', 'wt.id')

					->select('t.id', 't.user_id', 't.client_id', 't.work_type_id', 't.task', 't.deadline_date', 't.deadline_time', 't.forward_date', 't.forward_time', 't.priority', 't.status', 't.completed_date', 't.approve', 't.approve_by_id', 't.approve_date', 't.created_at', 't.updated_at', 't.inserted_by_id', 't.updated_by_id')

					->addSelect(DB::raw('DATE_FORMAT(t.deadline_date, "%d-%M-%Y") as deadline_date1'))
					->addSelect('u.fullname')
					->addSelect('cl.name as client_name')
					->addSelect('d.name as department_name')
					->addSelect('wt.name as work_types')

					->where('u.company_id', '=', $token['company_id'] )
					->where('t.forward_date', '<', $today )
					->where('t.status', '=', 'Pending')
					->orderBy('t.priority', 'ASC');

		$query = $this->search($query);
		$query = $this->conditions($query);
		$overdue_task = $query->get();
		
		return $overdue_task;
 	}

 	public function future_task()
 	{
 		$token = JWTAuth::getPayload()->toArray();
 		$today = date('Y-m-d');
 		$query = DB::table('tasks as t')
					->leftJoin('users as u', 't.user_id', '=', 'u.id')
					->leftJoin('companies as c', 'u.company_id', '=', 'c.id')
					->leftJoin('clients as cl', 't.client_id', '=', 'cl.id')
					->leftJoin('departments as d', 'u.department_id', '=', 'd.id')
					->leftJoin('work_types as wt', 't.work_type_id', '=', 'wt.id')

					->select('t.id', 't.user_id', 't.client_id', 't.work_type_id', 't.task', 't.deadline_date', 't.deadline_time', 't.forward_date', 't.forward_time', 't.priority', 't.status', 't.completed_date', 't.approve', 't.approve_by_id', 't.approve_date', 't.created_at', 't.updated_at', 't.inserted_by_id', 't.updated_by_id')

					->addSelect(DB::raw('DATE_FORMAT(t.deadline_date, "%d-%M-%Y") as deadline_date1'))
					->addSelect('u.fullname')
					->addSelect('cl.name as client_name')
					->addSelect('d.name as department_name')
					->addSelect('wt.name as work_types')

					->where('u.company_id', '=', $token['company_id'] )
					->where('t.forward_date', '>', $today )
					->where('t.status', '=', 'Pending')
					->orderBy('t.priority', 'ASC');

		$query = $this->search($query);
		$query = $this->conditions($query);
		$future_task = $query->get();
		return $future_task;
 	}

 	public function dashboard_summary_numbers()
 	{
 		$summary_overdue = $this->summary_overdue();
 		$summary_today = $this->summary_today();
 		$summary_future = $this->summary_future(); 		


 		$data['summary_overdue'] = $summary_overdue;
 		$data['summary_today'] = $summary_today;
 		$data['summary_future'] = $summary_future;

 		$department_fulllist = app('App\Api\V1\Controllers\Masters\DepartmentController')->full_list();
 		$department_fulllist = $department_fulllist->original['data'];

 		return response()->json([
				'status' => 'success',
				'status_code' => 200,
				'message' => 'Task Summary :',
				'data' => $data,
				'department_fulllist' => $department_fulllist
				]);
 	}

 	public function summary_overdue()
 	{
 		$token = JWTAuth::getPayload()->toArray();
 		$today = date('Y-m-d');
 		// DB::enableQueryLog();
 		$query = DB::table('tasks as t')
 				->leftJoin('users as u', 't.user_id', '=', 'u.id')
				->select(DB::raw(" SUM(CASE WHEN t.priority = 'Critical' THEN 1 ELSE 0 END) as critical " ))
				->addSelect(DB::raw(" SUM(CASE WHEN t.priority = 'High' THEN 1 ELSE 0 END) as High " ))
				->addSelect(DB::raw(" SUM(CASE WHEN t.priority = 'Medium' THEN 1 ELSE 0 END) as Medium " ))
				->addSelect(DB::raw(" SUM(CASE WHEN t.priority = 'Low' THEN 1 ELSE 0 END) as Low " ))
 				->where('t.forward_date', '<', $today )
 				->where('u.company_id', '=', $token['company_id'] )
				->where('t.status', '=', "Pending")
				->orderBy('t.priority', 'ASC');

		$query = $this->search($query);
		$query = $this->conditions($query);

 		$summary_overdue = $query->get();
 		// dd(DB::getQueryLog());
		return $summary_overdue;
 	}

 	public function summary_today()
 	{
 		$token = JWTAuth::getPayload()->toArray();
 		$today = date('Y-m-d');
 		$query = DB::table('tasks as t')
 				->leftJoin('users as u', 't.user_id', '=', 'u.id')
				->select(DB::raw(" SUM(CASE WHEN t.priority = 'Critical' THEN 1 ELSE 0 END) as critical " ))
				->addSelect(DB::raw(" SUM(CASE WHEN t.priority = 'High' THEN 1 ELSE 0 END) as High " ))
				->addSelect(DB::raw(" SUM(CASE WHEN t.priority = 'Medium' THEN 1 ELSE 0 END) as Medium " ))
				->addSelect(DB::raw(" SUM(CASE WHEN t.priority = 'Low' THEN 1 ELSE 0 END) as Low " ))
 				->where('t.forward_date', '=', $today )
 				->where('u.company_id', '=', $token['company_id'] )
				->where('t.status', '=', "Pending")
				->orderBy('t.priority', 'ASC');
				
		$query = $this->search($query);
		$query = $this->conditions($query);

 		$summary_today = $query->get();
		return $summary_today;
 	}

 	public function summary_future()
 	{
 		$token = JWTAuth::getPayload()->toArray();
 		$today = date('Y-m-d');
 		$query = DB::table('tasks as t')
 		        ->leftJoin('users as u', 't.user_id', '=', 'u.id')
				->select(DB::raw(" SUM(CASE WHEN t.priority = 'Critical' THEN 1 ELSE 0 END) as critical " ))
				->addSelect(DB::raw(" SUM(CASE WHEN t.priority = 'High' THEN 1 ELSE 0 END) as High " ))
				->addSelect(DB::raw(" SUM(CASE WHEN t.priority = 'Medium' THEN 1 ELSE 0 END) as Medium " ))
				->addSelect(DB::raw(" SUM(CASE WHEN t.priority = 'Low' THEN 1 ELSE 0 END) as Low " ))
 				->where('t.forward_date', '>', $today )
 				->where('u.company_id', '=', $token['company_id'] )
				->where('t.status', '=', "Pending")
				->orderBy('t.priority', 'ASC');
				
		$query = $this->search($query);
		$query = $this->conditions($query);

 		$summary_future = $query->get();
		return $summary_future;
 	}
 	
 	public function conditions($query)
 	{
 	    $token = JWTAuth::getPayload()->toArray();
 	    $user_level = $token['user_level'];
 	    if($user_level > '4')
 	    {
 	        $query = $query->Where('t.user_id', '=', $token['id']);
 	    }
 	    if($user_level == '4')
 	    {
 	        $query = $query->Where('u.department_id', '=', $token['department_id']);
 	    }
 	    
 	    return $query;
 	}



}

?>