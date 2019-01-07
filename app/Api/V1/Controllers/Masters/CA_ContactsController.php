<?php

namespace App\Api\V1\Controllers\Masters;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Bank;
use App\Model\Address;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Model\CA_Contact;
use App\Api\V1\Controllers\Authentication\TokenController;

class CA_ContactsController extends Controller{

    public static function form(Request $request,$account_id)
    {
        $id = $request->get('contact_id');
        if($id === 'new')
        {
            $contact = new CA_Contact();
            $contact->created_by_id = TokenController::getUser()->id;
            $contact->ca_company_id= $account_id;
        }
        else
        {
            $contact = CA_Contact::findOrFail($id);
        }
       
        $contact->ca_contact_first_name  = $request->get('ca_contact_first_name');
        $contact->ca_contact_last_name = $request->get('ca_contact_last_name');
        $contact->ca_contact_mobile_number  = $request->get('ca_contact_mobile_number');
        $contact->ca_contact_email = $request->get('ca_contact_email'); 
        $contact->ca_contact_designation  = $request->get('ca_contact_designation');
        $contact->ca_contact_branch  = $request->get('ca_contact_branch'); 
        $contact->updated_by_id = TokenController::getUser()->id;
        try
        {
            $contact->save();
        }
        catch(\Exception $e )
        {
            throw new \Exception($e);
        }
        return;
    }
}