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
use App\Models\QuoteProposalsStatuses;
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
        $QuoteProposals = QuoteProposals::where('owner_id', $ownerId)->where('deleted', 0)->orderBy('created_at', 'asc')->get();
        $supplierApproved = Suppliers::where('owner_id', $ownerId)->where('approval_status', 'Active')->get();
        $supplierDeactivated = Suppliers::where('owner_id', $ownerId)->where('approval_status', 'Deactivate')->get();
        // $OwnerDocuments = OwnerDocuments::get();
        $users = DB::select('select * from users');
        $properties = Property::with('location')->where('owner_id', Auth::guard('owner')->id())->orderBy('id', 'DESC')->paginate(getPaginate());
        $locations=array();
        foreach($properties as $property){
            array_push($locations,$property['location']->name);
        }
        $QuoteRequests = QuoteRequests::whereDate('quote_deadline','>=',DB::raw('CURRENT_DATE'))
            ->where(function ($query) use ($locations) {
                foreach ($locations as $location) {
                    $query->orWhere('quote_location', 'like', "%$location%");
                }
            })
            ->where('deleted', 0)
            ->where(function ($query) {
                $query->where('published_status', 0)
                      ->orWhere('published_status', 2)
                      ->orWhere('published_status', 3)
                      ->orWhere('published_status', 4)
                      ->orWhere('published_status', 5);
            })
            ->orderBy('created_at', 'desc')->get();


            QuoteRequests::whereDate('quote_deadline', '>=', DB::raw('CURRENT_DATE'))
            ->where(function ($query) use ($locations) {
                foreach ($locations as $location) {
                    $query->orWhere('quote_location', 'like', "%$location%");
                }
            })
            ->where('deleted', 0)
            ->where('published_status', 0)
            ->update(['published_status' => 2]);

        $QuoteProposalsStatuses = QuoteProposalsStatuses::
        where('deleted', 0)
        ->orderBy('created_at', 'asc')->get();
        return view('owner.quote_request.quote', compact('QuoteProposalsStatuses','users','pageTitle', 'owner','OwnerDocuments','supplierRequests','supplierApproved','supplierDeactivated','QuoteRequests','QuoteProposals','emptyMessage'));
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
        $proposal->created_by_user_id = $request->created_by_user_id;
        $proposal->quote_id = $request->quote_id;
        $proposal->proposal_message = $request->proposal_message;
        $proposal->proposal_document = $fileName;
        $proposal-> proposal_status = "Draft";
        $proposal->save();

        QuoteRequests::where('id', $request->quote_id)
        ->update(['published_status' => 3]);

        $notify[] = ['success', 'Proposal submitted sucessfully'];
        return back()->withNotify($notify);
    }

    public function deleteThis(Request $request){
        $proposalId = $request->route('proposalId');
        QuoteProposals::where('id',$proposalId)
        ->update(['deleted' => 1]);
        $notify[] = ['success',"Proposal Deleted"];
        return back()->withNotify($notify);
    }

    public function sentThis(Request $request){
        $proposalId = $request->route('proposalId');
        QuoteProposals::where('id',$proposalId)
        ->update(['proposal_status' => "Sent"]);
        $notify[] = ['success',"Proposal Sent"];
        return back()->withNotify($notify);
    }

    public function markSupplied(Request $request){
        $proposalId = $request->route('proposalId');
        QuoteProposals::where('id',$proposalId)
        ->update(['supplier_status' => "Supplied"]);
        $notify[] = ['success',"Marked as Supplied"];
        return back()->withNotify($notify);
    }

}
