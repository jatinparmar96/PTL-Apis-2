<?php

namespace App\Api\V1\Controllers\Masters;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Model\LeaveType;
use Dingo\Api\Routing\Helpers;

class LeaveTypeController extends Controller
{

	public function query()
	{
		$token = JWTAuth::getPayload()->toArray();
		$query = DB::table('leave_types as lt')
					->join('companies as c', 'lt.company_id', '=', 'c.id')
					->select('lt.id', 'lt.company_id', 'lt.name')
					->where('lt.company_id', '=', $token['company_id'] );

		return $query;
	}

	public function TableColumn()
    {         
        $TableColumn = array(
                       "name"=>"lt.name",
                       "status"=>"lt.status"
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

    //use Helpers;
	public function index()
	{
		$limit = 10;
        $query = $this->query();
        $query = $this->search($query);
        $query = $this->sort($query);
        $result = $query->paginate($limit);
        return response()->json([
				'status' => 'success',
				'status_code' => 200,
				'message' => 'Leave Types List',
				'data' => $result
				]);
	}

	public function full_list()
	{
        $query = $this->query();
        $query = $this->search($query);
        $query = $this->sort($query);
        $result = $query->get();
        return response()->json([
				'status' => 'success',
				'status_code' => 200,
				'message' => 'Leave Types Full List',
				'data' => $result
				]);
	}


	public function store(Request $request)
	{
    	$user = JWTAuth::parseToken()->toUser();
		return LeaveType::create([
			
			"name"=>$request->get('name'),
			
			"company_id"=>$user->company_id,
			"inserted_by_id"=>$user->id,
			"updated_by_id"=>$user->id
			]);
	}

 	public function show($id)
 	{
 		try {
            $leaveType = LeaveType::findOrFail($id);
        } 
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! LeaveType not found');
        }
 		return $leaveType;
 	}

    public function update(Request $request, $id)
    {
    	$user = JWTAuth::parseToken()->toUser();
    	$leaveType = LeaveType::findOrFail($id);
    	$data = $request->all();
    	$data['updated_by_id'] = $user->id;
    	$leaveType->update($data);
    	return $leaveType;
    }

	public function destroy($id)
	{
		if(LeaveType::destroy($id)){
			return response()
			->json([
				'status' => 'ok'
				]);
		}
		else{
			throw new NotFoundHttpException('Oops! LeaveType not found.');
		}
	}

}
