@extends('owner.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-12 mb-30">
            <div class="card b-radius--5 overflow-hidden">
                <div class="card-body p-0">
                    <div class="d-flex p-3 bg--primary align-items-center">
                        <div class="pl-3">
                            <h4 class="text--white">@lang('To see Quote Requests you must register as supplier')</h4>
                        </div>
                    </div>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?if(
                                !empty($owner->firstname) && !empty($owner->lastname) && !empty($owner->email)
                                && !empty($owner->mobile) && !empty($owner->address->address) && !empty($owner->address->state)
                                && !empty($owner->address->country) && !empty($owner->address->zip) && !empty($owner->address->city)
                                ){ $profileFilled="yes";
                                ?>
                                <i style="color:#2e7d32" class="fa fa-check">&emsp;@lang('Complete Your Profile')</a></i>
                            <?} else {$profileFilled="no";?>
                                <i style="color:#f44336" class="fa fa-times">&emsp;@lang('Complete Your Profile')</a></i>
                            <?}?>
                        </li>
                    </ul>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <i class="fa fa-check">&emsp;@lang('Upload Your Business Documents')</i>
                        </li>
                    </ul>
                        <form action="{{ route('owner.supplier.request') }}" method="POST">
                            @csrf
                            <div class="row" style="padding:25px;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input style="height:50px;" placeholder="Enter Document Title" class="form-control" type="text" name="documenttype" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input style="height:50px;" class="form-control" type="file" name="documentlink">
                                        <input type="hidden" value="document" name="calltypedocument">
                                    </div>
                                </div>
                            </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn--success btn-block btn-lg">@lang('Upload')</button>
                            </div>
                        </form>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <i class="fa fa-registered">&emsp;@lang('Send Register as Supplier Request')</i>
                        </li>
                    </ul>
                    <form action="{{ route('owner.supplier.request') }}" method="POST">
                        @csrf
                        <div class="row" style="padding:25px;">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input style="height:50px;" placeholder="Request Message (optional)" class="form-control" type="text" name="coverletter" >
                                    <input type="hidden" value="request" name="calltyperequest">
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn--primary btn-block btn-lg">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
