<?php

namespace App\Api\V1\Controllers\Masters;

use App\Api\V1\Controllers\Authentication\TokenController;
use App\Http\Controllers\Controller;
use App\Model\BillOfMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillOfMaterialMasterController extends Controller
{
    public function form(Request $request)
    {
        $id = $request->get('id');
        $company_id = TokenController::getCompanyId();
        $user = TokenController::getUser();
        if ($id === 'new') {
            $bom = new BillOfMaterial();
            $bom->company_id = $company_id;
            $bom->created_by_id = $user->id;
        } else {
            $bom = BillOfMaterial::findOrFail($id);
        }
        $bom->item_name = $request->get('item_name');
        $bom->item_code = $request->get('item_code');
        $bom->quantity = $request->get('quantity');
        $bom->bom_name = $request->get('bom_name');
        $bom->bom_number = $request->get('bom_number');
        $bom->bom_date = $request->get('bom_date');
        $bom->revision_number = $request->get('revision_number');
        $bom->revision_date = $request->get('revision_date');
        $bom->uom = $request->get('uom');
        $processes = $request->get('processes');
        foreach ($processes as $process) {
            if ($process['id'] === 'new') {
                $bom_process = new BomProcess();
            } else {

            }
        }
    }

    public function query()
    {
        $current_company_id = TokenController::getCompanyId();
        $query = DB::table('bill_of_materials as bom')
            ->select('bom.item_name', 'bom.item_code', 'bom.quantity', 'bom.bom_number', 'bom.bom_date', 'bom.revision_number', 'bom.revision_date')
            ->where('company_id',$current_company_id);
        return $query;
    }

    public function index()
    {

    }

    public function full_list()
    {

    }
}
