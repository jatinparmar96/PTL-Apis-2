<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Logs;
use Dingo\Api\Routing\Helpers;

class LogsController extends Controller
{

    //use Helpers;
	public function index()
	{
		$user = JWTAuth::parseToken()->toUser();
		$limit = 10;
		// return Logs::paginate($limit);
		return DB::table('logs as l')
					->join('users as u', 'l.user_id', '=', 'u.id')
					->join('companies as c', 'u.company_id', '=', 'c.id')
					->select('l.ipaddress', 'l.user_id', 'l.module', 'l.task', 'l.note', 'l.logdate', 'l.old_data', 'l.new_data')
					->addSelect('u.fullname')
					->where('u.company_id', '=', $user->company_id )
					->paginate($limit);
	}

	public function store(Request $request)
	{
    	$user = JWTAuth::parseToken()->toUser();
		return Logs::create([
			"ipaddress"=>$request->get('ipaddress'),
			"user_id"=>$request->get('user_id'),
			"module"=>$request->get('module'),
			"task"=>$request->get('task'),
			"note"=>$request->get('note'),
			"logdate"=>$request->get('logdate'),
			"old_data"=>$request->get('old_data'),
			"new_data"=>$request->get('new_data'),
			"inserted_by_id"=>$user->id,
			"updated_by_id"=>$user->id
			]);
	}

 	public function show($id)
 	{
 		try {
            $logs = Logs::findOrFail($id);
        } 
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! Logs not found');
        }
 		return $logs;
 	}

    public function update(Request $request, $id)
    {
    	$user = JWTAuth::parseToken()->toUser();
    	$logs = Logs::findOrFail($id);
    	$data = $request->all();
    	$data['updated_by_id'] = $user->id;
    	$logs->update($data);
    	return $logs;
    }

	public function destroy($id)
	{
		if(Logs::destroy($id)){
			return response()
			->json([
				'status' => 'ok'
				]);
		}
		else{
			throw new NotFoundHttpException('Oops! Logs not found.');
		}
	}

}
