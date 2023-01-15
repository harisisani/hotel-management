<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Suppliers;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $pageTitle = 'Active Suppliers';
        $suppliersApproved = Suppliers::orderBy('approval_status','asc')
        ->where('approval_status','Active')
        ->orWhere('approval_status','Deactivate')
        ->groupBy('owner_id')
        ->get();

        $suppliersRequested = Suppliers::orderBy('id','desc')
        ->where('approval_status','Pending')
        ->orWhere('approval_status','Rejected')
        // ->orWhere('approval_status','Expired')
        ->get();
        return view('admin.supplier.index', compact('pageTitle', 'suppliersApproved','suppliersRequested'));
    }

    public function activeThis(Request $request){
        $requestId = $request->route('requestId');
        Suppliers::where('id',$requestId)
        ->update(['approval_status' => "Active"]);
        $notify[] = ['success',"Account Actived"];
        return back()->withNotify($notify);
    }

    public function deactiveThis(Request $request){
        $requestId = $request->route('requestId');
        Suppliers::where('id',$requestId)
        ->update(['approval_status' => "Deactivate"]);
        $notify[] = ['success',"Account Deactivated"];
        return back()->withNotify($notify);
    }

    public function approveThis(Request $request){
        $requestId = $request->route('requestId');
        Suppliers::where('id',$requestId)
        ->update(['approval_status' => "Active"]);
        $notify[] = ['success',"Request Approved"];
        return back()->withNotify($notify);
    }

    public function rejectThis(Request $request){
        $requestId = $request->route('requestId');
        Suppliers::where('id',$requestId)
        ->update(['approval_status' => "Rejected"]);
        $notify[] = ['success',"Request Rejected"];
        return back()->withNotify($notify);
    }
}
