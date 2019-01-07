<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Attachment;
use Dingo\Api\Routing\Helpers;

class AttachmentController extends Controller
{

    //use Helpers;
	public function index()
	{
		$url = url('/');
		$user = JWTAuth::parseToken()->toUser();
		$limit = 10;
		// return Attachment::paginate($limit);
		return DB::table('attachments as a')
					->join('clients as cl', 'a.module_id', '=', 'cl.id')
					->join('companies as c', 'cl.company_id', '=', 'c.id')
					->select('a.id', 'a.attachment', 'a.comments')
					->addSelect(DB::raw("CONCAT('$url/', a.attachment) as image"))
					->addSelect('cl.name')
					->addSelect('cl.name as company_name')
					->where('cl.company_id', '=', $user->company_id )
					->paginate($limit);
	}

	public function store(Request $request)
	{
		$user = JWTAuth::parseToken()->toUser();

		$image = $request->file('image');
		$ext = $image->getClientOriginalExtension();
		$destinationPath = public_path('/data');
		$image_name = time().'.'.$ext;
		$image->move($destinationPath, $image_name);
		
		return Attachment::create([
			"module"=>$request->get('module'),
			"module_id"=>$request->get('module_id'),
			"attachment"=>$image_name,
			"comments"=>$request->get('comments'),
			
			"inserted_by_id"=>$user->id,
			"updated_by_id"=>$user->id
			]);
	}

 	public function show($id)
 	{
 		try {
            $Attachment = Attachment::findOrFail($id);
        } 
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! Attachment not found');
        }
 		return $Attachment;
 	}

 	public function attachment_by_user($id)
	{
		$user = JWTAuth::parseToken()->toUser();
		
		$data = DB::table('attachments as a')
					->join('clients as cl', 'a.module_id', '=', 'cl.id')
					->join('companies as c', 'cl.company_id', '=', 'c.id')
					->select('a.id', 'a.attachment', 'a.comments', 'a.module_id')
					->addSelect( 'cl.name')
					->addSelect('cl.name as company_name')
					->where('cl.company_id', '=', $user->company_id )
					->where('a.module_id', '=', $id )
					->where('a.module', '=', 'clients' )
					->get();

			return response()
			->json([
				'data' => $data
				]);
	}

	public function attachment_by_task($id)
	{
		$user = JWTAuth::parseToken()->toUser();
		$url = url('/');
		$data = DB::table('attachments as a')
					->leftjoin('tasks as t', 'a.module_id', '=', 't.id')
					->leftjoin('users as u', 't.user_id', '=', 'u.id')
					->join('companies as c', 'u.company_id', '=', 'c.id')

					->select('a.id', 'a.comments', 'a.module_id')
					->addSelect(DB::raw("CONCAT('$url/data/', a.attachment) as attachment"))
					
					->where('u.company_id', '=', $user->company_id )
					->where('a.module_id', '=', $id )
					->where('a.module', '=', 'tasks' )
					->get();
		return response()
			->json([
				'data' => $data
				]);
	}

    public function update(Request $request, $id)
    {
    	$test = $request->all();
    	dd($test);

    	$image = $request->file('image');

    	$user = JWTAuth::parseToken()->toUser();
    	$Attachment = Attachment::findOrFail($id);
    	$data = $request->all();
    	$data['updated_by_id'] = $user->id;
    	$Attachment->update($data);
    	return $Attachment;
    }

	public function destroy($id)
	{
		if(Attachment::destroy($id)){
			return response()
			->json([
				'status' => 'ok'
				]);
		}
		else{
			throw new NotFoundHttpException('Oops! Attachment not found.');
		}
	}

}
