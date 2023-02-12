<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@extends($activeTemplate.'layouts.master')
@section('content')
<div class="custom--card">
    <div class="card-header pb-3">
        <h6 class="d-inline">@lang('Your Quotes')</h6>
        <a href="javascript:void(0)" class="btn btn--base btn-sm float-end openNewQuote">@lang('Create a New Quote Request')</a>
    </div>
</div>
<br/>
    <?foreach($QuoteRequests as $value){
    $timestamp = showDateTime($value['created_at']);
    // $createdDate = date('F d, Y', $timestamp);    
    $deadline = date('F d, Y',strtotime($value['quote_deadline']));
    ?>
    <div class="custom--card">
        <div class="card-body">
            <div class="table-responsive--md">
                <div class="card-header pb-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Quote</th>
                                <th scope="col">Created Date</th>
                                <th scope="col">Locations</th>
                                <th scope="col">Deadline</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="row">
                                    <?php
                                    echo $value["quote_title"].'<br/>';
                                    $files = explode(",", $value['quote_document']);
                                    foreach($files as $index => $file){
                                    if(!empty($file)){
                                    ?>
                                    <br/>
                                    <a target="_blank" href="https://booking.emphospitality.com/uploads/quote-files/<?=$file?>" class="d-inline"><?='Document: '.++$index?></a>
                                <?php
                                    }}
                                ?>
                                </td>
                                <td scope="row"><?=$timestamp?></td>
                                <td scope="row"><?=(str_replace(",","<br/>",$value['quote_location']))?></td>
                                <td scope="row"><?=$deadline?></td>
                                <td scope="row">
                                    <?=($value->published_status==0)? "Sent" : ""?>
                                    <?=($value->published_status==1)? "Draft" : ""?>
                                    <?=($value->published_status==2)? "Received" : ""?>
                                    <?=($value->published_status==3)? "In Review" : ""?>
                                    <?=($value->published_status==4)? "Awarded" : ""?>
                                    <?=($value->published_status==5)? "Rejected" : ""?>
                                    <?=($value->published_status==6)? "Commitment" : ""?>
                                    <?=($value->published_status==7)? "Checked In" : ""?>
                                    <?=($value->published_status==8)? "Checked Out" : ""?>
                                    <?=($value->published_status==9)? "Payment" : ""?>
                                    <?=($value->published_status==10)? "Deadline Expired" : ""?>
                                </td>
                                <td nowrap data-label="@lang('Action')">
                                @if($value->published_status==1)
                                    <a href="{{ route('user.quote.publish', ['quoteId' => $value->id]) }}">
                                    <button class="p-1 btn--success ml-1">Send
                                        <i class="las la-check-circle"></i>
                                    </button>
                                    </a>
                                    <a href="{{ route('user.quote.delete', ['quoteId' => $value->id]) }}">
                                    <button class="p-1 btn--danger ml-1">Delete
                                        <i class="las la-exclamation-triangle"></i>
                                    </button>
                                    </a>
                                    @endif
                                </tr>
                        </tbody>
                    </table>
                </div>
                <table class="table custom--table">
                    <thead>
                        <tr>
                            <th>@lang('Message')</th>
                            <th>@lang('Document')</th>
                            <th>@lang('Created Date')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($QuoteProposals as $proposal)
                        @if($proposal->quote_id==$value['id'])
                        <tr>
                            <td data-label="@lang('Proposal Message')">{{ $proposal->proposal_message }}</td>
                            <td data-label="@lang('Proposal Document')">
                                <a target="_blank" href="https://booking.emphospitality.com/uploads/proposals/{{ $proposal->proposal_document}}">@lang('Proposal Download')</a>
                            </td>
                            <td data-label="@lang('Proposal Created')">{{ showDateTime($proposal->created_at) }}</td>
                            <td style="text-align:left;" data-label="@lang('Proposal Created')">
                                <strong>Proposal Status:</strong>&nbsp;{{ ($proposal->proposal_status) }}
                                {{-- <br/>
                                Supply Status:&nbsp;<strong>{{ ($proposal->supplier_status) }}</strong>
                                <br/>
                                Payment Status:&nbsp;<strong>{{ ($proposal->payment_status) }}</strong> --}}
                                <?php
                                $quote_status=array();
                                foreach( $QuoteProposalsStatuses as $status){
                                   if($status->quote_proposal_id == $proposal->id){
                                       $quote_status[]=array(
                                          'status_type' => $status['status_type'],
                                          'status_document' => $status['status_document'],
                                       );
                                   }
                                }
                                if(count($quote_status)>0){?>
                                <br/><br/><strong>Service Status:</strong>
                                <?php
                                foreach($quote_status as $quoteStatus){
                                   if(!empty($quoteStatus['status_document'])){
                                       echo '<br/><a target="_blank" href="https://booking.emphospitality.com/uploads/proposals/statuses/'.$quoteStatus['status_document'].'">'.$quoteStatus['status_type'].'</a>';
                                   }else{
                                       echo '<br/>'.$quoteStatus['status_type'];
                                   }
                                }
                                }
                                ?>
                            </td>
                            <td nowrap data-label="@lang('Action')">
                                @if($proposal->proposal_status=="Sent" || $proposal->proposal_status=="Received" || $proposal->proposal_status=="Rejected" )
                                <a href="{{ route('user.proposal.acceptThis', ['proposalId' => $proposal->id]) }}">
                                    <button class="p-1 btn--success ml-1">Award
                                        <i class="las la-check-circle"></i>
                                    </button>
                                 </a>
                                 @endif
                                 @if($proposal->proposal_status=="Sent" || $proposal->proposal_status=="Received")
                                 <a href="{{ route('user.proposal.rejectThis', ['proposalId' => $proposal->id]) }}">
                                    <button class="p-1 btn--danger ml-1">Reject
                                        <i class="las la-trash"></i>
                                    </button>
                                 </a>
                                 @endif
                                 @if($proposal->proposal_status=="Awarded")
                                 @foreach($owners as $owner)
                                 @if($proposal->owner_id==$owner->id)
                                <?
                                    $jsonAddress=json_decode($owner->address);
                                    $address=$jsonAddress->address.', '.$jsonAddress->city.', '.$jsonAddress->state.', '.$jsonAddress->zip.', '.$jsonAddress->country;
                                ?>
                                {{-- @if($proposal->payment_status=="Pending")
                                <a href="{{ route('user.proposal.markPaid', ['proposalId' => $proposal->id]) }}">
                                    <button class="p-1 btn--success ml-1">mark as paid<i class="las la-check-circle"></i>
                                     </button>
                                </a>
                                 <br/>
                                 <br/>
                                 @endif --}}
                                <button href="javascript:void(0)" onclick="OwnerDetails('{{$owner->firstname}}','{{$owner->lastname}}','{{$owner->email}}','{{$owner->mobile}}','{{$address}}')"
                                    class="p-1 btn--primary ml-1">supplier details<i class="las la-check-circle"></i>
                                 </button>
                                 <br/>
                                 <br/>
                                 <button href="javascript:void(0)" onclick="openStatusModal({{$proposal->id}})"
                                    class="p-1 btn--success ml-1">add a new status<i class="las la-check-circle"></i>
                                 </button>
                                 @endif
                                 @endforeach
                                 @endif
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <br/>
    <?}?>
@endsection

@push('modal')
<div id="addNewStatus" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Add a New Status')</h5>
                <button type="button" class="btn btn-sm btn--danger" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('user.quote.status.create') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <label>@lang('Status Type*')</label>
                    <div class="input-group has_append">
                        <select onchange="setStatusType(this.value)" style="width:100%">
                            <option value="">Select Status Type</option>
                            <option value="Commitment">Commitment</option>
                            <option value="Checked In">Checked In</option>
                            <option value="Checked Out">Checked Out</option>
                            <option value="Payment Completed">Payment Completed</option>
                        </select>
                        <input type="hidden" name="status_type">
                        <input type="hidden" name="quote_proposal_id">
                    </div>
                    <label>@lang('Document')</label>
                    <div class="input-group has_append">
                        <input type="file" name="status_document" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn--base">@lang('Save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('modal')
<div id="starModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Create a new Quote Request')</h5>
                <button type="button" class="btn btn-sm btn--danger" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('user.quote.request.create') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <label>@lang('Quote Title*')</label>
                    <div class="input-group has_append">
                        <input type="text" name="quote_title" class="form-control" placeholder="@lang('Enter Quote Title')">
                    </div>
                    <label>@lang('Quote Deadline*')</label>
                    <div class="input-group has_append">
                        <input type="date" value="<?=date('Y-m-d')?>" name="quote_deadline" class="form-control" >
                    </div>
                    <label>@lang('Quote Document(s)')</label>
                    <div class="input-group has_append">
                        <input type="file" name="quote_document[]" class="form-control" multiple>
                    </div>
                    <label>@lang('Locations*')</label>
                    <div class="input-group has_append">
                        <select id="locations" name="locations[]" multiple="multiple" class="js-example-basic-multiple" style="width:100%">
                            {{-- <option value="">Select Location</option> --}}
                            @foreach ($locations as $location)
                                <option value="{{ $location->name }}">{{$location->name}}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="quote_location">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn--base">@lang('Save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('modal')
<div id="showOwner" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Supplier Details')</h5>
                <button type="button" class="btn btn-sm btn--danger" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
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
@endpush

@push('style')
    <style>
        .select2-container{
            z-index:10000000 !important;
        }
        .select2-container--default .select2-results>.select2-results__options {
            max-height: 150px;
            overflow-y: auto;
        }
        .rate {
            float: left;
            height: 46px;
            padding: 0 10px;
        }
        .rate:not(:checked) > input {
            position:absolute;
            top:-9999px;
        }
        .rate:not(:checked) > label {
            float:right;
            width:1em;
            overflow:hidden;
            white-space:nowrap;
            cursor:pointer;
            font-size:30px;
            color:#ccc;
        }
        .rate:not(:checked) > label:before {
            content: '★ ';
        }
        .rate > input:checked ~ label {
            color: #ffc700;
        }
        .rate:not(:checked) > label:hover,
        .rate:not(:checked) > label:hover ~ label {
            color: #deb217;
        }
        .rate > input:checked + label:hover,
        .rate > input:checked + label:hover ~ label,
        .rate > input:checked ~ label:hover,
        .rate > input:checked ~ label:hover ~ label,
        .rate > label:hover ~ input:checked ~ label {
            color: #c59b08;
        }
        .description{
            margin-top: 50px;
        }
        .rating{
            color: #FFD700;
            font-size: 18px;
        }
        .icon-btn{
            background-color: #FFD700;
        }
    </style>
@endpush

@push('script')
<script>
    (function ($) {
        "use strict";
        $('.openNewQuote').click(function () {
            var modal = $('#starModal');
            modal.modal('show');
        });
    })(jQuery);

    openStatusModal =(quoteProposalId) => {
        $('input[name="quote_proposal_id"]').val(quoteProposalId);
        var modal = $('#addNewStatus');
        modal.modal('show');
    }

    OwnerDetails =  (fname,lname,email,contact,address) => {
        $('#fname').html(fname);
        $('#lname').html(lname);
        $('#email').html(email);
        $('#contact').html(contact);
        $('#address').html(address);
        var modal = $('#showOwner');
        modal.modal('show');
    }

    setLocation = () =>{
        var value="";
        var locations = $('#locations').select2('data');
        for(var i in locations){
            value+=locations[i]['text']+",";
        }
        $('input[name="quote_location"]').val(value);
    }

    setStatusType = (value) => {
        $('input[name="status_type"]').val(value);
    }

    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();

        $('#locations').on('select2:select', function (e) {
            setLocation();
        });

        $('#locations').on('select2:unselect', function (e) {
            // Run your script on unselection
            setLocation();
        });
    });
</script>
@endpush