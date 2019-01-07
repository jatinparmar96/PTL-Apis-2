<?php

namespace App\Api\V1\Controllers\Masters;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Model\Godown;
use App\Model\Address;
use Illuminate\Support\Facades\DB;
use App\Api\V1\Controllers\Authentication\TokenController;


class GodownMasterController extends Controller
{
    public function form(Request $request)
    {
        $status = true;
        $id = $request->get('id');
        $user = TokenController::getUser();
        $current_company_id= TokenController::getCompanyId();
        if($id === 'new')
        {
            $count = Godown::where('godown_name',$request->get('godown_name'))
                            ->where('company_id',$current_company_id)
                            ->count();
            if($count>0)
            {
                $status = false;
                $message = 'Kindly fill up form Correctly !!';
                $error['godown_name'] = 'Godown Name already exist !!';
            }    
            else
            {
                $godown = new Godown();
                $message = 'Record Added Successfully';
                $godown->created_by_id = $user->id;
            }
        }
        else
        {
            $message = 'Record Updated Successfully';
            $godown = Godown::findOrFail($id);
        }
        if($status)
        {
            $godown->company_id = $current_company_id;
            $godown->godown_name = $request->get('godown_name');
            $godown->godown_code = $request->get('godown_code');
            $godown->updated_by_id = $user->id;
            
            try
            {
                $godown->save();
                AddressController::storeAddress($request,'godown_','Godown',$godown->id);
            }
            catch(\Exception $e)
            {
                $status = false;
                $message = 'Something is wrong';
            }
            $godown = $this->query()->where('g.id',$godown->id)->first();
            return response()->json([
                'status' => $status,
                'data' => $godown,
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
        $company_id = TokenController::getCompanyId();
        $query = DB::table('godowns as g')
					->leftJoin('addresses as a', 'a.type_id', '=', 'g.id')
					->select('g.id', 'g.godown_name', 'g.godown_code')
					->addSelect('a.block_no','a.road_name','a.landmark','a.country','a.city','a.state','a.pincode')
                    ->where('g.company_id','=',$company_id);
                    
        return $query;
    }

    public function TableColumn()
    {         
        $TableColumn = array(
                       "id"=>"g.id",
                       "godown_name"=>"g.gowdown_name",                       
                       "godown_code"=>"g.godown_code"
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
           $query = $query->orderBy('g.godown_name', 'ASC');
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
                'message' => 'Godown List',
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
                'message' => 'Godown Full List',
                'data' => $result
                ]);
    }

}
