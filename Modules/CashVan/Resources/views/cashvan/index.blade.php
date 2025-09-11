@extends('layouts.app')
@section('title', __('cashvan::cashvan.all_cashvan'))

@section('content')
    @if (!empty($status))
        <div class="alert alert-{{ $status['success'] ? 'success' : 'danger' }}">
            {{ $status['msg'] }}
        </div>
    @endif
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('cashvan::cashvan.all_cashvan')
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('cashvan::cashvan.manage_cashvans')</small>
        </h1>
        <!-- <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                    <li class="active">Here</li>
                </ol> -->
    </section>
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('cashvan::cashvan.all_cashvan')])
            @can('cashvan.create')
                @slot('tool')
                    <div class="box-tools">

                        <a href="#cashvan_modal"
                            class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full btn-modal"
                            data-toggle="modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24V0H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg>
                            @lang('messages.add')
                        </a>
                    </div>
                @endslot
            @endcan
            @can('cashvan.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="cashvan_table">
                        <thead>
                            <tr>
                                <th>@lang('user.name')</th>
                                <th>@lang('cashvan::cashvan.license_plate')</th>
                                <th>@lang('cashvan::cashvan.color')</th>
                                <th>@lang('cashvan::cashvan.driver')</th>
                                <th>@lang('purchase.business_location')</th> 
                                <th>@lang('sale.status')</th> 
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>.
                </div>
            @endcan
        @endcomponent


        @include('cashvan::cashvan.create')
        <div class="modal fade" id="cashvan_edit_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" id="cashvan_edit_modal_body">
                    <!-- Content will be loaded here dynamically -->
                </div>
            </div>
        </div>
        <section id="receipt_section" class="print_section"></section>
    </section>
    <!-- /.content -->
@stop
@section('javascript')

	<script src="{{ asset('js/stock_transfer.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            var cashvan_table = $('#cashvan_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                ajax: '/cashvan/',
                columnDefs: [{
                    "targets": [1, 5],
                    "orderable": false,
                    "searchable": false
                }],
                "columns": [{
                        "data": "name"
                    },
                    {
                        "data": "license_plate"
                    },
                    {
                        "data": "color"
                    },
                    {
                        "data": "driver_name"
                    },
                    {
                        "data": "van_locations"
                    },
                    {
                        "data": "status"
                    },
                    {
                        "data": "action"
                    }
                ]

            });
            $('#cashvan_table tbody').on('click', 'input.row_checkbox', function(e) {
                e.stopPropagation();
            });
            $(document).on('click', 'button.delete_user_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_user,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        var data = $(this).serialize();
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    cashvan_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });


            });

        });
        $(document).on('click', '.edit_cashvan_button', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            $('#cashvan_edit_modal_body').html('<div class="p-4 text-center">@lang('messages.loading')...</div>');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {


                    $('#cashvan_edit_modal_body').html(response);
                },
                error: function() {
                    $('#cashvan_edit_modal_body').html(
                        '<div class="p-4 text-danger">@lang('messages.something_went_wrong')</div>');
                }
            });
        });
        $('#cashvan_edit_modal').on('shown.bs.modal', function() {
            $(this).find('select.select2').select2({
                dropdownParent: $('#cashvan_edit_modal'),
            });
        });
        $(document).on('click', 'a.delete_van', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).data('href');
                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        success: function(result) {
                            if (result.success) {
                                window.location.reload();

                                toastr.success(result.msg);
                                cashvan_table.ajax.reload();

                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
        $(document).on('click', 'a.delete_van_stock', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).data('href');
                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        success: function(result) {
                            if (result.success) {

                                toastr.success(result.msg);
                                cashvan_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                               
                            }
                        },
                    });
                }
            });
        });

      
   
    </script>

@if(session('receipts'))


    <script>
        $(document).ready(function () {
            let allReceiptsHtml = '';
            let receipts = @json(session('receipts'));

            receipts.forEach(receipt => {
                if (receipt.html_content) {
                    allReceiptsHtml += `
                        <div class="receipt" style="page-break-after: always;">
                            ${receipt.html_content}
                        </div>`;
                }
            });

            $('#receipt_section').html(allReceiptsHtml);

            

            // Trigger print
            $('#receipt_section').printThis();

                    
           
        });
    </script>
@endif
@endsection
