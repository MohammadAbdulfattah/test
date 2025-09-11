@extends('layouts.app')
@section('title', __('cashvan::stock.view_history'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('cashvan::stock.view_history')
            <small></small>
        </h1>
        <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('history_list_date', __('report.date_range') . ':') !!}
                    {!! Form::text('history_list_date', null, [
                        'placeholder' => __('lang_v1.select_a_date_range'),
                        'class' => 'form-control',
                        
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('type', __('lang_v1.type') . ':') !!}
                    {!! Form::select('type', $types, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.select_all'),
                    ]) !!}
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary', 'title' =>  __('cashvan::stock.view_history')])
            
        <table class="table table-bordered table-striped ajax_view" id="stock_history_table" style="width: 100%;">
            <thead>
                <tr>
                    <th>@lang('messages.action')</th>
                    <th>@lang('messages.date')</th>
                    <th>@lang('purchase.ref_no')</th>
                    <th>@lang('purchase.location')</th>
                    <th>@lang('lang_v1.type')</th>
                    <th>@lang('purchase.grand_total')</th>
                    <th>@lang('lang_v1.added_by')</th>
                </tr>
            </thead>
            <tfoot>
                <tr class="bg-gray font-17 text-center footer-total">
                    <td ><strong>@lang('sale.total'):</strong></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="footer_sell_total"></td>
                    <td></td>
                </tr>
           
        </table>
        @endcomponent

        <div class="modal fade product_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>



    </section>

    <section id="receipt_section" class="print_section"></section>

    <!-- /.content -->
@stop
@section('javascript')
  
<script>
    $(document).ready(function() {
        let pathSegments = window.location.pathname.split('/');
        let id = pathSegments[pathSegments.length - 1];
       
        //Date range as a button
        $('#history_list_date').daterangepicker(
            dateRangeSettings,
            function(start, end) {
                $('#history_list_date').val(start.format(moment_date_format) + ' ~ ' + end.format(
                    moment_date_format));
                stock_history_table.ajax.reload();
            }
        );
        $(document).on('change',
                '#type',
                function() {
                    stock_history_table.ajax.reload();
                });

        stock_history_table = $('#stock_history_table').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader:false,
            scrollY: "75vh",
            scrollX:        true,
            scrollCollapse: true,
            ajax: {
                url: '/cashvan/show/history/'+id,
                data: function(d) {
                    var start = '';
                    var end = '';
                    if ($('#history_list_date').val()) {
                        start = $('input#history_list_date')
                            .data('daterangepicker')
                            .startDate.format('YYYY-MM-DD');
                        end = $('input#history_list_date')
                            .data('daterangepicker')
                            .endDate.format('YYYY-MM-DD');
                    }
                    d.start_date = start;
                    d.end_date = end;
                    if ($('#type').length) {
                            d.type = $('#type').val();
                        }
                    d = __datatable_ajax_callback(d);
                },
            },
            aaSorting: [[1, 'desc']],
            columns: [
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'ref_no', name: 'ref_no' },
                { data: 'location_name', name: 'location_name',orderable: false, searchable: false  },
                { data: 'type', name: 'type' },
                { data: 'final_total', name: 'final_total' },
                { data: 'added_by', name: 'u.first_name' },
            ],
            fnDrawCallback: function(oSettings) {
                __currency_convert_recursively($('#stock_history_table'));
            }, "footerCallback": function ( row, data, start, end, display ) {
            var total_sell = 0;
           
            for (var r in data){
                total_sell += $(data[r].final_total).data('orig-value') ? 
                parseFloat($(data[r].final_total).data('orig-value')) : 0;
            }

            $('.footer_sell_total').html(__currency_trans_from_en(total_sell));
        },
            createdRow: function(row, data, dataIndex) {
                $(row)
                    .find('td:eq(5)')
                    .attr('class', 'clickable_td');
            },
        });
    });
       
</script>

@endsection
