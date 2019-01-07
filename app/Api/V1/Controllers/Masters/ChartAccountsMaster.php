<?php

namespace App\Api\V1\Controllers\Masters;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use JWTAuth;
use App\Model\ChartOfAccount;
use App\Model\Address;
use Illuminate\Support\Facades\DB;
use App\Api\V1\Controllers\Authentication\TokenController;
use App\Model\CA_Contact;

class ChartAccountsMaster extends Controller
{
  public function form(Request $request)
  {
    $user = TokenController::getUser();
    $status = true;
    $current_company_id = TokenController::getCompanyId();
    $id = $request->get('id');
    if($id === 'new')
    {
        $count = ChartOfAccount::where('ca_company_name',$request->get('ca_company_name'))
                                ->where('company_id',$current_company_id)
                                ->count();
        $display = ChartOfAccount::where('ca_company_display_name',$request->get('ca_company_display_name'))
                                ->where('company_id',$current_company_id)
                                ->count();
        if($count>0)
        {  
            $status = false;
            $message = 'Kindly Fill up the form Correctly !!';
            $error['ca_company_name'] = 'Company Name already Exits';
        }
        if($display>0)
        {  
            $status = false;
            $message = 'Kindly Fill up the form Correctly !!';
            $error['ca_company_display_name'] = 'Company with same display name already Exits';
        }
        else
        {
            $account = new ChartOfAccount();
            $contact = new CA_Contact();
            $account->created_by_id = $user->id;
            $message = "Chart Of account record added Successfully !!";
            $account->company_id = $current_company_id;
        }
    }
    else
    {
        $account = ChartOfAccount::findOrFail($id);
        $message = "Record updated Successfully";
    }
   
    if($status)
    {
        $account->ca_company_name=$request->get('ca_company_name');
        $account->ca_company_display_name=$request->get('ca_company_display_name');
        $account->ca_category=$request->get('ca_category');
        $account->ca_code = $request->get('ca_code');
        $account->ca_opening_amount=$request->get('ca_opening_amount');
        $account->ca_opening_type=$request->get('ca_opening_type');
        $account->ca_website=$request->get('ca_website');
        $account->ca_pan=$request->get('ca_pan');
        $account->ca_gstn=$request->get('ca_gstn');
        $account->ca_tan=$request->get('ca_tan');
        $account->ca_date_opened = $request->get('ca_date_opened');
        $account->updated_by_id = $user->id;
        try
        {   
            $account->save();
            CA_ContactsController::form($request,$account->id);
            AddressController::storeAddress($request,'ca_','ChartOfAccounts',$account->id);  
        }
        catch(\Exception $e)
        {
            $status = false;
            $message= 'Something is wrong'.$e;
        }
        $account = $this->query()->where('ca.id',$account->id)->first();
        return response()->json([
            'status' => $status,
            'data' => $account,
            'message'=> $message
            ]);
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
        $current_company_id = TokenController::getCompanyId();
        $query = DB::table('chart_of_accounts as ca')
                    ->leftJoin('addresses as a','ca.id','a.type_id')
                    ->leftJoin('ca_contacts as co','ca.id','co.ca_company_id')
                    ->select(
                    'ca.id','ca.ca_company_name','ca.ca_company_display_name','ca.ca_category','ca.ca_code','ca.ca_opening_amount','ca.ca_opening_type','ca.ca_website','ca.ca_pan','ca.ca_gstn','ca.ca_tan','ca.ca_date_opened'
                    )
                    ->addSelect(
                    'co.id as contact_id','co.ca_contact_first_name','co.ca_contact_last_name','co.ca_contact_mobile_number','co.ca_contact_email','co.ca_contact_designation','co.ca_contact_branch'
                    )
					->addSelect('a.id as address_id','a.building as ca_address_building','a.road_name as ca_address_road_name','a.landmark as ca_address_landmark','a.pincode as ca_address_pincode','a.city as ca_address_city','a.state as ca_address_state','a.country as ca_address_country')
                    ->where('ca.company_id',$current_company_id);
        return $query;
  }

  public function TableColumn()
  {         
      $TableColumn = array(
                     "id"=>"ca.id",
                     "ca_company_name"=>"ca.ca_company_name",                       
                     "ca_company_display_name"=>"ca.ca_company_display_name",
                     "ca_category"=>"ca.ca_category",
                     "ca_code"=>"ca.ca_code",
                     "ca_opening_amount"=>"ca.ca_opening_amount",
                     "ca_opening_type"=>"ca.ca_opening_type",
                     "ca_first_name"=>"ca.ca_first_name",
                     "ca_last_name"=>"ca.ca_last_name",
                     "ca_mobile_number"=>"ca.ca_mobile_number",
                     "ca_email"=>"ca.ca_email",
                     "ca_website"=>"ca.ca_website",
                     "ca_designation"=>"ca.ca_designation",
                     "ca_branch"=>"ca.ca_branch",
                     "ca_pan"=>"ca.ca_pan",
                     "ca_gstn"=>"ca.ca_gstn",
                     "ca_tan"=>"ca.ca_tan",
                     "ca_date_opened"=>"ca.ca_date_opened",
                     "created_by_id"=>"ca.created_by_id",
                     "updated_by_id"=>"ca.updated_by_id",
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
        $query = $query->orderBy('ca.ca_company_name','ASC');
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
      $limit = 10;
      $query = $this->query();
      $query = $this->search($query);
      $query = $this->sort($query);
      $result = $query->paginate($limit);
      return response()->json([
              'status' => true,
              'status_code' => 200,
              'message' => 'Chart Of Account List',
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
              'message' => 'Chart Of Account Full List',
              'data' => $result
              ]);
  }
  public function show($id)
  {
      $query = $this->query();
      $query = $this->search($query);
      $query = $this->sort($query);
      $result = $query->where('ca.id',$id)->first();
      return response()->json([
              'status' => true,
              'status_code' => 200,
              'message' => 'Chart Of Account',
              'data' => $result
              ]);
  }

}
