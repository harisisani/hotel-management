@extends('admin.layouts.app')
@section('panel')
    <?foreach($QuoteRequests as $value){
    $timestamp = showDateTime($value['created_at']);
    // $createdDate = date('F d, Y', $timestamp);    
    $deadline = date('F d, Y',strtotime($value['quote_deadline']));
    ?>
    <div style="background: white;" class="custom--card">
        <div class="card-body">
            <div class="table-responsive--md" style="overflow: auto;">
                <div class="card-header pb-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Quote</th>
                                <th scope="col">Created Date</th>
                                <th scope="col">Deadline</th>
                                <th scope="col">Location</th>
                                <th scope="col">Posted By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="row"><a target="_blank" href="https://booking.emphospitality.com/uploads/quote-files/<?=$value['quote_document']?>" class="d-inline"><?=$value["quote_title"]?></a></td>
                                <td scope="row"><?=$timestamp?></td>
                                <td scope="row"><?=$deadline?></td>
                                <td scope="row"><?=$value['quote_location']?></td>
                                <td>
                                    <a href="{{ route('admin.users.detail', $value['created_by_user_id']) }}" class="icon-btn" data-toggle="tooltip" title="" data-original-title="@lang('Details')">
                                        <i class="las la-desktop text--shadow"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <table class="table table--light style--two">
                    <thead>
                        <tr>
                            <th>@lang('Message')</th>
                            <th>@lang('Document')</th>
                            <th>@lang('Created Date')</th>
                            <th>@lang('Statuses')</th>
                            <th>@lang('Proposed By')</th>
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
                            <td>
                                <a href="{{ route('admin.owners.detail', $proposal->owner_id) }}" class="icon-btn" data-toggle="tooltip" title="" data-original-title="@lang('Details')">
                                    <i class="las la-desktop text--shadow"></i>
                                </a>
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