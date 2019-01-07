<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\MenuUserlevel;
use Dingo\Api\Routing\Helpers;

class MenuUserlevelController extends Controller
{

    //use Helpers;
	public function index()
	{
		$limit = 10;
		return MenuUserlevel::paginate($limit);
	}

	public function store(Request $request)
	{
    	$user = JWTAuth::parseToken()->toUser();
		return MenuUserlevel::create([
			"userlevel_id"=>$request->get('userlevel_id'),
			"menu_id"=>$request->get('menu_id'),
			"is_active"=>$request->get('is_active'),
			"list"=>$request->get('list'),
			"create"=>$request->get('create'),
			"update"=>$request->get('update'),
			"delete"=>$request->get('delete'),
			"inserted_by_id"=>$user->id,
			"updated_by_id"=>$user->id
			]);
	}

 	public function show($id)
 	{
 		try {
            $menuUserlevel = MenuUserlevel::findOrFail($id);
        } 
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! MenuUserlevel not found');
        }
 		return $menuUserlevel;
 	}

    public function update(Request $request, $id)
    {
    	$user = JWTAuth::parseToken()->toUser();
    	$menuUserlevel = MenuUserlevel::findOrFail($id);
    	$data = $request->all();
    	$data['updated_by_id'] = $user->id;
    	$menuUserlevel->update($data);
    	return $menuUserlevel;
    }

	public function destroy($id)
	{
		if(MenuUserlevel::destroy($id)){
			return response()
			->json([
				'status' => 'ok'
				]);
		}
		else{
			throw new NotFoundHttpException('Oops! MenuUserlevel not found.');
		}
	}

}
