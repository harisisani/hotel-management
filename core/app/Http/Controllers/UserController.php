<?php

namespace App\Http\Controllers;

use App\Lib\GoogleAuthenticator;
use App\Models\GeneralSetting;
use App\Models\Property;
use App\Models\BookedProperty;
use App\Models\Review;
use App\Models\QuoteRequests;
use App\Models\QuoteProposals;
use App\Models\SupportTicket;
use App\Models\Location;
use App\Models\QuoteProposalsStatuses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
class UserController extends Controller
{
    public function __construct()
    {
        $this->activeTemplate = activeTemplate();
    }

    public function home()
    {
        $pageTitle = 'Dashboard';
        $emptyMessage = 'No booking history found';
        $widget['total_tickets'] = SupportTicket::where('user_id', Auth::id())->count();
        $widget['total_booked'] = BookedProperty::where('user_id', Auth::id())->where('status', 1)->count();
        $widget['total_pending_review'] =  Property::with('bookedProperties')
            ->whereHas('bookedProperties', function($bookedProperty){
                $bookedProperty->where('user_id', Auth::id())->where('status', 1);
            })->whereDoesntHave('review')->count();
        $propertyBookings = BookedProperty::with('property', 'bookedRooms.room')->where('user_id', Auth::id())->orderBy('id', 'DESC')->limit(6)->get();

        return view($this->activeTemplate . 'user.dashboard', compact('pageTitle', 'emptyMessage', 'propertyBookings', 'widget'));
    }

    public function profile()
    {
        $pageTitle = "Profile Setting";
        $user = Auth::user();
        return view($this->activeTemplate. 'user.profile_setting', compact('pageTitle','user'));
    }

    public function submitProfile(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'address' => 'sometimes|required|max:80',
            'state' => 'sometimes|required|max:80',
            'zip' => 'sometimes|required|max:40',
            'city' => 'sometimes|required|max:50',
        ],[
            'firstname.required'=>'First name field is required',
            'lastname.required'=>'Last name field is required'
        ]);

        $user = Auth::user();

        $in['firstname'] = $request->firstname;
        $in['lastname'] = $request->lastname;

        $in['address'] = [
            'address' => $request->address,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => @$user->address->country,
            'city' => $request->city,
        ];

        $user->fill($in)->save();
        $notify[] = ['success', 'Profile updated successfully.'];
        return back()->withNotify($notify);
    }

    public function changePassword()
    {
        $pageTitle = 'Change password';
        return view($this->activeTemplate . 'user.password', compact('pageTitle'));
    }

    public function submitPassword(Request $request)
    {

        $password_validation = Password::min(6);
        $general = GeneralSetting::first();
        if ($general->secure_password) {
            $password_validation = $password_validation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $this->validate($request, [
            'current_password' => 'required',
            'password' => ['required','confirmed',$password_validation]
        ]);


        try {
            $user = auth()->user();
            if (Hash::check($request->current_password, $user->password)) {
                $password = Hash::make($request->password);
                $user->password = $password;
                $user->save();
                $notify[] = ['success', 'Password changes successfully.'];
                return back()->withNotify($notify);
            } else {
                $notify[] = ['error', 'The password doesn\'t match!'];
                return back()->withNotify($notify);
            }
        } catch (\PDOException $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function propertyHistory()
    {
        $pageTitle = 'Booking History';
        $emptyMessage = 'No booking history found';
        $propertyBookings = BookedProperty::with('property', 'bookedRooms.room')->where('user_id', Auth::id())->where('status', 1)->latest()->paginate(getPaginate());

        return view($this->activeTemplate.'user.booking_history', compact('pageTitle', 'emptyMessage', 'propertyBookings'));

    }

    public function reviews()
    {
        $pageTitle = 'Reviews';
        $emptyMessage = 'No property found';

        $pendingReviewProperties = Property::with(['location', 'review','bookedProperties'=>function($bookedProperty){
            $bookedProperty->select('id');
        }
        ])->select('id', 'name', 'location_id', 'phone')->whereHas('bookedProperties', function($bookedProperty){
            $bookedProperty->where('user_id', Auth::id())->where('status', 1);
        })->whereDoesntHave('review')->limit(10)->get();

        $reviewedProperties = Property::with(['location', 'review'])->select('id', 'name', 'location_id', 'phone')->whereHas('review', function($reviewed){
            $reviewed->where('user_id', Auth::id());
        })->limit(10)->get();

        return view($this->activeTemplate.'user.review', compact('pageTitle', 'emptyMessage', 'pendingReviewProperties', 'reviewedProperties'));
    }

    public function saveReview(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|between:1,5'
        ]);
        $propertyId = $request->property_id;

        BookedProperty::where('property_id', $propertyId)->where('user_id', Auth::id())->firstOrFail();

        $review = Review::where('user_id', Auth::id())->where('property_id', $propertyId)->first();
        $property = Property::find($propertyId);
        if (!$review) {
            $review = new Review();
            $property->total_rating += $request->rating;
            $property->review += 1;
            
            $notify[] = ['success', 'Review given successfully'];
        }else{
            $property->total_rating = $property->total_rating - $review->rating + $request->rating;
            $notify[] = ['success', 'Review updated successfully'];
        }
        $property->rating = $property->total_rating / $property->review;
        $property->save();

        $review->rating = $request->rating;
        $review->description = $request->description;
        $review->user_id = Auth::id();
        $review->property_id = $propertyId;
        $review->save();

        return back()->withNotify($notify);

    }

   public function pendingReview()
   {
        $pageTitle = 'Pending Review';
        $emptyMessage = 'No pending review found';
        $pendingReviewProperties = Property::with(['location', 'review','bookedProperties'=>function($bookedProperty){
                $bookedProperty->select('id');
            }
            ])->select('id', 'name', 'location_id', 'phone')->whereHas('bookedProperties', function($bookedProperty){
                $bookedProperty->where('user_id', Auth::id())->where('status', 1);
        })->whereDoesntHave('review')->paginate(getPaginate());

        return view($this->activeTemplate.'user.pending_review', compact('pageTitle', 'emptyMessage', 'pendingReviewProperties'));
   }

    public function completedReview()
    {
        $pageTitle = 'Completed Review';
        $emptyMessage = 'No completed review found';
        $reviewedProperties = Property::with(['location', 'review'])->select('id', 'name', 'location_id', 'phone')->whereHas('review', function($reviewed){
            $reviewed->where('user_id', Auth::id());
        })->paginate(getPaginate());

        return view($this->activeTemplate.'user.completed_review', compact('pageTitle', 'emptyMessage', 'reviewedProperties'));
    }

    public function show2faForm()
    {
        $general = GeneralSetting::first();
        $ga = new GoogleAuthenticator();
        $user = auth()->user();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . $general->sitename, $secret);
        $pageTitle = 'Two Factor';
        return view($this->activeTemplate.'user.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request)
    {
        $user = auth()->user();
        $this->validate($request, [
            'key' => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($user,$request->code,$request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts = 1;
            $user->save();
            $userAgent = getIpInfo();
            $osBrowser = osBrowser();
            notify($user, '2FA_ENABLE', [
                'operating_system' => @$osBrowser['os_platform'],
                'browser' => @$osBrowser['browser'],
                'ip' => @$userAgent['ip'],
                'time' => @$userAgent['time']
            ]);
            $notify[] = ['success', 'Google authenticator enabled successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Wrong verification code'];
            return back()->withNotify($notify);
        }
    }

    public function disable2fa(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $user = auth()->user();
        $response = verifyG2fa($user,$request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts = 0;
            $user->save();
            $userAgent = getIpInfo();
            $osBrowser = osBrowser();
            notify($user, '2FA_DISABLE', [
                'operating_system' => @$osBrowser['os_platform'],
                'browser' => @$osBrowser['browser'],
                'ip' => @$userAgent['ip'],
                'time' => @$userAgent['time']
            ]);
            $notify[] = ['success', 'Two factor authenticator disable successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }

    //Quote Request Module
    function user_quote_request(){
        $pageTitle = "Quote Requests";
        $emptyMessage = 'No quote requests found';
        $user = Auth::guard('owner')->user();
        $userId = auth()->user()->id;
        QuoteRequests::whereDate('quote_deadline', '<', DB::raw('CURRENT_DATE'))
            ->where('deleted', 0)
            ->where('created_by_user_id', $userId)
            ->where(function ($query) {
                $query->where('published_status', 0)
                      ->orWhere('published_status', 1)
                      ->orWhere('published_status', 2)
                      ->orWhere('published_status', 3);
            })
            ->update(['published_status' => 10]);
        $locations = Location::where('status', 1)->get();
        $QuoteRequests = QuoteRequests::where('created_by_user_id', $userId)->where('deleted', 0)->orderBy('created_at', 'desc')->get();
        $QuoteProposals = QuoteProposals::where('deleted', 0)
        ->where('created_by_user_id',$userId)
        ->where('proposal_status', "Sent")
        ->update(['proposal_status' => 'Received']);

        $QuoteProposals = QuoteProposals::where('deleted', 0)
            ->where(function ($query) {
                $query->where('proposal_status', "Sent")
                    ->orWhere('proposal_status', "Received")
                    ->orWhere('proposal_status', "Awarded")
                    ->orWhere('proposal_status', "Rejected");
        })
        ->where('created_by_user_id',$userId)
        ->orderBy('created_at', 'asc')->get();
        $owners = DB::select('select * from owners');
        $QuoteProposalsStatuses = QuoteProposalsStatuses::where('created_by_user_id', $userId)->where('deleted', 0)->orderBy('created_at', 'asc')->get();
        return view($this->activeTemplate.'user.quote_request.index', compact('QuoteProposalsStatuses','owners','pageTitle','emptyMessage','user','QuoteRequests','QuoteProposals','locations'));
    }

    function user_quote_request_create(Request $request){
        $this->validate($request, [
            'quote_title' => 'required',
            'quote_deadline' => 'required',
            'quote_location' => 'required',
        ], [
            'quote_title.required' => 'Please add a title',
            'quote_deadline.required' => 'Please add a deadline',
            'quote_location.required' => 'Please select a location',
        ]);
        $user = auth()->user()->id;
        if ($request->hasFile('quote_document')) {
            $files = $request->file('quote_document');
            $filenames = '';
            foreach ($files as $index => $file) {
                $extention = $file->getClientOriginalExtension();
                $filename = ++$index . '_' . time() . '.' . $extention;
                $folder = 'quote-files/';
                $file->move('uploads/' . $folder, $filename);
                $filenames .= $filename . ',';
            }
        }else{
            $filenames='';
        }
        $quote = new QuoteRequests;
        // Assign values to the model's attributes using the request data
        $quote->created_by_user_id = $user;
        $quote->quote_title = $request->input('quote_title');
        $quote->quote_deadline = $request->input('quote_deadline'); 
        $quote->quote_location = $request->input('quote_location'); 
        $quote->quote_status = 'Active';
        $quote->quote_document = $filenames;
        $quote->published_status = 1;
        $quote->deleted = 0;
        // Save the model to the database
        $quote->save();
        $notify[] = ['success', 'Quote Request Saved Successfully'];
        return back()->withNotify($notify);
    }

    public function deleteThis(Request $request){
        $quoteId = $request->route('quoteId');
        QuoteRequests::where('id',$quoteId)
        ->update(['deleted' => 1]);
        $notify[] = ['success',"Quote Request Deleted"];
        return back()->withNotify($notify);
    }

    public function publishThis(Request $request){
        $quoteId = $request->route('quoteId');
        QuoteRequests::where('id',$quoteId)
        ->update(['published_status' => 0]);
        $notify[] = ['success',"Quote Request Sent"];
        return back()->withNotify($notify);
    }

    public function acceptThis(Request $request){
        $proposalId = $request->route('proposalId');
        QuoteProposals::where('id',$proposalId)
        ->update(['proposal_status' => "Awarded"]);
        $notify[] = ['success',"Proposal Awarded"];

        $quoteId = QuoteProposals::where('id', $proposalId)->value('quote_id');
        QuoteRequests::where('id',$quoteId)
        ->update(['published_status' => 4]);

        return back()->withNotify($notify);
    }

    public function rejectThis(Request $request){
        $proposalId = $request->route('proposalId');
        QuoteProposals::where('id',$proposalId)
        ->update(['proposal_status' => "Rejected"]);
        $notify[] = ['success',"Proposal Rejected"];

        $quoteId = QuoteProposals::where('id', $proposalId)->value('quote_id');
        QuoteRequests::where('id',$quoteId)
        ->update(['published_status' => 5]);
        return back()->withNotify($notify);
    }
    public function markPaid(Request $request){
        $proposalId = $request->route('proposalId');
        QuoteProposals::where('id',$proposalId)
        ->update(['payment_status' => "Payment Completed"]);
        $notify[] = ['success',"Marked as Payment Completed"];
        return back()->withNotify($notify);
    }

    public function addStatus(Request $request){
        $this->validate($request, [
            'quote_proposal_id' => 'required',
            'status_type' => 'required'
        ], [
            'quote_proposal_id.required' => 'Proposal Id error, please try again',
            'status_type.required' => 'Please select a status type'
        ]);
        $userId = auth()->user()->id;

        if($request->file('status_document')){
            $file = $request->file('status_document');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $file->move(('uploads/proposals/statuses'), $fileName);
        }else{
            $fileName = "";
        }

        $proposalStatus = new QuoteProposalsStatuses;
        $proposalStatus->quote_proposal_id = $request->quote_proposal_id;
        $proposalStatus->status_type = $request->status_type;
        $proposalStatus->status_document = $fileName;
        $proposalStatus->created_by_user_id = $userId;
        $proposalStatus->status_message = "";
        $proposalStatus-> deleted = 0;
        $proposalStatus->save();

        $notify[] = ['success', 'Status submitted sucessfully'];
        return back()->withNotify($notify);
    }

}
