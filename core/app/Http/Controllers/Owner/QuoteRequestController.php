<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Lib\GoogleAuthenticator;
use App\Models\AdminNotification;
use App\Models\GeneralSetting;
use App\Models\Property;
use App\Models\Room;
use App\Models\OwnerDocuments;
use App\Models\Suppliers;
use App\Models\RoomCategory;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class QuoteRequestController extends Controller
{

     /*
     * Owner Operations
     */

    public function quoteRequest()
    {
        $pageTitle = "Quote Requests";
        $owner = Auth::guard('owner')->user();
        $ownerId = auth()->guard('owner')->user()->id;
        $OwnerDocuments = OwnerDocuments::where('owner_id', $ownerId)->get();
        $supplierRequests = Suppliers::where('owner_id', $ownerId)->get();
        $supplierApproved = Suppliers::where('owner_id', $ownerId)->where('approval_status', 'Active')->get();
        $supplierDeactivated = Suppliers::where('owner_id', $ownerId)->where('approval_status', 'Deactivate')->get();
        // $OwnerDocuments = OwnerDocuments::get();
        return view('owner.quote_request.quote', compact('pageTitle', 'owner','OwnerDocuments','supplierRequests','supplierApproved','supplierDeactivated'));
    }


    public function supplierRequest(Request $request)
    {
        $owner = auth()->guard('owner')->user()->id;
        if(empty($request->calltypedocument)){
            $supplierRequest =new Suppliers;

            $supplierRequest->owner_id = $owner;
            $supplierRequest->request_title = auth()->guard('owner')->user()->firstname.' '.auth()->guard('owner')->user()->lastname;
            $supplierRequest->approval_status = "Pending";
            $supplierRequest->request_message = $request->input('coverletter');
            $supplierRequest->save();
            $notify[] = ['success', 'Supplier Request Send Successfully.'];
        }else{

        if($request->hasFile('documentlink'))
        {

            $file = $request->file('documentlink');
            $extention = $file->getClientOriginalExtension();
            $filename = '_'.time().'.'.$extention;
            $folder='owner-files/'.$owner;
            $file->move('uploads/'.$folder, $filename);
            $OwnerDocument = new OwnerDocuments;
            // Assign values to the model's attributes using the request data
            $OwnerDocument->owner_id = $owner;
            $OwnerDocument->document_title = $request->input('documenttype');
            $OwnerDocument->document_name = $filename;
            // Save the model to the database
            $OwnerDocument->save();

            $notify[] = ['success', 'Document uploaded sucessfully'];
        }else{
            $notify[] = ['error','Document upload failed'];
        }
    }
    return back()->withNotify($notify);
    }
}
