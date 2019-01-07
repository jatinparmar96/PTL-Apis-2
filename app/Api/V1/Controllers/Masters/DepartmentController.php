<?php

namespace App\Api\V1\Controllers\Masters;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Model\Department;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\ValidationHttpException;

class DepartmentController extends Controller
{

	public function query()
	{
		$token = JWTAuth::getPayload()->toArray();
		$query = DB::table('departments as d')
					->join('companies as c', 'd.company_id', '=', 'c.id')
					->select('d.id', 'd.company_id', 'd.name', 'd.status')
					->addSelect(DB::raw('IF(d.status = "0", "Inactive", "Active") as status_type'))
					->where('d.company_id', '=', $token['company_id'] );

		return $query;
	}

	public function TableColumn()
    {         
        $TableColumn = array(
                       "name"=>"d.name",
                       "status"=>"d.status"
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
		// DB::enableQueryLog();
		$limit = 25;
        $query = $this->query();
        $query = $this->search($query);
        $query = $this->sort($query);
        $result = $query->paginate($limit);
        // dd(DB::getQueryLog());
        return $result;
	}

 	public function show($id)
 	{
 		try {
            $department = Department::findOrFail($id);
        } 
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! Department not found');
        }
 		return $department;
 	}

	public function full_list()
	{
        $query = $this->query();
        $query = $this->sort($query);
        $query = $query->where('d.status', '=', '1' );
        $result = $query->get();
        return response()->json([
        			'status'	=>	'success',
        			'status_code' => 200,
        			'message'	=>	'Department Full List',
        			'data'		=>	$result
        		]);
	}

	public function store(Request $request)
	{

			if($request->get('name') == 'mohit'){
				throw new ValidationHttpException([ 'name' => 'Category is missing from the request.']);
			}
	
    	$user = JWTAuth::parseToken()->toUser();
		return Department::create([
			
			"name"=>$request->get('name'),
			"status"=>$request->get('status'),
			
			"company_id"=>$user->company_id,
			"inserted_by_id"=>$user->id,
			"updated_by_id"=>$user->id
			]);
	}

    public function update(Request $request, $id)
    {
    	$user = JWTAuth::parseToken()->toUser();
    	$department = Department::findOrFail($id);
    	$data = $request->all();
    	$data['updated_by_id'] = $user->id;
    	$department->update($data);
    	return $department;
    }

	public function destroy($id)
	{
		if(Department::destroy($id)){
			return response()->json([
				'status' => 'ok'
				]);
		}
		else{
			throw new NotFoundHttpException('Oops! Department not found.');
		}
	}

}
