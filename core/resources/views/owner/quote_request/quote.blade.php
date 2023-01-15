@extends('owner.layouts.app')
@section('panel')
<?
$checkSupplier="no";
if(count($supplierApproved)>0){
    $checkSupplier="yes";
}
if(count($supplierDeactivated)<1){
    if($checkSupplier=="no"){?>
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
                                <i style="color:#2e7d32" class="fa fa-check">&emsp;@lang('Complete Your Profile')</i>
                            <?} else {$profileFilled="no";?>
                                <a href="{{ route('owner.profile') }}">
                                    <i style="color:#f44336" class="fa fa-times">&emsp;@lang('Complete Your Profile')</i>
                                </a>
                            <?}?>
                            
                            
                        </li>
                    </ul>
                    <br/>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?if(count($OwnerDocuments)>0 && $profileFilled=="yes"){?>
                                <i style="color:#2e7d32" class="fa fa-check">&emsp;@lang('Upload Your Business Documents')</i>
                            <?}else{?>
                                <i style="color:#f44336" class="fa fa-times">&emsp;@lang('Upload Your Business Documents')</i>
                            <?}?>
                        </li>
                    </ul>
                    <?if($profileFilled=="yes"){?>
                        <form action="{{ route('owner.supplier.request') }}" method="POST" enctype="multipart/form-data">
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
                    <?}?>
                    <?if($profileFilled=="yes"){?>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <i style="color:#2e7d32" class="fa fa-registered">&emsp;@lang('Send Register as Supplier Request')</i>
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
                            <button type="submit" class="btn btn--primary btn-block btn-lg">@lang('Send Request')</button>
                        </div>
                    </form>
                    <?}else{?>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <i style="color:#f44336" class="fa fa-registered">&emsp;@lang('Send Register as Supplier Request')</i>
                            </li>
                        </ul>
                    <?}?>
                    <?if(count($OwnerDocuments)>0){?>
                        <div class="d-flex p-3 bg--primary align-items-center">
                            <div class="pl-3">
                                <h4 class="text--white">@lang('Documents & Requests History')</h4>
                            </div>
                        </div>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <i style="color:#1769AA;" class="fa fa-history">&emsp;@lang('Documents History')</i>
                            </li>
                        </ul>
                        <div class="row">
                            <div class="col-md-12">
                                <?php foreach($OwnerDocuments as $value) {?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <a target="_blank" href="https://booking.emphospitality.com/uploads/owner-files/<?=$owner->id?>/<?=$value['document_name']?>"><i class="fa fa-download">&emsp;<?=$value['document_title']?></i></a>
                                    </li>
                                <?}?>
                            </div>
                        </div>
                        <?}?>
                        <br/>
            <? if(count($supplierRequests)>0){?>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <i style="color:#1769AA;" class="fa fa-history">&emsp;@lang('Requests History')</i>
                    </li>
                </ul>
                <table class="table table-striped">
                    <thead>
                        <tr>
                        <th scope="col">Requested Date</th>
                        <th scope="col">Request Message</th>
                        <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <? foreach($supplierRequests as $value){
                        $timestamp = strtotime($value['created_at']);
                        $date = date('F d, Y', $timestamp);
                        ?>
                        <tr>
                        <td><?= $date ?></td>
                        <td><?=$value['request_message']?></td>
                        <td><?=$value['approval_status']?></td>
                        </tr>
                        <?}?>
                    </tbody>
                    </table>
                    <?}?>
        </div>
    </div>
    <?
    } 
    else{
    ?>
    <div class="row mb-none-30">
        <div class="col-12 mb-30">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Requested Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>wewqee</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-12 mb-30">
            <div class="card b-radius--5 overflow-hidden">
                <div class="card-body p-0">
                    <div class="d-flex p-3 bg--primary align-items-center">
                        <div class="pl-3">
                            <h4 class="text--white">@lang('Your Quotes Requests')</h4>
                        </div>
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Requested Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>wewqee</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?}}else{?>
    <div class="d-flex p-3 bg--danger align-items-center">
        <div class="pl-3">
            <h4 class="text--white">@lang('Your Supplier Account is Deactivated, contact Administrator')</h4>
        </div>
    </div>
<?}?>
@endsection
