@extends('layouts.app')
@section('title', __('report.customer') . ' - ' . __('report.supplier') . ' ' . __('report.reports'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('report.customer')}} & {{ __('report.supplier')}} {{ __('report.reports')}}</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('cg_customer_group_id', __( 'lang_v1.customer_group_name' ) . ':') !!}
                        {!! Form::select('cnt_customer_group_id', $customer_group, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'cnt_customer_group_id']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('type', __( 'lang_v1.type' ) . ':') !!}
                        {!! Form::select('contact_type', $types, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'contact_type']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('cs_report_location_id', __( 'sale.location' ) . ':') !!}
                        {!! Form::select('cs_report_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'cs_report_location_id']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('scr_contact_id', __( 'report.contact' ) . ':') !!}
                        {!! Form::select('scr_contact_id', $contact_dropdown, null , ['class' => 'form-control select2', 'id' => 'scr_contact_id', 'placeholder' => __('lang_v1.all'), 'style' => 'width:100%']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('scr_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'scr_date_filter', 'readonly']); !!}
                    </div>
                </div>

            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="supplier_report_tbl">
                    <thead>
                        <tr>
                            <th>@lang('report.contact')</th>
                            <th>@lang('report.total_purchase')</th>
                            <th>@lang('lang_v1.total_purchase_return')</th>
                            <th>@lang('report.total_sell')</th>
                            <th>@lang('lang_v1.total_sell_return')</th>
                            <th>@lang('lang_v1.opening_balance_due')</th>
                            <th>@lang('report.total_due') &nbsp;&nbsp;<i class="fa fa-info-circle text-info no-print" data-toggle="tooltip" data-placement="bottom" data-html="true" data-original-title="{{ __('messages.due_tooltip')}}" aria-hidden="true"></i></th>
                            <th>@lang('lang_v1.last_payment')</th>
                            <th>@lang('lang_v1.last_payment_date')</th>
                            <th>@lang('lang_v1.days_count_from_last_payment')</th>
                            <th>@lang('lang_v1.last_transaction')</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td><strong>@lang('sale.total'):</strong></td>
                            <td><span class="display_currency" id="footer_total_purchase" data-currency_symbol ="true"></span></td>
                            <td><span class="display_currency" id="footer_total_purchase_return" data-currency_symbol ="true"></span></td>
                            <td><span class="display_currency" id="footer_total_sell" data-currency_symbol ="true"></span></td>
                            <td><span class="display_currency" id="footer_total_sell_return" data-currency_symbol ="true"></span></td>
                            <td><span class="display_currency" id="footer_total_opening_bal_due" data-currency_symbol ="true"></span></td>
                            <td><span class="display_currency" id="footer_total_due" data-currency_symbol ="true"></span></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
<style> 
/* General DataTable styles */
table.dataTable {
    width: 100%;
    margin: 0 auto;
    border-collapse: collapse;
}

/* Header row styling */
table.dataTable thead th {
    background-color: #ff4d4d; /* Red */
    color: white;
    font-weight: bold;
    padding: 10px;
    text-align: center;
    border: 1px solid #fff;
}

/* Alternating row colors */
table.dataTable tbody tr:nth-child(even) {
    background-color: #aaffaa; /* Light Green */
}
table.dataTable tbody tr:nth-child(odd) {
    background-color: #ffcc99; /* Light Orange */
}

/* Specific column styling (if needed) */
table.dataTable td.total_purchase {
    background-color: #aaffaa; /* Light Green */
}
table.dataTable td.total_invoice {
    background-color: #ffcc99; /* Light Orange */
}
table.dataTable td.due {
    background-color: #99ccff; /* Light Blue */
}
table.dataTable td.opening_balance_due {
    background-color: #cc99ff; /* Light Purple */
}

/* Hover effect */
table.dataTable tbody tr:hover {
    background-color: #88ff88; /* Darker Green on hover */
}

/* Table cell padding and alignment */
table.dataTable td {
    padding: 8px;
    text-align: center;
    border: 1px solid #ddd;
}
</style>
@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
@endsection