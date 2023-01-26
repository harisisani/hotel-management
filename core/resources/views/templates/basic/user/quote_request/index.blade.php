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
                                <th scope="col">Deadline</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="row"><a target="_blank" href="https://booking.emphospitality.com/uploads/quote-files/<?=$value['quote_document']?>" class="d-inline"><?=$value["quote_title"]?></a></td>
                                <td scope="row"><?=$timestamp?></td>
                                <td scope="row"><?=$deadline?></td>
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
                            <td data-label="@lang('Proposal Created')">
                                <strong>{{ ($proposal->proposal_status) }}</strong>
                            </td>
                            <td data-label="@lang('Action')">
                                @if($proposal->proposal_status=="Pending" || $proposal->proposal_status=="Rejected" )
                                <a href="{{ route('user.proposal.acceptThis', ['proposalId' => $proposal->id]) }}"
                                    class="p-1 btn--success ml-1">Accept
                                     <i class="las la-check-circle"></i>
                                 </a>
                                 @endif
                                 @if($proposal->proposal_status=="Pending")
                                 <a href="{{ route('user.proposal.rejectThis', ['proposalId' => $proposal->id]) }}"
                                    class="p-1 btn--danger ml-1">Reject
                                     <i class="las la-trash"></i>
                                 </a>
                                 @endif
                                 @if($proposal->proposal_status=="Accepted")
                                 @foreach($owners as $owner)
                                 @if($proposal->owner_id==$owner->id)
                                <?
                                    $jsonAddress=json_decode($owner->address);
                                    $address=$jsonAddress->address.', '.$jsonAddress->city.', '.$jsonAddress->state.', '.$jsonAddress->zip.', '.$jsonAddress->country;
                                ?>
                                <a href="javascript:void(0)" onclick="OwnerDetails('{{$owner->firstname}}','{{$owner->lastname}}','{{$owner->email}}','{{$owner->mobile}}','{{$address}}')"
                                    class="p-1 btn--primary ml-1">see supplier details
                                     <i class="las la-check-circle"></i>
                                 </a>
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
                    <label>@lang('Quote Title')</label>
                    <div class="input-group has_append">
                        <input type="text" name="quote_title" class="form-control" placeholder="@lang('Enter Quote Title')">
                    </div>
                    <label>@lang('Quote Deadline')</label>
                    <div class="input-group has_append">
                        <input type="date" value="<?=date('Y-m-d')?>" name="quote_deadline" class="form-control" >
                    </div>
                    <label>@lang('Quote Document')</label>
                    <div class="input-group has_append">
                        <input type="file" name="quote_document" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn--base">@lang('Submit')</button>
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

    OwnerDetails =  (fname,lname,email,contact,address) => {
        $('#fname').html(fname);
        $('#lname').html(lname);
        $('#email').html(email);
        $('#contact').html(contact);
        $('#address').html(address);
        var modal = $('#showOwner');
        modal.modal('show');
    }
</script>
@endpush