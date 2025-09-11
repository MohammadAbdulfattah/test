@extends('layouts.app')
@section('title', __('lang_v1.all_sales'))
 
@section('content')
@php 
    $colspan = 15;
    $enabled_modules = (array)(!empty(session('business.enabled_modules')) ? session('business.enabled_modules') : []);
@endphp
    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1  class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('sale.sells')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')])
            @include('sell.partials.sell_list_filters')
            @if ($payment_types)
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('payment_method', __('lang_v1.payment_method') . ':') !!}
                        {!! Form::select('payment_method', $payment_types, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
            @endif
            @if ($drivers && in_array('drivers', $enabled_modules))
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('driver_id', __('lang_v1.drivers') . ':') !!}
                        {!! Form::select('driver_id', $drivers, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
            @endif
            @if (!empty($sources))
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sell_list_filter_source', __('lang_v1.sources') . ':') !!}

                        {!! Form::select('sell_list_filter_source', $sources, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
            @endif
        @endcomponent
    @can('direct_sell.access')    
        <div class="tw-transition-all tw-mb-4 lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md tw-ring-gray-200">
            <div class="box-header with-border" style="cursor: pointer;">
            <h3 class="box-title tw-pt-2 tw-pb-2 tw-pl-2">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseStatus">
                                        <i class="fa fa-filter" aria-hidden="true"></i>
                    {{ __('lang_v1.change_status') }}
                </a>
            </h3>
        </div>
        <div id="collapseStatus" class="panel-collapse active tw-pt-4 tw-pb-4 collapse" aria-expanded="false" style="height: 32px;">
            <div class="box-body">
                @if (!empty($shipping_statuses))
                <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('current_status', __('lang_v1.current_status') . ':') !!}
                            {!! Form::select('current_status', $shipping_statuses, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.select_status'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('new_status', __('lang_v1.new_status') . ':') !!}
                            {!! Form::select('new_status', $shipping_statuses, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.select_status'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <button type="button" id="change_status_button" class="btn bg-primary" style="margin-top: 23px; border-radius: 4px; padding:5px; font-size: 16px;">
                                {{ __('lang_v1.change_status') }}
                            </button>
                        </div>
                    </div>
                        
                @endif        
                
            </div>
            
        </div>
    @endcan
    
        @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.all_sales')])
            @can('direct_sell.access')
                @slot('tool')
                    <div class="box-tools">
                        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                            href="{{ action([\App\Http\Controllers\SellController::class, 'create']) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg> @lang('messages.add')
                        </a>
                    </div>
                @endslot
            @endcan
            @if (auth()->user()->can('direct_sell.view') ||
                    auth()->user()->can('view_own_sell_only') ||
                    auth()->user()->can('view_commission_agent_sell'))
                @php
                    $custom_labels = json_decode(session('business.custom_labels'), true);
                @endphp
                <table class="table table-bordered table-striped ajax_view" id="sell_table">
                    <thead>
                        <tr>
                             <th>
                                
                            <input type="checkbox" id="check_all">
                           </th>
                            <th>@lang('messages.action')</th>
                            <th>@lang('messages.date')</th>
                            <th>@lang('sale.invoice_no')</th>
                            <th>@lang('sale.customer_name')</th>
                            <th>@lang('lang_v1.contact_no')</th>
                            <th>@lang('sale.location')</th>
                            <th>@lang('sale.payment_status')</th>
                            <th>@lang('lang_v1.payment_method')</th>
                            <th>@lang('sale.total_amount')</th>
                            <th>@lang('sale.total_paid')</th>
                            <th>@lang('lang_v1.sell_due')</th>
                            <th>@lang('lang_v1.sell_return_due')</th>
                            <th>@lang('lang_v1.shipping_status')</th>
                            <th>@lang('lang_v1.total_items')</th>
                            <th>@lang('lang_v1.types_of_service')</th>
                            <th>{{ $custom_labels['types_of_service']['custom_field_1'] ?? __('lang_v1.service_custom_field_1') }}
                            </th>
                            <th>{{ $custom_labels['sell']['custom_field_1'] ?? '' }}</th>
                            <th>{{ $custom_labels['sell']['custom_field_2'] ?? '' }}</th>
                            <th>{{ $custom_labels['sell']['custom_field_3'] ?? '' }}</th>
                            <th>{{ $custom_labels['sell']['custom_field_4'] ?? '' }}</th>
                            <th>@lang('lang_v1.added_by')</th>
                            @if(in_array('drivers', $enabled_modules))
                                <th>@lang('cashvan::cashvan.driver')</th>
                            @endif 
                            @if(in_array('drivers', $enabled_modules))
                                <th>@lang('lang_v1.assigned_at')</th>
                            @endif
                            <th>@lang('sale.sell_note')</th>
                            <th>@lang('sale.staff_note')</th>
                            <th>@lang('sale.shipping_details')</th>
                            <th>@lang('restaurant.table')</th>
                            <th>@lang('restaurant.service_staff')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    
                    <tfoot>
                       
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td ><strong>@lang('sale.total'):</strong></td>
                            <td ></td>
                            <td ></td>
                            <td ></td>
                            <td ></td>
                            <td ></td>
                            <td ></td>
                            <td class="footer_payment_status_count"></td>
                            <td class="payment_method_count"></td>
                            <td class="footer_sale_total"></td>
                            <td class="footer_total_paid"></td>
                            <td class="footer_total_remaining"></td>
                            <td class="footer_total_sell_return_due"></td> 
                            <td ></td>
                            <td ></td>
                            <td class="service_type_count"></td>
                            <td ></td>
                            <td ></td>
                            <td ></td>
                            <td ></td>
                            <td ></td>
                            <td ></td>
                            @if(in_array('drivers', $enabled_modules))
                             <td ></td>
                            @endif
                            @if(in_array('drivers', $enabled_modules))
                             <td ></td>
                            @endif
                            <td ></td>
                            <td ></td>
                            <td ></td>
                            <td ></td>
                            <td ></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="row">
                   
                    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent" id="print_multi">
                        @lang('lang_v1.print_invoice')
                    </button>
                
                    @if(in_array('drivers', $enabled_modules))
                        @can('sell.assign_to_drivers')
                            <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-neutral" id="assign_to_driver">
                                @lang('lang_v1.assign_to_driver')
                            </button>
                        @endcan
                        
                    @endif
                </div>
            @endif
        @endcomponent
    </section>
    <!-- /.content -->
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade driver_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
        </section> 

@stop

@section('javascript')
    <script type="text/javascript">
    let isRTL = document.documentElement.getAttribute('dir') === 'rtl';

let domLayout = isRTL
    // RTL: Buttons on the left, Search on top, Length on the right
    ? '<"row "<"col-sm-4"f><"col-sm-8"B><"col-sm-1"l>>tip'

    // LTR: Search above the Length, Buttons on the right
    : '<"row"<"col-sm-4"f><"col-sm-8"B><"col-sm-1"l>>tip';
        $(document).ready(function() {
            //Date range as a button
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    sell_table.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                sell_table.ajax.reload();
            });

            sell_table = $('#sell_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                
                
                "ajax": {
                    "url": "/sells",
                    "data": function(d) {
                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                        d.is_direct_sale = 1;

                        d.location_id = $('#sell_list_filter_location_id').val();
                        d.customer_id = $('#sell_list_filter_customer_id').val();
                        d.payment_status = $('#sell_list_filter_payment_status').val();
                        d.created_by = $('#created_by').val();
                        d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                        d.service_staffs = $('#service_staffs').val();
                        
                        if ($('#shipping_status').length) {
                            d.shipping_status = $('#shipping_status').val();
                        }

                    if ($('#sell_list_filter_source').length) {
                            d.source = $('#sell_list_filter_source').val();
                        }

                        if ($('#only_subscriptions').is(':checked')) {
                            d.only_subscriptions = 1;
                        }

                        if ($('#payment_method').length) {
                            d.payment_method = $('#payment_method').val();
                        }
                        if ($('#driver_id').length) {
                            d.driver_id = $('#driver_id').val();
                        }

                        d = __datatable_ajax_callback(d);
                    }
                },
                dom: domLayout,
                scrollY: "75vh",
                scrollX: true,
                
                scrollCollapse: true,
                columns: [ { 
                    data: 'checkbox',
                    orderable: false, 
                    searchable: false 
                   
                },{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'payment_methods',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'final_total',
                        name: 'final_total'
                    },
                    {
                        data: 'total_paid',
                        name: 'total_paid',
                        "searchable": false
                    },
                    {
                        data: 'total_remaining',
                        name: 'total_remaining'
                    },
                    {
                        data: 'return_due',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    {
                        data: 'total_items',
                        name: 'total_items',
                        "searchable": false
                    },
                    {
                        data: 'types_of_service_name',
                        name: 'tos.name',
                       visible:{{ empty($is_types_service_enabled) ? 'false' : 'true' }}
                    },
                    {
                        data: 'service_custom_field_1',
                        name: 'service_custom_field_1',
                        visible:{{ empty($is_types_service_enabled) ? 'false' : 'true' }}
                        
                    },
                    {
                        data: 'custom_field_1',
                        name: 'transactions.custom_field_1',
                        visible:{{ empty($custom_labels['sell']['custom_field_1']) ? 'false' : 'true' }}
                        
                    },
                    {
                        data: 'custom_field_2',
                        name: 'transactions.custom_field_2',
                        visible: {{ empty($custom_labels['sell']['custom_field_2']) ? 'false' : 'true' }}
                       
                    },
                    {
                        data: 'custom_field_3',
                        name: 'transactions.custom_field_3',
                        visible: {{ empty($custom_labels['sell']['custom_field_3']) ? 'false' : 'true' }}
                    },
                    {
                        data: 'custom_field_4',
                        name: 'transactions.custom_field_4',
                        visible: {{ empty($custom_labels['sell']['custom_field_4']) ? 'false' : 'true' }}
                    },
                    {
                        data: 'added_by',
                        name: 'u.first_name'
                    },
                    @if(in_array('drivers', $enabled_modules))
                        {
                            data: 'driver',
                            name: 'u.first_name'
                        },
                        {
                            data: 'assigned_at',
                            name: 'assigned_at'
                        },
                    @endif
                    {
                        data: 'additional_notes',
                        name: 'additional_notes'
                    },
                    {
                        data: 'staff_note',
                        name: 'staff_note'
                    },
                    {
                        data: 'shipping_details',
                        name: 'shipping_details'
                    },
                    {
                        data: 'table_name',
                        name: 'tables.name',
                        visible: {{ empty($is_tables_enabled) ? 'false' : 'true' }}
                    },
                    {
                        data: 'waiter',
                        name: 'ss.first_name',
                       visible: {{ empty($is_service_staff_enabled) ? 'false' : 'true' }}
                    },
                ],colReorder: true,initComplete: function() {
        
        var columnOrder = localStorage.getItem('columnOrder');
        if (columnOrder && columnOrder !== "undefined") {
            try {
                var order = JSON.parse(columnOrder);
                this.api().colReorder.order(order);  // Apply the column order
            } catch (e) {
                console.error("Failed to parse column order from localStorage:", e);
            }
        }

        
        this.api().on('column-reorder', function(e, settings, details) {
            
            if (details.order) {
                localStorage.setItem('columnOrder', JSON.stringify(details.order));
            }
        });
    },
                buttons: [ {
                    text: '<i class="fa fa-refresh" aria-hidden="true"></i> ' +'@lang('lang_v1.refresh')',
                    className: ' tw-dw-btn tw-dw-btn-outline tw-my-2',
                    action: function ( e, dt, node, config ) {
                        dt.ajax.reload(); 
                    }
                },{
                    extend: 'csv',
                    text: '<i class="fa fa-file-csv" aria-hidden="true"></i> ' + LANG.export_to_csv,
                    className: ' tw-dw-btn tw-dw-btn-outline tw-my-2 tw-rounded-none',
                    exportOptions: {
                        columns: ':visible',
                    },
                    footer: true,
                },
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel" aria-hidden="true"></i> ' + LANG.export_to_excel,
                    className: ' tw-dw-btn tw-dw-btn-outline tw-my-2 tw-rounded-none',
                    exportOptions: {
                        columns: ':visible',
                    },
                    footer: true,
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print" aria-hidden="true"></i> ' + LANG.print,
                    className: ' tw-dw-btn tw-dw-btn-outline tw-my-2 tw-rounded-none',
                    exportOptions: {
                        columns: ':visible',
                        stripHtml: true,
                    },
                    footer: true,
                    customize: function (win) {
                        if ($('.print_table_part').length > 0) {
                            $($('.print_table_part').html()).insertBefore(
                                $(win.document.body).find('table')
                            );
                        }
                        if ($(win.document.body).find('table.hide-footer').length) {
                            $(win.document.body).find('table.hide-footer tfoot').remove();
                        }
                        __currency_convert_recursively($(win.document.body).find('table'));
                    },
                },
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns" aria-hidden="true"></i> ' + LANG.col_vis,
                    className: ' tw-dw-btn tw-dw-btn-outline tw-my-2 tw-rounded-none',
                }],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#sell_table'));
                },
                "footerCallback": function(row, data, start, end, display) {
                    var footer_sale_total = 0;
                    var footer_total_paid = 0;
                    var footer_total_remaining = 0;
                    var footer_total_sell_return_due = 0;
                    for (var r in data) {
                        footer_sale_total += $(data[r].final_total).data('orig-value') ? parseFloat($(
                            data[r].final_total).data('orig-value')) : 0;
                        footer_total_paid += $(data[r].total_paid).data('orig-value') ? parseFloat($(
                            data[r].total_paid).data('orig-value')) : 0;
                        footer_total_remaining += $(data[r].total_remaining).data('orig-value') ?
                            parseFloat($(data[r].total_remaining).data('orig-value')) : 0;
                        footer_total_sell_return_due += $(data[r].return_due).find('.sell_return_due')
                            .data('orig-value') ? parseFloat($(data[r].return_due).find(
                                '.sell_return_due').data('orig-value')) : 0;
                    }

                    $('.footer_total_sell_return_due').html(__currency_trans_from_en(
                        footer_total_sell_return_due));
                    $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining));
                    $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid));
                    $('.footer_sale_total').html(__currency_trans_from_en(footer_sale_total));

                    $('.footer_payment_status_count').html(__count_status(data, 'payment_status'));
                    $('.service_type_count').html(__count_status(data, 'types_of_service_name'));
                    $('.payment_method_count').html(__count_status(data, 'payment_methods'));
                },
                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(6)').attr('class', 'clickable_td');
                }
            });
            
            $(document).on('change',
                '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs, #shipping_status, #sell_list_filter_source, #payment_method,#driver_id',
                function() {
                    sell_table.ajax.reload();
                });

            $('#only_subscriptions').on('ifChanged', function(event) {
                sell_table.ajax.reload();
            });
             $('#check_all').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('#sell_table tbody input[type="checkbox"]').prop('checked', isChecked);
            });
            $('#sell_table tbody').on('click', 'input.row_checkbox', function(e) {
            e.stopPropagation(); 
            });
        });
         $('#change_status_button').on('click', function() 
        {

            const currentStatus = $('#current_status').val();
            const newStatus = $('#new_status').val();
            const selectedRows = [];
            
            $('#sell_table tbody input.row_checkbox:checked').each(function() {
                selectedRows.push($(this).val());
            });
            if (selectedRows.length > 0 && currentStatus && newStatus) {
                $.ajax({
                    url: '/sells/update/shipping', // Your endpoint to update status
                    method: 'POST',
                    data: {
                        'transaction_ids[]': selectedRows,
                        current_status: currentStatus,
                        new_status: newStatus,
                        _token: '{{ csrf_token() }}' 
                    }, traditional: true,
                     success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    sell_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                });
            } else {
                toastr.error('@lang('lang_v1.select_one_row')'); 
        }
        });
       $('#print_multi').on('click', function() {
    const selectedRows = [];
    
    $('#sell_table tbody input.row_checkbox:checked').each(function() {
        selectedRows.push($(this).val());
    });

    if (selectedRows.length > 0) {
        $.ajax({
            url: '/sells/multi_print', 
            method: 'POST',
            data: {
                'transaction_ids[]': selectedRows,
                _token: '{{ csrf_token() }}'
            },
            traditional: true,
            success: function(result) {
                if (result.success) {
                    let allReceiptsHtml = '';

                    result.receipts.forEach(receipt => {
                        if (receipt.html_content) {
                            
                            allReceiptsHtml += `<div class="receipt" style="page-break-after: always;">
                                    
                                    ${receipt.html_content}
                                </div>`;
                            
                        }
                    });
                     $('#receipt_section').html(allReceiptsHtml);
                    __currency_convert_recursively($('#receipt_section')); // If needed

                    
                    __print_receipt('receipt_section');

                    
                    setTimeout(function() {
                        document.title = title;
                    }, 1200);

                    toastr.success(result.msg);
                    sell_table.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    } else {
         toastr.error('@lang('lang_v1.select_one_row')'); // Validation message
    }
});
        function pos_print(receipt) 
        {
   
            if (receipt.print_type == 'printer') {
                var content = receipt;
                content.type = 'print-receipt';

                //Check if ready or not, then print.
                if (socket != null && socket.readyState == 1) {
                    socket.send(JSON.stringify(content));
                } else {
                    initializeSocket();
                    setTimeout(function() {
                        socket.send(JSON.stringify(content));
                    }, 700);
                }

            } else if (receipt.html_content != '') {
                var title = document.title;
                if (typeof receipt.print_title != 'undefined') {
                    document.title = receipt.print_title;
                }

                
                $('#receipt_section').html(receipt.html_content);
                __currency_convert_recursively($('#receipt_section'));
                __print_receipt('receipt_section');

                setTimeout(function() {
                    document.title = title;
                }, 1200);
            }
        }

        $(document).on('click', '#assign_to_driver', function () {
            const selectedRows = [];
    
            $('#sell_table tbody input.row_checkbox:checked').each(function() {
                selectedRows.push($(this).val());
            });

            if (selectedRows.length > 0) {
            
            $.ajax({
                url: '/sells/driver', 
                type: 'GET',
                data: {
                'transaction_ids[]': selectedRows,
                
                },
                dataType: 'html',
                success: function (result) {
                    $('.driver_modal').html(result).modal('show');
                    
                },
                error: function () {
                    alert('Failed to load driver assignment modal.');
                }
            });
        } else {
            toastr.error('@lang('lang_v1.select_one_row')'); // Validation message
        }
        });
        
    </script>
       
   
  


<!-- JS -->

 
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection

