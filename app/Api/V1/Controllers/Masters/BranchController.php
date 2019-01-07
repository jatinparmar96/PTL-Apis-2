<?php

namespace App\Api\V1\Controllers\Masters;

use App\Api\V1\Controllers\Authentication\TokenController;
use App\Http\Controllers\Controller;
use App\Model\Bank;
use App\Model\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class BranchController extends Controller
{
    public function form(Request $request,$company_id=0)
    {
        $status = true;
        $current_company_id = $company_id;
        if($company_id === 0)
        {
           $current_company_id = TokenController::getCompanyId();
        }
        $id = $request->get('id');
        if($id === 'new')
        {
            $count = Branch::where('name',$request->get('branch_name'))
                            ->where('company_id',$current_company_id)
                            ->count();
            if($count>0)
            {
              
               $status = false;
               $message = 'Kindly Fill up the form Correctly !!';
               $error['branch_name']= 'Branch Name already Exits';
            }
            else
            {
                $message = 'New Branch created successfully!!';
                $branch = new Branch();
                $branch->company_id = $current_company_id;
                $branch->created_by_id =TokenController::getUser()->id;
            }
           
        }
        else
        {
            $message = 'Branch updated successfully!!';
            $branch = Branch::findOrFail($id);
        }
        if($status)
        {
            if($company_id !== 0)
            {
                $branch->name = 'Head Office';
            }
            else
            {
                $branch->name = $request->get('branch_name');
            }
            $branch->code = $request->get('branch_code');
            $branch->gst_number = $request->get('branch_gst_number');  
            $branch->is_godown = ($request->get('branch_godown')=='Yes'? true:false); 
            $branch->updated_by_id = TokenController::getUser()->id;
            try
            {
                $branch->save();
            }
            catch(\Exception $e)
            {
                $status = false;
                $message='Something is wrong'. $e;
            }
            if ($company_id != 0) 
            {
                return $branch; 
            }
            else
            {
                try
                {
                    $bank = Bank::findOrFail($request->get('branch_bank_id'));
                    $bank->type = 'Branch';
                    $bank->type_id = $branch->id;
                    $bank->save();
                    AddressController::storeAddress($request,'branch_','Branch',$branch->id);
                    $branch = $this->query()->where('b.id',$branch->id)->first();
                }
                catch(\Exception $e)
                {
                    $status = false;
                    $message='Something is wrong'.$e;
                }
                return response()->json([
                    'status'=>$status,
                    'data'=>$branch,
                    'message'=>$message
                ]);
           
            }
        }
        else
        {
            return response()->json([
                'status'=>$status,
                'message'=>$message,
                'error'=>$error
            ]);
        }
    }
  

    public function query()
    {
        $current_company_id  = TokenController::getCompanyId();
       
        $query = DB::table('company_branches as b')
                    ->leftjoin('addresses as a','b.id','a.type_id')
                    ->leftjoin('banks as ba','b.id','ba.type_id')
                    ->select(
                    'b.id','b.name as branch_name','b.gst_number as branch_gst_number','b.code as branch_code','b.is_godown'
                    )
                    ->addSelect(DB::raw("IF(b.is_godown = 1,'Yes','No') as branch_godown"))
					->addSelect('a.id as address_id','a.building as branch_address_building','a.road_name as branch_address_road_name','a.landmark as branch_address_landmark','a.country as branch_address_country','a.city as branch_address_city','a.state as branch_address_state','a.pincode as branch_address_pincode')
                    ->addSelect('ba.id as branch_bank_id','ba.bank_name','ba.account_name','ba.account_no','ba.ifsc_code')
                    ->where('b.company_id',$current_company_id);
        return $query;
    }

    public function TableColumn()
    {         
        $TableColumn = array(
                       "id"=>"b.id",
                       "name"=>"b.name",
                       "gst_number"=>"b.gst_number",
                       "code"=>"b.code",
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
           $query = $query->orderBy('b.name', 'ASC');
           
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
                'message' => 'Branch List',
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
                'message' => 'Branch Full List',
                'data' => $result
                ]);
    }
    public function show(Request $request,$id)
    {
        $query = $this->query();
        $query = $this->search($query);
        $query = $this->sort($query);
        $result = $query->where('b.id',$id)->first();
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Branch',
            'data' => $result
            ]);
    }
}
