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
            'url' => action([Modules\CashVan\Http\Controllers\VanStockController::class, 'saveMainStock'],[$sell_transfer->id]) ,
            'method' => 'put',
            'id' => 'stock_transfer_form',
        ]) !!}
@component('components.widget', ['class' => 'box-solid'])
			<div class="row">
                
			 <div class="col-sm-4  @if(!auth()->user()->can('date.access')) hide @endif">
			 	<div class="form-group">
			 		{!! Form::label('transaction_date', __('messages.date') . ':*') !!}
			 		<div class="input-group">
			 			<span class="input-group-addon">
			 				<i class="fa fa-calendar"></i>
			 			</span>
			 			{!! Form::text('transaction_date', @format_datetime($sell_transfer->transaction_date), ['class' => 'form-control', 'readonly', 'required']); !!}
			 		</div>
			 	</div>
			 </div>
                
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('ref_no', __('purchase.ref_no').':') !!}
						{!! Form::text('ref_no', $sell_transfer->ref_no, ['class' => 'form-control', 'readonly']); !!}
					</div>
				</div>
				
				<div class="clearfix"></div>
				<div class="col-sm-6">
					<div class="form-group">
						{!! Form::label('location_id', __('lang_v1.location_from').':*') !!}
						{!! Form::select('location_id', $business_locations, $sell_transfer->location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'id' => 'location_id', 'disabled']); !!}
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						{!! Form::label('transfer_location_id', __('lang_v1.location_to').':*') !!}
						{!! Form::select('transfer_location_id', $business_locations, $purchase_transfer->location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'id' => 'transfer_location_id', 'disabled']); !!}
					</div>
				</div>
				
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
							{!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product_for_srock_adjustment', 'placeholder' => __('stock_adjustment.search_product')]); !!}
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
								<th class="col-sm-2 text-center"><i class="fa fa-trash" aria-hidden="true"></i></th>
							</tr>
						</thead>
						<tbody>
							@php
								$product_row_index = 0;
								$subtotal = 0;
							@endphp
							@foreach($products as $product)
								@include('cashvan::van_stock.partials.product_row', ['product' => $product, 'row_index' => $loop->index, 'sub_units' => !empty($product->unit_details) ? $product->unit_details : []])
								@php
									$product_row_index = $loop->index + 1;
									$subtotal += ($product->quantity_ordered*$product->last_purchased_price);
								@endphp
							@endforeach
						</tbody>
						<tfoot>
							<tr class="text-center show_price_with_permission"><td colspan="3"></td><td><div class="pull-right"><b>@lang('sale.total'):</b> <span id="total_adjustment">{{@num_format($subtotal)}}</span></div></td></tr>
						</tfoot>
					</table>
					<input type="hidden" id="product_row_index" value="{{$product_row_index}}">
					</div>
				</div>
			</div>
		</div>
	@endcomponent
	@component('components.widget', ['class' => 'box-solid'])
			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('additional_notes',__('purchase.additional_notes')) !!}
						{!! Form::textarea('additional_notes', $sell_transfer->additional_notes, ['class' => 'form-control', 'rows' => 3]); !!}
					</div>
				</div>
			</div>
			@php
				$final_total = $subtotal + $sell_transfer->shipping_charges;
			@endphp
			<div class="row">
				<div class="col-md-12 text-right show_price_with_permission">
					<input type="hidden" id="total_amount" name="final_total" value="{{$sell_transfer->final_total}}">
					<b>@lang('stock_adjustment.total_amount'):</b> <span id="final_total_text">{{@num_format($final_total)}}</span>
				</div>
				<br>
				<br>
				<div class="col-sm-12 text-center">
					<button type="submit" id="save_stock_transfer" class="btn btn-primary btn-big">@lang('messages.save')</button>
				</div>
			</div>
		@endcomponent
<!--box end-->
	{!! Form::close() !!}
</section>
@stop
@section('javascript')
    <script>
        $(document).ready(function() {
    
        if ($('#search_product_for_srock_adjustment').length > 0) {
            //Add Product
            $('#search_product_for_srock_adjustment')
                .autocomplete({
                    source: function(request, response) {
                        $.getJSON(
                            '/products/list',
                            {  term: request.term },
                            response
                        );
                    },
                    minLength: 2,
                    response: function(event, ui) {
                        if (ui.content.length == 1) {
                            ui.item = ui.content[0];
                            if (ui.item.enable_stock == 1) {
                                $(this)
                                    .data('ui-autocomplete')
                                    ._trigger('select', 'autocompleteselect', ui);
                                $(this).autocomplete('close');
                            }
                        } else if (ui.content.length == 0) {
                            swal(LANG.no_products_found);
                        }
                    },
                    focus: function(event, ui) {
                       
                    },
                    select: function(event, ui) {
                        
                            $(this).val(null);
                            stock_transfer_product_row(ui.item.variation_id);
                        
                    },
                })
                .autocomplete('instance')._renderItem = function(ul, item) {
                if (item.enable_stock != 1) {
                    return ul;
                } else {
                    var string = '<div>' + item.name;
                    if (item.type == 'variable') {
                        string += '-' + item.variation;
                    }
                    string += ' (' + item.sub_sku + ') </div>';
                    return $('<li>')
                        .append(string)
                        .appendTo(ul);
                }
            };
        }

        $(document).on('change', 'input.product_quantity', function() {
            update_table_row($(this).closest('tr'));
        });
        $(document).on('change', 'input.product_unit_price', function() {
            update_table_row($(this).closest('tr'));
        });

        $(document).on('click', '.remove_product_row', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    $(this)
                        .closest('tr')
                        .remove();
                    update_table_total();
                }
            });
        });

        //Date picker
       

        jQuery.validator.addMethod(
            'notEqual',
            function(value, element, param) {
                return this.optional(element) || value != param;
            },
            'Please select different location'
        );

        $('form#stock_transfer_form').validate({
            rules: {
                transfer_location_id: {
                    notEqual: function() {
                        return $('select#location_id').val();
                    },
                },
            },
        });
       
        $('#save_van_stock').click(function(e) {
           
                $('form#van_stock_form').submit();
          
        });
        stock_transfer_table = $('#stock_transfer_table').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader:false,
            
            ajax: '/stock-transfers',
            columnDefs: [
                {
                    targets: 1,
                    orderable: false,
                    searchable: false,
                },
            ],
            columns: [
               
                { data: 'final_total', name: 'final_total' },
                { data: 'action', name: 'action' },
            ],
            fnDrawCallback: function(oSettings) {
                __currency_convert_recursively($('#stock_transfer_table'));
            },
        });
        var detailRows = [];

        $('#stock_transfer_table tbody').on('click', '.view_stock_transfer', function() {
            var tr = $(this).closest('tr');
            var row = stock_transfer_table.row(tr);
            var idx = $.inArray(tr.attr('id'), detailRows);

            if (row.child.isShown()) {
                $(this)
                    .find('i')
                    .removeClass('fa-eye')
                    .addClass('fa-eye-slash');
                row.child.hide();

                // Remove from the 'open' array
                detailRows.splice(idx, 1);
            } else {
                $(this)
                    .find('i')
                    .removeClass('fa-eye-slash')
                    .addClass('fa-eye');

                row.child(get_stock_transfer_details(row.data())).show();

                // Add to the 'open' array
                if (idx === -1) {
                    detailRows.push(tr.attr('id'));
                }
            }
        });

        // On each draw, loop over the `detailRows` array and show any child rows
        stock_transfer_table.on('draw', function() {
            $.each(detailRows, function(i, id) {
                $('#' + id + ' .view_stock_transfer').trigger('click');
            });
        });

        //Delete Stock Transfer
        $(document).on('click', 'button.delete_stock_transfer', function() {
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
                                stock_transfer_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
    });

    function stock_transfer_product_row(variation_id) {
        var row_index = parseInt($('#product_row_index').val());
        $.ajax({
            method: 'POST',
            url: '/stock-adjustments/get_product_row',
            data: { row_index: row_index, variation_id: variation_id, type: 'stock_transfer',check_qty:'no'},
            dataType: 'html',
            success: function(result) {
                $('table#stock_adjustment_product_table tbody').append(result);
                update_table_total();
                $('#product_row_index').val(row_index + 1);
            },
        });
    }

    function update_table_total() {
        var table_total = 0;
        $('table#stock_adjustment_product_table tbody tr').each(function() {
            var this_total = parseFloat(__read_number($(this).find('input.product_line_total')));
            if (this_total) {
                table_total += this_total;
            }
        });

        $('span#total_adjustment').text(__number_f(table_total));

        

        $('span#final_total_text').text(__number_f(table_total));
        $('input#total_amount').val(table_total);
    }

    

    $(document).on('change', 'select.sub_unit', function() {
        var tr = $(this).closest('tr');
        var selected_option = $(this).find(':selected');
        var multiplier = parseFloat(selected_option.data('multiplier'));
        var allow_decimal = parseInt(selected_option.data('allow_decimal'));
        tr.find('input.base_unit_multiplier').val(multiplier);

        var base_unit_price = tr.find('input.hidden_base_unit_price').val();

        var unit_price = base_unit_price * multiplier;
        var unit_price_element = tr.find('input.product_unit_price');
        __write_number(unit_price_element, unit_price);
        
        var qty_element = tr.find('input.product_quantity');
        var base_max_avlbl = qty_element.data('qty_available');
        var error_msg_line = 'pos_max_qty_error';

        if (tr.find('select.lot_number').length > 0) {
            var lot_select = tr.find('select.lot_number');
            if (lot_select.val()) {
                base_max_avlbl = lot_select.find(':selected').data('qty_available');
                error_msg_line = 'lot_max_qty_error';
            }
        }
        qty_element.attr('data-decimal', allow_decimal);
        var abs_digit = true;
        if (allow_decimal) {
            abs_digit = false;
        }
        qty_element.rules('add', {
            abs_digit: abs_digit,
        });

        if (base_max_avlbl) {
            var max_avlbl = parseFloat(base_max_avlbl) / multiplier;
            var formated_max_avlbl = __number_f(max_avlbl);
            var unit_name = selected_option.data('unit_name');
            var max_err_msg = __translate(error_msg_line, {
                max_val: formated_max_avlbl,
                unit_name: unit_name,
            });
            qty_element.attr('data-rule-max-value', max_avlbl);
            qty_element.attr('data-msg-max-value', max_err_msg);
            qty_element.rules('add', {
                'max-value': max_avlbl,
                messages: {
                    'max-value': max_err_msg,
                },
            });
            qty_element.trigger('change');
        }
        qty_element.valid();
        update_table_row($(this).closest('tr'));
    });

    function update_table_row(tr) {
        var quantity = parseFloat(__read_number(tr.find('input.product_quantity')));
        var multiplier = 1;

        if (tr.find('select.sub_unit').length) {
            multiplier = parseFloat(
                tr.find('select.sub_unit')
                    .find(':selected')
                    .data('multiplier')
            );
        }
        quantity = quantity * multiplier;
        
        var unit_price = parseFloat(tr.find('input.hidden_base_unit_price').val());
        var row_total = 0;
        if (quantity && unit_price) {
            row_total = quantity * unit_price;
        }
        tr.find('input.product_line_total').val(__number_f(row_total));
        update_table_total();
    }

    function get_stock_transfer_details(rowData) {
        var div = $('<div/>')
            .addClass('loading')
            .text('Loading...');
        $.ajax({
            url: '/stock-transfers/' + rowData.DT_RowId,
            dataType: 'html',
            success: function(data) {
                div.html(data).removeClass('loading');
            },
        });

        return div;
    }

   
  
    $(document).on('shown.bs.modal', '.view_modal', function() {
        __currency_convert_recursively($('.view_modal'));
    });

    </script>
    <script type="text/javascript">
        __page_leave_confirmation('#stock_transfer_form');
    </script>
@endsection


@cannot('view_purchase_price')
    <style>
        .show_price_with_permission {
            display: none !important;
        }
    </style>
@endcannot
