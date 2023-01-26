@extends('owner.layouts.app')
@section('panel')
<?
$submittedProposals=array();
foreach($QuoteProposals as $proposal){
    array_push($submittedProposals,$proposal->quote_id);
}
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
                            <h4 class="text--white">@lang('Complete below requirements to see Quote Requests')</h4>
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
                    {{-- <br/> --}}
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
                                    <input style="height:50px;" placeholder="Request Message" class="form-control" type="text" name="coverletter" >
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
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Quote Title')</th>
                                    <th>@lang('Quote Deadline')</th>
                                    <th>@lang('Quote Created')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($QuoteRequests as $quote)
                                @if(!in_array($quote->id,$submittedProposals))
                                    <tr>
                                        <td data-label="@lang('Quote Title')"> <a target="_blank" href="https://booking.emphospitality.com/uploads/quote-files/{{ $quote->quote_document}}">{{ $quote->quote_title}}</a></td>
                                        <td data-label="@lang('Quote Deadline')"><?=date('F d, Y',strtotime($quote->quote_deadline))?></td>
                                        <td data-label="@lang('Quote Created')">{{ showDateTime($quote->created_at) }}</td>
                                        <td data-label="@lang('Action')">
                                            <a href="javascript:void(0)" data-value="<?=$quote->id?>" class="icon-btn ml-1 submitProposal"><i class="la la-pen"></i></i>Send Proposal</a>
                                        </td>
                                    </tr>
                                @endif 
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
    
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row align-items-center mb-30 justify-content-between">
        <div class="col-lg-6 col-sm-6">
            <h6 class="page-title">Your Quotes</h6>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                        @forelse($QuoteRequests as $quote)
                            <thead>
                                @if(in_array($quote->id,$submittedProposals))
                                <tr>
                                    <th>@lang('Quote Title')</th>
                                    <td  style="vertical-align: bottom;text-align:left;" data-label="@lang('Quote Title')"> <a target="_blank" href="https://booking.emphospitality.com/uploads/quote-files/{{ $quote->quote_document}}">{{ $quote->quote_title}}</a></td>
                                    <th>@lang('Quote Deadline')</th>
                                    <td  style="vertical-align: bottom;text-align:left;" data-label="@lang('Quote Deadline')"><?=date('F d, Y',strtotime($quote->quote_deadline))?></td>
                                    <th>@lang('Quote Created')</th>
                                    <td  style="vertical-align: bottom;text-align:left;" data-label="@lang('Quote Created')">{{ showDateTime($quote->created_at) }}</td>
                                </tr>  
                                @endif 
                                    @foreach ($QuoteProposals as $proposal)
                                    @if($proposal->quote_id == $quote->id)
                                    <tr>
                                        <th>@lang('Proposal'): </th>
                                        <td style="vertical-align: bottom;text-align:left;">
                                            {{$proposal->proposal_message}}
                                            <br/>
                                            <br/>
                                            <a target="_blank" href="https://booking.emphospitality.com/uploads/proposals/{{ $proposal->proposal_document}}">@lang('Proposal Download')</a>
                                        </td>
                                        <th>@lang('Proposal Created')</th>
                                        <td  style="vertical-align: bottom;text-align:left;" data-label="@lang('Proposal Created')">{{ showDateTime($proposal->created_at) }}</td>
                                        <th>@lang('Status')</th>
                                        <td style="vertical-align: bottom;text-align:left;" data-label="@lang('Action')">
                                            {{$proposal->proposal_status}}
                                             @if($proposal->proposal_status=="Accepted")
                                             @foreach($users as $user)
                                             @if($user->id==$quote->created_by_user_id)
                                            <?
                                                $jsonAddress=json_decode($user->address);
                                                $address=$jsonAddress->address.', '.$jsonAddress->city.', '.$jsonAddress->state.', '.$jsonAddress->zip.', '.$jsonAddress->country;
                                            ?>
                                            <br/>
                                            <br/>
                                            <a href="javascript:void(0)" onclick="UserDetails('{{$user->firstname}}','{{$user->lastname}}','{{$user->email}}','{{$user->mobile}}','{{$address}}')"
                                                class="p-1 btn--primary ml-1">see customer details
                                                 <i class="las la-check-circle"></i>
                                             </a>
                                             @endif
                                             @endforeach
                                             @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach   
                                
                            </thead>
                            <tbody>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                </tbody>
                                @endforelse
                            </table><!-- table end -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="submitProposalModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Submit a Proposal')</h5>
                    <button type="button" class="btn btn-sm btn--danger closeProposal" data-bs-dismiss="modal" aria-label="Close">
                        <span style="color:white;" aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('owner.quote.proposal')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <label>@lang('Proposal Message')</label>
                        <div class="input-group has_append">
                            <textarea type="text" name="proposal_message" class="form-control" placeholder="@lang('Enter Proposal Message')"></textarea>
                            <input type="hidden" id="quoteId" name="quote_id" class="form-control" placeholder="@lang('Enter Quote Id')">
                        </div>
                        <label>@lang('Proposal Document')</label>
                        <div class="input-group has_append">
                            <input type="file" name="proposal_document" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark closeProposal" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" style="background-color: #7367f0;color:white;" class="btn btn--base">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<div id="showUser" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Supplier Details')</h5>
                <button type="button" class="btn btn-sm btn--danger closeProposal" data-bs-dismiss="modal" aria-label="Close">
                    <span style="color:white;" aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                      <tr>
                        <th colspan=2 scope="col">Your supplier details</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th scope="row">First Name</th>
                        <td id="fname">Mark</td>
                      </tr>
                      <tr>
                        <th scope="row">Last Name</th>
                        <td id="lname" >Jacob</td>
                      </tr>
                      <tr>
                        <th scope="row">Email</th>
                        <td id="email">Larry</td>
                      </tr>
                      <tr>
                        <th scope="row">Contact</th>
                        <td id="contact">Larry</td>
                      </tr>
                      <tr>
                        <th scope="row">Address</th>
                        <td id="address">Larry</td>
                      </tr>
                    </tbody>
                  </table>
            </div>
        </div>
    </div>
</div>
    @push('script')
    <script>
        (function ($) {
            "use strict";
            $('.submitProposal').click(function () {
                var value = $(this).data('value');
                $('#quoteId').val(value);
                var modal = $('#submitProposalModal');
                modal.modal('show');
            });

            $('.closeProposal').click(function () {
                var modal = $('#submitProposalModal');
                modal.modal('hide');
                var modal = $('#showUser');
                modal.modal('hide');
            });

        })(jQuery);

        UserDetails =  (fname,lname,email,contact,address) => {
            $('#fname').html(fname);
            $('#lname').html(lname);
            $('#email').html(email);
            $('#contact').html(contact);
            $('#address').html(address);
            var modal = $('#showUser');
            modal.modal('show');
        }
    </script>
    @endpush
<?}}else{?>
    <div class="d-flex p-3 bg--danger align-items-center">
        <div class="pl-3">
            <h4 class="text--white">@lang('Your Supplier Account is Deactivated, contact Administrator')</h4>
        </div>
    </div>
<?}?>
@endsection

