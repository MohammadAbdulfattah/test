@extends('layouts.app')
@section('title', __('report.stock_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('report.stock_report')}}</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => action([\Modules\CashVan\Http\Controllers\VanStockController::class, 'getStockReport']), 'method' => 'get', 'id' => 'stock_report_filter_form' ]) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('van_id',  __('cashvan::cashvan.cashvan') . ':') !!}
                        {!! Form::select('van_id', $vans, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
               
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
   
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
                @include('cashvan::van_stock.partials.stock_report_table')
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
             var stock_report_cols = [
            { data: 'sku', name: 'variations.sub_sku' },
            { data: 'product', name: 'p.name' },
            { data: 'variation', name: 'variation' },
            { data: 'category_name', name: 'c.name' },
            { data: 'van_name', name: 'van.name' },
            { data: 'unit_price', name: 'variations.sell_price_inc_tax' },
            { data: 'stock', name: 'stock', searchable: false },
        ];
        if ($('th.stock_price').length) {
            stock_report_cols.push({ data: 'stock_price', name: 'stock_price', searchable: false });
            stock_report_cols.push({ data: 'stock_value_by_sale_price', name: 'stock_value_by_sale_price', searchable: false, orderable: false });
            stock_report_cols.push({ data: 'potential_profit', name: 'potential_profit', searchable: false, orderable: false });
        }

        stock_report_cols.push({ data: 'total_sold', name: 'total_sold', searchable: false });
        stock_report_cols.push({ data: 'total_transfered', name: 'total_transfered', searchable: false });
        stock_report_cols.push({ data: 'total_adjusted', name: 'total_adjusted', searchable: false });
       

        if ($('th.current_stock_mfg').length) {
            stock_report_cols.push({ data: 'total_mfg_stock', name: 'total_mfg_stock', searchable: false });
        }
    //Stock report table
    stock_report_table = $('#stock_report_table').DataTable({
        processing: true,
        fixedHeader:false,
        order: [[1, 'asc']],
        serverSide: true,
        scrollY: "75vh",
        scrollX:        true,
        scrollCollapse: true,
        ajax: {
            url: '/cashvan/van_stock_report',
            data: function(d) {
                d.van_id = $('#van_id').val();
               
            },
        },
        columns: stock_report_cols,
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#stock_report_table'));
        },
        "footerCallback": function ( row, data, start, end, display ) {
            var footer_total_stock = 0;
            var footer_total_sold = 0;
            var footer_total_transfered = 0;
            var total_adjusted = 0;
            var total_stock_price = 0;
            var footer_stock_value_by_sale_price = 0;
            var total_potential_profit = 0;
            var footer_total_mfg_stock = 0;
            for (var r in data){
                footer_total_stock += $(data[r].stock).data('orig-value') ? 
                parseFloat($(data[r].stock).data('orig-value')) : 0;

                footer_total_sold += $(data[r].total_sold).data('orig-value') ? 
                parseFloat($(data[r].total_sold).data('orig-value')) : 0;

                footer_total_transfered += $(data[r].total_transfered).data('orig-value') ? 
                parseFloat($(data[r].total_transfered).data('orig-value')) : 0;

                total_adjusted += $(data[r].total_adjusted).data('orig-value') ? 
                parseFloat($(data[r].total_adjusted).data('orig-value')) : 0;

                total_stock_price += $(data[r].stock_price).data('orig-value') ? 
                parseFloat($(data[r].stock_price).data('orig-value')) : 0;

                footer_stock_value_by_sale_price += $(data[r].stock_value_by_sale_price).data('orig-value') ? 
                parseFloat($(data[r].stock_value_by_sale_price).data('orig-value')) : 0;

                total_potential_profit += $(data[r].potential_profit).data('orig-value') ? 
                parseFloat($(data[r].potential_profit).data('orig-value')) : 0;

                footer_total_mfg_stock += $(data[r].total_mfg_stock).data('orig-value') ? 
                parseFloat($(data[r].total_mfg_stock).data('orig-value')) : 0;
            }

            $('.footer_total_stock').html(__currency_trans_from_en(footer_total_stock, false));
            $('.footer_total_stock_price').html(__currency_trans_from_en(total_stock_price));
            $('.footer_total_sold').html(__currency_trans_from_en(footer_total_sold, false));
            $('.footer_total_transfered').html(__currency_trans_from_en(footer_total_transfered, false));
            $('.footer_total_adjusted').html(__currency_trans_from_en(total_adjusted, false));
            $('.footer_stock_value_by_sale_price').html(__currency_trans_from_en(footer_stock_value_by_sale_price));
            $('.footer_potential_profit').html(__currency_trans_from_en(total_potential_profit));
            if ($('th.current_stock_mfg').length) {
                $('.footer_total_mfg_stock').html(__currency_trans_from_en(footer_total_mfg_stock, false));
            }
        },
    });
    $('#stock_report_filter_form #van_id'
    ).change(function() {
        stock_report_table.ajax.reload();
        stock_expiry_report_table.ajax.reload();
        get_stock_value();
    });

        });
        function get_stock_value() {
            var loader = __fa_awesome();
            $('#closing_stock_by_pp').html(loader);
            $('#closing_stock_by_sp').html(loader);
            $('#potential_profit').html(loader);
            $('#profit_margin').html(loader);
            var data = {
                van_id: $('#van_id').val(),
            
            }
            $.ajax({
                url: '/reports/get-stock-value',
                data: data,
                success: function(data) {
                    $('#closing_stock_by_pp').text(__currency_trans_from_en(data.closing_stock_by_pp));
                    $('#closing_stock_by_sp').text(__currency_trans_from_en(data.closing_stock_by_sp));
                    $('#potential_profit').text(__currency_trans_from_en(data.potential_profit));
                    $('#profit_margin').text(__currency_trans_from_en(data.profit_margin, false));
                },
            });
        }
    </script>
@endsection