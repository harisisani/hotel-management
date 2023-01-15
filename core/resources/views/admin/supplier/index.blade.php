@extends('admin.layouts.app')
<?
    $owners=array();
?>
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>@lang('Owner Id')</th>
                                <th>@lang('Requested By')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Created Date')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliersApproved as $supplier)
                                <tr>
                                    <td>{{ $supplier->owner_id }}</td>
                                    <td>{{ $supplier->request_title }}</td>
                                    <td>{{ $supplier->approval_status }}</td>
                                    <td>{{ showDateTime($supplier->created_at) }}</td>
                                    <td data-label="@lang('Action')">
                                        <? if ($supplier->approval_status=="Deactivate") {?>
                                        <a href="{{ route('admin.supplier.activeThis', ['requestId' => $supplier->id]) }}"
                                           class="icon-btn btn--success ml-1 removeModalBtn">Activate
                                            <i class="las la-check-circle"></i>
                                        </a>
                                        <?}else {?>
                                        <a href="{{ route('admin.supplier.deactiveThis', ['requestId' => $supplier->id]) }}"
                                           class="icon-btn btn--danger ml-1 removeModalBtn">Deactive
                                            <i class="las la-trash"></i>
                                        </a>
                                        <?}
                                        array_push($owners,$supplier->owner_id);
                                        ?>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">No active suppliers found</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
            </div><!-- card end -->
            <br/>
            <div class="d-flex p-3 bg--primary align-items-center">
                <div class="pl-3">
                    <h4 class="text--white">@lang('Supplier Requests')</h4>
                </div>
            </div>
            <br/>
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>@lang('Owner Id')</th>
                                <th>@lang('Requested By')</th>
                                <th>@lang('Requested Message')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Created Date')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliersRequested as $supplier)
                                <?if(!in_array($supplier->owner_id,$owners)){?>
                                <tr>
                                    <td>{{ $supplier->owner_id }}</td>
                                    <td>{{ $supplier->request_title }}</td>
                                    <td>{{ $supplier->request_message }}</td>
                                    <td>{{ $supplier->approval_status }}</td>
                                    <td>{{ showDateTime($supplier->created_at) }}</td>
                                    <td data-label="@lang('Action')">
                                        <a href="{{ route('admin.supplier.approveThis', ['requestId' => $supplier->id]) }}"
                                           class="icon-btn btn--success ml-1 removeModalBtn">Approve
                                            <i class="las la-check-circle"></i>
                                        </a>
                                        <a href="{{ route('admin.supplier.rejectThis', ['requestId' => $supplier->id]) }}"
                                           class="icon-btn btn--danger ml-1 removeModalBtn">Reject
                                            <i class="las la-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?}?>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">No suppliers requests found</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
            </div><!-- card end -->
        </div>
    </div>
@endsection
