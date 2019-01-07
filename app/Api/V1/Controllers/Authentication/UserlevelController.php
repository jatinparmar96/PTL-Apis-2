<?php

namespace App\Api\V1\Controllers\Authentication;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Model\Userlevel;
use Dingo\Api\Routing\Helpers;

class UserlevelController extends Controller
{

    //use Helpers;
	public function index()
	{
		$limit = 10;
		return Userlevel::Where('is_active', '=', 'Yes')->paginate($limit);
	}

	public function store(Request $request)
	{
    	$user = JWTAuth::parseToken()->toUser();
		return Userlevel::create([
			"name"=>$request->get('name'),
			"alias"=>$request->get('alias'),
			"is_active"=>$request->get('is_active'),
			"inserted_by_id"=>$user->id,
			"updated_by_id"=>$user->id
			]);
	}

 	public function show($id)
 	{
 		try {
            $userlevel = Userlevel::findOrFail($id);
        } 
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! Userlevel not found');
        }
 		return $userlevel;
 	}

    public function update(Request $request, $id)
    {
    	$user = JWTAuth::parseToken()->toUser();
    	$userlevel = Userlevel::findOrFail($id);
    	$data = $request->all();
    	$data['updated_by_id'] = $user->id;
    	$userlevel->update($data);
    	return $userlevel;
    }

	public function destroy($id)
	{
		if(Userlevel::destroy($id)){
			return response()
			->json([
				'status' => 'ok'
				]);
		}
		else{
			throw new NotFoundHttpException('Oops! Userlevel not found.');
		}
	}

}
