<?php

namespace App\Api\V1\Controllers\Masters;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\RawProduct;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductController extends Controller
{
    public function storeFinishedProduct(Request $request)
    {
        $token = JWTAuth::decode(JWTAuth::getToken());
        $current_company_id = $token['company_id']['id'];
        $raw = new RawProduct();
        $raw->product_name = Input::get('raw_product_name');
        $raw->product_display_name = Input::get('raw_product_display_name');
        $raw->product_code = Input::get('raw_product__code');
        $raw->product_uom = Input::get('raw_product_uom');
        $raw->product_conv_uom = Input::get('raw_product_conv_uom');
        $raw->conv_factor = Input::get('raw_product_conv_factor');
        $raw->batch_type = Input::get('raw_product_batch_type');
        
    }
    public function storeRawProduct(Request $request)
    {
      
        $token = JWTAuth::decode(JWTAuth::getToken());
        $current_company_id = $token['company_id']['id'];
        $raw = new RawProduct();
        $raw->company_id = $current_company_id;
        $raw->product_name = Input::get('raw_product_name');
        $raw->product_display_name = Input::get('raw_product_display_name');
        $raw->product_code = Input::get('raw_product_code');
        $raw->product_uom = Input::get('raw_product_uom');
        $raw->product_conv_uom = Input::get('raw_product_conv_uom');
        $raw->conv_factor = Input::get('raw_product_conv_factor');
        $raw->batch_type = Input::get('raw_product_batch_type');
        $raw->stock_ledger = Input::get('raw_product_maintain_stock_ledger');
        $raw->store_location = Input::get('raw_product_store_location');
        $raw->opening_stock = Input::get('raw_product_opening_stock');
        $raw->opening_amount = Input::get('raw_product_opening_amount');
        $raw->product_rate_pick = Input::get('raw_product_rate_pick_from');
        $raw->product_purchase_rate = Input::get('raw_product_purchase_rate');
        $raw->mrp_rate = Input::get('raw_product_mrp_rate');
        $raw->sales_rate = Input::get('raw_product_sales_rate');
        $raw->gst_rate = Input::get('raw_product_gst_slot');
        $raw->max_level = Input::get('raw_product_max_level');
        $raw->min_level = Input::get('raw_product_min_level');
        $raw->reorder_level = Input::get('raw_product_reorder_level');
        $raw->description = Input::get('raw_product_description');
        try{
            $raw->save();
            return response()
                ->json([
                    "status"=>true
                ]);
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }

    }
    public function getRawProducts()
    {
        $token = JWTAuth::getPayload()->toArray();
        $user = JWTAuth::parseToken()->toUser();
        $current_company_id= $token['company_id']['id'];
        $raw_products = RawProduct::where('company_id',$current_company_id)->get();
        return response()
                ->json([
                    'raw_products'=>$raw_products,
                    'status'=>true
                ]);
    }
}
