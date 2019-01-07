<?php

namespace App\Api\V1\Controllers\Masters;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\RawProduct;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Api\V1\Controllers\Authentication\TokenController;
use Illuminate\Support\Facades\DB;


class RawProductController extends Controller
{
    public function form(Request $request)
    { 
        $status = true;
        $id = $request->get('id');
        $user = TokenController::getUser();
        $current_company_id = TokenController::getCompanyId();
       
        if($id == 'new')
        {
            $count = RawProduct::where('product_name',$request->get('product_name'))
                                ->where('company_id',$current_company_id)
                                ->count();
            $code = RawProduct::where('product_code',$request->get('product_code'))
                                ->where('company_id',$current_company_id)
                                ->count();
            if($count>0)
            {
                $status = false;
                $message = 'Please fill the form correctly!!!';
                $error['product_name'] = 'Product with this name Already Exists!!';
            }
            if($code>0)
            {
                $status = false;
                $message = 'Please fill the form correctly!!!';
                $error['product_code'] = 'Product with this code Already Exists!!';
            }
            else
            {
                $raw = new RawProduct();
                $raw->company_id = $current_company_id;
                $message = "Record added Successfully";
                $raw->created_by_id = $user->id;
            }
            
        }
        else
        {
            $message = 'Record Updated Successfully';
            $raw = RawProduct::findOrFail($id);
        }
        if($status)
        {
            $raw->product_name = $request->get('product_name');
            $raw->product_display_name = $request->get('product_display_name');
            $raw->product_code = $request->get('product_code');
            $raw->product_uom = $request->get('product_uom');
            $raw->product_conv_uom = $request->get('product_conv_uom');
            $raw->product_conv_factor = $request->get('product_conv_factor');
            $raw->product_batch_type = ($request->get('product_batch_type')=='Yes'? true:false);
            $raw->product_trade_name = $request->get('product_trade_name');
            $raw->product_stock_ledger = ($request->get('product_stock_ledger')=='Yes'? true:false);
            $raw->product_rate_pick = $request->get('product_rate_pick');
            $raw->product_purchase_rate = $request->get('product_purchase_rate');
            $raw->product_mrp_rate = $request->get('product_mrp_rate');
            $raw->product_sales_rate = $request->get('product_sales_rate');
            $raw->product_gst_rate = $request->get('product_gst_rate');
            $raw->product_max_level = $request->get('product_max_level');
            $raw->product_min_level = $request->get('product_min_level');
            $raw->product_description = $request->get('product_description');
            $raw->product_store_location = $request->get('product_store_location');
            $raw->product_category = $request->get('product_category');
            $raw->product_hsn = $request->get('product_hsn');
            $raw->product_type = $request->get('product_type');
            $raw->updated_by_id = $user->id;
            try
            {
                $raw->save();
            }
            catch(\Exception $e)
            {
                $status = false;
                $message = 'Something is wrong. Kindly Contact Admin'.$e;
            }
            $raw = $this->query()->where('rp.id',$raw->id)->first();
            return response()->json([
                'status' => $status,
                'data' => $raw,
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
        $query = DB::table('raw_products as rp')
                ->leftJoin('unit_of_measurements as uom1','rp.product_uom','uom1.id')
                ->leftJoin('unit_of_measurements as uom2','rp.product_conv_uom','uom2.id')
                ->leftJoin('taxes as t','rp.product_gst_rate','t.id')
                ->leftJoin('product_categories as pc','rp.product_category','pc.id')
                ->select(
                'rp.id','rp.product_name','rp.product_display_name','rp.product_code',
                'rp.product_conv_factor','rp.product_batch_type',
                'rp.product_stock_ledger','rp.product_rate_pick',
                'rp.product_purchase_rate','rp.product_mrp_rate',
                'rp.product_sales_rate','rp.product_max_level','rp.product_min_level',
                'rp.product_hsn','rp.product_description','rp.product_trade_name',
                'rp.product_store_location','rp.product_type'
                )
                ->addSelect(DB::raw('IF(rp.product_batch_type = 1,"Yes","No") as product_batch_type'))
                ->addSelect(DB::raw('IF(rp.product_stock_ledger = 1,"Yes","No") as product_stock_ledger'))
                ->addSelect('uom1.id as product_uom','uom1.unit_name as product_uom_name')
                ->addSelect('uom2.id as product_conv_uom','uom2.unit_name as product_conv_uom_name')
                ->addSelect('t.id as product_gst_rate','t.tax_name','t.tax_rate')
                ->addSelect('pc.id as product_category','pc.product_category_name')
                ->where('rp.company_id',$current_company_id);
        return $query;
    }

    public function TableColumn()
    {         
        $TableColumn = array(
                       "id"=>"rp.id",
                       "product_name"=>"rp.product_name",                       
                       "product_display_name"=>"rp.product_display_name",
                       "product_code"=>"rp.product_code",                       
                       "conv_factor"=>"rp.conv_factor",
                       "batch_type"=>"rp.batch_type",
                       "stock_ledger"=>"rp.stock_ledger",
                       "product_rate_pick"=>"rp.product_rate_pick",
                       "product_purchase_rate"=>"rp.product_purchase_rate",
                       "mrp_rate"=>"rp.mrp_rate",
                       "sales_rate"=>"rp.sales_rate",
                       "gst_rate"=>"rp.gst_rate",
                       "max_level"=>"rp.max_level",
                       "min_level"=>"rp.min_level",
                       "description"=>"rp.description",
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
           $query = $query->orderBy('rp.product_display_name', 'ASC');
           
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
                'message' => 'Raw Product List',
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
                'message' => 'Raw Product Full List',
                'data' => $result
                ]);
    }
    public function show(Request $request,$id)
    {
        $query = $this->query();
        $query = $this->search($query);
        $query = $this->sort($query);
        $result = $query->where('rp.id',$id)->first();
        return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Raw Product Full List',
                'data' => $result
                ]);
    }
}
