@extends($activeTemplate.'layouts.master')
@section('content')
<div class="custom--card">
    <div class="card-header pb-3">
        <h6 class="d-inline">@lang('Your Quotes')</h6>
        <a href="javascript:void(0)" class="btn btn--base btn-sm float-end openNewQuote">@lang('Create a New Quote Request')</a>
    </div>
    <div class="card-body">
        <div class="table-responsive--md">
            <div class="card-header pb-3">
                <h6 class="d-inline">@lang('Property Waiting for Review')</h6>
             </div>
            <table class="table custom--table">
                <thead>
                    <tr>
                        <th>@lang('Message')</th>
                        <th>@lang('Document')</th>
                        <th>@lang('Created Date')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @forelse ($quoteProposals as $proposal) --}}
                    {{-- <tr>
                        <td data-label="@lang('Property Name')">{{ $proposal->proposal_message }}</td>
                        <td data-label="@lang('Property Name')">{{ $proposal->proposal_document }}</td>
                        <td data-label="@lang('Property Name')">{{ ($proposal->created_at) }}</td>
                        <td data-label="@lang('Action')">
                            <button class="icon-btn"><i class="las la-star"></i></button>
                        </td>
                    </tr> --}}
                    {{-- @empty --}}
                    <tr>
                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                    </tr>
                    {{-- @endforelse --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

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
</script>
@endpush
