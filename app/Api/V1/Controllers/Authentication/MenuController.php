<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Menu;
use Dingo\Api\Routing\Helpers;

class MenuController extends Controller
{

    //use Helpers;
	public function index()
	{
		$limit = 10;
		return Menu::paginate($limit);
	}

	public function store(Request $request)
	{
    	$user = JWTAuth::parseToken()->toUser();
		return Menu::create([
			"caption"=>$request->get('caption'),
			"level"=>$request->get('level'),
			"class_name"=>$request->get('class_name'),
			"link"=>$request->get('link'),
			"parent"=>$request->get('parent'),
			"sort"=>$request->get('sort'),
			"inserted_by_id"=>$user->id,
			"updated_by_id"=>$user->id
			]);
	}

 	public function show($id)
 	{
 		try {
            $menu = Menu::findOrFail($id);
        } 
        catch (\Exception $e) {
            throw new NotFoundHttpException('Opps! Menu not found');
        }
 		return $menu;
 	}

    public function update(Request $request, $id)
    {
    	$user = JWTAuth::parseToken()->toUser();
    	$menu = Menu::findOrFail($id);
    	$data = $request->all();
    	$data['updated_by_id'] = $user->id;
    	$menu->update($data);
    	return $menu;
    }

	public function destroy($id)
	{
		if(Menu::destroy($id)){
			return response()
			->json([
				'status' => 'ok'
				]);
		}
		else{
			throw new NotFoundHttpException('Oops! Menu not found.');
		}
	}

}
