<?php

namespace App\Api\V1\Controllers\Masters;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\ProductCategory;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Api\V1\Controllers\Authentication\TokenController;
use Illuminate\Support\Facades\DB;


class ProductCategoryController extends Controller
{
    public function form(Request $request)
    {
        $status = true;
        $id = $request->get('id');
        $user = TokenController::getUser();
        $current_company_id = TokenController::getCompanyId();
        if($id == 'new')
        {
            $count = ProductCategory::where('product_category_name',$request->get('product_category_name'))
                                ->where('company_id',$current_company_id)
                                ->count();
            if($count>0)
            {
                $status = false;
                $message = 'Please fill the form correctly!!!';
                $error['product_category_name'] = 'Product Category with this name Already Exists';
            }
            else
            {
                $category = new ProductCategory();
                $category->company_id = $current_company_id;
                $message = "Record added Successfully";
                $category->created_by_id = $user->id;
            }
            
        }
        else
        {
            $message = 'Record Updated Successfully';
            $category = ProductCategory::findOrFail($id);
        }
        if($status)
        {
            $category->product_category_name = $request->get('product_category_name');
            $category->updated_by_id = $user->id;
            try
            {
                $category->save();
            }
            catch(\Exception $e)
            {
                $status = false;
                $message = 'Something is wrong. Kindly Contact Admin'.$e;
            }
            $category = $this->query()->where('id',$category->id)->first();
            return response()->json([
                'status' => $status,
                'data' => $category,
                'message'=>$message
                ]);
        }
        else
        {
            return response()->json([
                'status' => $status,                
                'message'=>$message,
                'error' => $error,
                ]);
        }
       

    }

    public function query()
    {

        $current_company_id = TokenController::getCompanyId();
        $query = DB::table('product_categories as pc')
                ->select(
                'pc.id','pc.product_category_name'
                )
                ->where('pc.company_id',$current_company_id);
        return $query;
    }

    public function TableColumn()
    {         
        $TableColumn = array(
                       "id"=>"pc.id",
                       "product_category_name"=>"pc.product_category_name"
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
        else
           $query = $query->orderBy('pc.product_category_name', 'ASC');
           
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
                if($searchvalue !== '') 
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
                'status' => true,
                'status_code' => 200,
                'message' => 'Product Category List',
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
                'status' => true,
                'status_code' => 200,
                'message' => 'Product Category Full List',
                'data' => $result
                ]);
    }

    public function show($id)
    {
        $query = $this->query();
        $query = $this->search($query);
        $query = $this->sort($query);
        $result = $query->where('pc.id',$id)->first();
        return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Product Category List',
                'data' => $result
                ]);
    }
}
