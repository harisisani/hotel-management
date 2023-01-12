<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Lib\GoogleAuthenticator;
use App\Models\AdminNotification;
use App\Models\GeneralSetting;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class QuoteRequestController extends Controller
{
    public function dashboard()
    {
        $pageTitle = 'Dashboard';
        $widget['balance'] = Auth::guard('owner')->user()->balance;
        $widget['total_properties'] = Property::where('owner_id', Auth::guard('owner')->id())->count();
        $widget['total_rooms'] = Room::with('property')
        ->whereHas('property', function($property){
            $property->where('owner_id', Auth::guard('owner')->id());
        })->count();
        $widget['total_room_category'] = RoomCategory::with('property')
        ->whereHas('property', function($property){
            $property->where('owner_id', Auth::guard('owner')->id());
        })->count();
        return view('owner.dashboard', compact('pageTitle', 'widget'));
    }

    public function profile()
    {
        $pageTitle = 'Profile';
        $owner = Auth::guard('owner')->user();
        return view('owner.profile', compact('pageTitle', 'owner'));
    }

    public function profileUpdate(Request $request)
    {

        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'address' => 'sometimes|required|max:80',
            'state' => 'sometimes|required|max:80',
            'zip' => 'sometimes|required|integer|min:1',
            'city' => 'sometimes|required|max:50',
        ],[
            'firstname.required'=>'First name field is required',
            'lastname.required'=>'Last name field is required'
        ]);
        $owner = Auth::guard('owner')->user();

        $owner->firstname= $request->firstname;
        $owner->lastname= $request->lastname;


        $in['address'] = [
            'address' => $request->address,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => @$owner->address->country,
            'city' => $request->city,
        ];

        $owner->fill($in)->save();

        $notify[] = ['success', 'Profile updated successfully.'];
        return back()->withNotify($notify);

    }

    public function changePassword()
    {
        $pageTitle = 'Change password';
        $owner = Auth::guard('owner')->user();
        return view('owner.password', compact('pageTitle', 'owner'));
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
            $owner = auth()->guard('owner')->user();
            if (Hash::check($request->current_password, $owner->password)) {
                $password = Hash::make($request->password);
                $owner->password = $password;
                $owner->save();
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

    public function show2faForm()
    {
        $general = GeneralSetting::first();
        $ga = new GoogleAuthenticator();
        $owner = auth()->guard('owner')->user();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($owner->username . '@' . $general->sitename, $secret);
        $pageTitle = 'Two Factor';
        return view('owner.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request)
    {
        $owner = auth()->guard('owner')->user();
        $this->validate($request, [
            'key' => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($owner,$request->code,$request->key);
        if ($response) {
            $owner->tsc = $request->key;
            $owner->ts = 1;
            $owner->save();
            $ownerAgent = getIpInfo();
            $osBrowser = osBrowser();
            notify($owner, '2FA_ENABLE', [
                'operating_system' => @$osBrowser['os_platform'],
                'browser' => @$osBrowser['browser'],
                'ip' => @$ownerAgent['ip'],
                'time' => @$ownerAgent['time']
            ], 'owner');
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

        $owner = auth()->guard('owner')->user();
        $response = verifyG2fa($owner,$request->code);
        if ($response) {
            $owner->tsc = null;
            $owner->ts = 0;
            $owner->save();
            $ownerAgent = getIpInfo();
            $osBrowser = osBrowser();
            notify($owner, '2FA_DISABLE', [
                'operating_system' => @$osBrowser['os_platform'],
                'browser' => @$osBrowser['browser'],
                'ip' => @$ownerAgent['ip'],
                'time' => @$ownerAgent['time']
            ], 'owner');
            $notify[] = ['success', 'Two factor authenticator disable successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }

     /*
     * Withdraw Operation
     */

    public function quoteRequest()
    {
        $pageTitle = "Quote Requests";
        $owner = Auth::guard('owner')->user();
        return view('owner.quote_request.quote', compact('pageTitle', 'owner'));
    }


    public function supplierRequest(Request $request)
    {

        // $request->validate([
        //     'firstname' => 'required|string|max:50',
        //     'lastname' => 'required|string|max:50',
        //     'address' => 'sometimes|required|max:80',
        //     'state' => 'sometimes|required|max:80',
        //     'zip' => 'sometimes|required|integer|min:1',
        //     'city' => 'sometimes|required|max:50',
        // ],[
        //     'firstname.required'=>'First name field is required',
        //     'lastname.required'=>'Last name field is required'
        // ]);
        // $owner = Auth::guard('owner')->user();

        // $owner->firstname= $request->firstname;
        // $owner->lastname= $request->lastname;


        // $in['address'] = [
        //     'address' => $request->address,
        //     'state' => $request->state,
        //     'zip' => $request->zip,
        //     'country' => @$owner->address->country,
        //     'city' => $request->city,
        // ];

        // $owner->fill($in)->save();

        if(empty($request->calltypedocument)){
            $notify[] = ['success', 'Supplier Request Send Successfully.'];
        }else{
            $document='';

            $notify[] = ['success', 'Document Uploaded Successfully.'];
        }
        return back()->withNotify($notify);
    }
}
