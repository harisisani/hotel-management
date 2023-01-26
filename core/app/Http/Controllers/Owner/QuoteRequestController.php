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
use App\Models\QuoteRequests;
use App\Models\QuoteProposals;
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
use Illuminate\Support\Facades\DB;

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
        $emptyMessage = 'No quote requests found';
        // $QuoteRequests = QuoteRequests::whereDate('quote_deadline','>=',DB::raw('CURRENT_DATE'))->orderBy('created_at', 'desc')->get();
        $QuoteRequests = QuoteRequests::whereDate('quote_deadline','>=',DB::raw('CURRENT_DATE'))
            ->orderBy('created_at', 'desc')->get();
        $QuoteProposals = QuoteProposals::where('owner_id', $ownerId)->orderBy('created_at', 'asc')->get();
        $supplierApproved = Suppliers::where('owner_id', $ownerId)->where('approval_status', 'Active')->get();
        $supplierDeactivated = Suppliers::where('owner_id', $ownerId)->where('approval_status', 'Deactivate')->get();
        // $OwnerDocuments = OwnerDocuments::get();
        $users = DB::select('select * from users');
        return view('owner.quote_request.quote', compact('users','pageTitle', 'owner','OwnerDocuments','supplierRequests','supplierApproved','supplierDeactivated','QuoteRequests','QuoteProposals','emptyMessage'));
    }


    public function supplierRequest(Request $request)
    {
        $owner = auth()->guard('owner')->user()->id;
        if(empty($request->calltypedocument)){
            $this->validate($request, [
                'coverletter' => 'required',
            ], [
                'coverletter.required' => 'Please add a request message',
            ]);

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
            $this->validate($request, [
                'documentlink' => 'required',
                'documenttype' => 'required',
            ], [
                'documentlink.required' => 'Please upload a document',
                'documenttype.required' => 'Please add a document title',
            ]);
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
    public function sendProposal(Request $request)
    {
        $ownerId = auth()->guard('owner')->user()->id;
        $this->validate($request, [
            'quote_id' => 'required',
            'proposal_message' => 'required',
            'proposal_document' => 'required|file|mimes:pdf,doc,docx|max:2048'
        ], [
            'quote_id.required' => 'Quote Id error, please try again',
            'proposal_message.required' => 'Please add a message',
            'proposal_document.required' => 'Please upload a document',
        ]);

        $file = $request->file('proposal_document');
        $fileName = time().'.'.$file->getClientOriginalExtension();
        $file->move(('uploads/proposals'), $fileName);
        $proposal = new QuoteProposals;
        $proposal->owner_id = $ownerId;
        $proposal->quote_id = $request->quote_id;
        $proposal->proposal_message = $request->proposal_message;
        $proposal->proposal_document = $fileName;
        $proposal-> proposal_status = "Pending";
        $proposal->save();
        $notify[] = ['success', 'Proposal submitted sucessfully'];
        return back()->withNotify($notify);
    }
}
