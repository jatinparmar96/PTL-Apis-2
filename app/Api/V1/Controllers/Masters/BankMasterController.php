<?php

namespace App\Api\V1\Controllers\Masters;

use App\Api\V1\Controllers\Authentication\TokenController;
use App\Http\Controllers\Controller;
use App\Model\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankMasterController extends Controller
{
    public function form(Request $request, $type = '', $type_id = 0, $company_id = 0)
    {
        // If type and Type_Id is non zero Function is called from wizard
        // Else the request is coming from a form in that case return a json response
        $status = true;
        $user = TokenController::getUser();
        $current_company_id = $company_id;
        if ($company_id === 0) {
            $current_company_id = TokenController::getCompanyId();
        }
        $id = $request->get('id');
        if ($id == 'new') {
            $count = Bank::where('bank_name', $request->get('bank_name'))
                ->where('account_name', $request->get('account_name'))
                ->where('company_id', $current_company_id)
                ->count();
            if ($count > 0) {
                $status = false;
                $message = 'Kindly Fill up the form Correctly !!';
                $error['bank'] = 'Bank Name with Same account name already Exits!!!';
            } else {
                $bank = new Bank();
                $bank->created_by_id = $user->id;
                $bank->company_id = $current_company_id;
                $message = 'Bank Created Successfully';
            }
        } else {
            $bank = Bank::findOrFail($id);
            $message = 'Bank updated Successfully';
        }
        if ($status) {
            if ($bank->type == '' && $bank->type_id == '') {
                if ($type === '' && $type_id === 0) {
                    $bank->type = 'Not Defined';
                    $bank->type_id = $type_id;
                } else {
                    $bank->type = $type;
                    $bank->type_id = $type_id;
                }
            }
            $bank->account_name = $request->get('account_name');
            $bank->bank_name = $request->get('bank_name');
            $bank->account_no = $request->get('account_no');
            $bank->ifsc_code = $request->get('ifsc_code');
            $bank->branch = $request->get('branch');
            $bank->updated_by_id = $user->id;
            try {
                $bank->save();
            } catch (\Exception $e) {
                $status = false;
                $message = 'Something is wrong. Kindly Contact Admin' . $e;
            }
            return response()->json([
                'status' => $status,
                'data' => $bank,
                'message' => $message
            ]);
        } else {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'error' => $error
            ]);
        }


    }

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
            'message' => 'Bank List',
            'data' => $result
        ]);
    }

    public function query()
    {
        $current_company_id = TokenController::getCompanyId();
        $banks = Bank::where('company_id', $current_company_id)->get();
        $query = DB::table('banks as ba')
            ->select('ba.id', 'ba.type', 'ba.type_id', 'ba.account_name', 'ba.bank_name', 'ba.account_no', 'ba.ifsc_code',
                'ba.branch')
            ->where('ba.company_id', $current_company_id);
        return $query;
    }

    public function search($query)
    {
        $search = \Request::get('search');
        if (!empty($search)) {
            $TableColumn = $this->TableColumn();
            foreach ($search as $key => $searchvalue) {
                $query = $query->Where($TableColumn[$key], 'LIKE', '%' . $searchvalue . '%');
            }
        }

        return $query;
    }

    public function TableColumn()
    {
        $TableColumn = array(
            "id" => "ba.id",
            "type" => "ba.type ",
            "type_id" => "ba.type_id",
            "account_name" => "ba.account_name",
            "bank_name" => "ba.bank_name",
            "account_no" => "ba.account_no",
            "ifsc_code" => "ba.ifsc_code",
            "branch" => "ba.branch",
        );
        return $TableColumn;
    }

    //use Helpers;

    public function sort($query)
    {
        $sort = \Request::get('sort');
        if (!empty($sort)) {
            $TableColumn = $this->TableColumn();

            $query = $query->orderBy($TableColumn[key($sort)], $sort[key($sort)]);
        } else
            $query = $query->orderBy('ba.account_name', 'ASC');
        return $query;
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
            'message' => 'Bank Full List',
            'data' => $result
        ]);
    }

    public function show($id)
    {
        $query = $this->query();
        $query = $this->search($query);
        $query = $this->sort($query);
        $result = $query->where('ba.id', $id)->first();
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Bank',
            'data' => $result
        ]);
    }
}
