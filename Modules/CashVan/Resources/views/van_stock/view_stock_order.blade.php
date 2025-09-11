@extends('layouts.app')
@section('title', __('cashvan::stock.add_van_stock'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('cashvan::stock.add_van_stock')</h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        {!! Form::open([
            'url' => action([Modules\CashVan\Http\Controllers\VanStockController::class, 'responseOnStockOrder'], [$sell_transfer->id]),
            'method' => 'get',
            'id' => 'stock_transfer_form',
        ]) !!}
        @component('components.widget', ['class' => 'box-solid'])
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('transaction_date', __('messages.date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                        {!! Form::text('ref_no', null, ['class' => 'form-control','disabled']) !!}
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('location_id', __('lang_v1.location_from') . ':*') !!}
                        {!! Form::select('location_id', $business_locations, $sell_transfer->location_id, [
                            'class' => 'form-control select2',
                            'placeholder' => __('messages.please_select'),
                            'id' => 'location_id',
                            'disabled',
                        ]) !!}
                    </div>
                </div>
                <input type="hidden" name="type" id="action_type">

            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-solid'])
            <div class="box-header">
                <h3 class="box-title">{{ __('stock_adjustment.search_products') }}</h3>
            </div>
            <div class="">
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-search"></i>
                                </span>
                                {!! Form::text('search_product', null, [
                                    'class' => 'form-control',
                                    'id' => 'search_product_for_srock_adjustment',
                                    'disabled',
                                    'placeholder' => __('stock_adjustment.search_product'),
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-condensed"
                                id="stock_adjustment_product_table">
                                <thead>
                                    <tr>
                                        <th class="col-sm-4 text-center">
                                            @lang('sale.product')
                                        </th>
                                        <th class="col-sm-2 text-center">
                                            @lang('sale.qty')
                                        </th>
                                        <th class="col-sm-2 text-center show_price_with_permission">
                                            @lang('sale.unit_price')
                                        </th>
                                        <th class="col-sm-2 text-center show_price_with_permission">
                                            @lang('sale.subtotal')
                                        </th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $product_row_index = 0;
                                        $subtotal = 0;
                                    @endphp
                                    @foreach ($products as $product)
                                        @include('cashvan::van_stock.partials.product_table_row', [
                                            'product' => $product,
                                            'row_index' => $loop->index,
                                            'sub_units' => !empty($product->unit_details)
                                                ? $product->unit_details
                                                : [],
                                        ])
                                        @php
                                            $product_row_index = $loop->index + 1;
                                            $subtotal += $product->quantity_ordered * $product->last_purchased_price;
                                        @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="text-center show_price_with_permission">
                                        <td colspan="3"></td>
                                        <td>
                                            <div class="pull-right"><b>@lang('sale.total'):</b> <span
                                                    id="total_adjustment">{{ @num_format($subtotal) }}</span></div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            <input type="hidden" id="product_row_index" value="{{ $product_row_index }}">
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-solid'])
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('additional_notes', __('purchase.additional_notes')) !!}
                        {!! Form::textarea('additional_notes', $sell_transfer->additional_notes, [
                            'class' => 'form-control',
                            'rows' => 3,
                        ]) !!}
                    </div>
                </div>
            </div>
            @php
                $final_total = $subtotal + $sell_transfer->shipping_charges;
            @endphp
            <div class="row">
                <div class="col-md-12 text-right show_price_with_permission">
                    <input type="hidden" id="total_amount" name="final_total" value="{{ $sell_transfer->final_total }}">
                    <b>@lang('stock_adjustment.total_amount'):</b> <span id="final_total_text">{{ @num_format($final_total) }}</span>
                </div>
                <br>
                <br>
                <div class="col-sm-12 text-center">
                    <button type="submit" id="save_stock_transfer" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white" onclick="document.getElementById('action_type').value='accept';">@lang('cashvan::stock.accept_on_order')</button>
                    <button type="submit" id="save_stock_transfer" class="tw-dw-btn tw-dw-btn-error tw-dw-btn-lg tw-text-white" onclick="document.getElementById('action_type').value='refuse';">@lang('cashvan::stock.refuse_order')</button>
                </div>
            </div>
        @endcomponent
        <!--box end-->
        {!! Form::close() !!}
    </section>
@stop
@section('javascript')
    <script src="{{ asset('js/stock_transfer.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        __page_leave_confirmation('#stock_transfer_form');
    </script>
    <script>
        $(document).ready(function () {
            $('#stock_adjustment_product_table tbody')
                .find('input, select, textarea, button')
                .prop('disabled', true);
        });
        </script>
@endsection
@cannot('view_purchase_price')
    <style>
        .show_price_with_permission {
            display: none !important;
        }
    </style>
@endcannot
