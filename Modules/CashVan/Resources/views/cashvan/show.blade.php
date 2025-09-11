<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		    <h4 class="modal-title" id="modalTitle"> @lang('cashvan::stock.van_stock')
		    </h4>
		</div>
		<div class="modal-body">
				<div class="row invoice-info">
				  <div class="col-sm-4 invoice-col">
				    @lang('lang_v1.location_from'):
				    <address>
				      <strong>{{ $location_details['sell']->name }}</strong>
				      
				      @if(!empty($location_details['sell']->landmark))
				        <br>{{$location_details['sell']->landmark}}
				      @endif

				      @if(!empty($location_details['sell']->city) || !empty($location_details['sell']->state) || !empty($location_details['sell']->country))
				        <br>{{implode(',', array_filter([$location_details['sell']->city, $location_details['sell']->state, $location_details['sell']->country]))}}
				      @endif

				      @if(!empty($sell_transfer->contact->tax_number))
				        <br>@lang('contact.tax_no'): {{$sell_transfer->contact->tax_number}}
				      @endif

				      @if(!empty($location_details['sell']->mobile))
				        <br>@lang('contact.mobile'): {{$location_details['sell']->mobile}}
				      @endif
				      @if(!empty($location_details['sell']->email))
				        <br>Email: {{$location_details['sell']->email}}
				      @endif
				    </address>
				  </div>

				  
				  <div class="col-sm-4 invoice-col">
				    <b>@lang('purchase.ref_no'):</b> #{{ $sell_transfer->ref_no }}<br/>
				    <b>@lang('messages.date'):</b> {{ @format_date($sell_transfer->transaction_date) }}<br/>
				  </div>
				</div>

				<br>
				<div class="row">
				  <div class="col-xs-12">
				    <div class="table-responsive">
				      <table class="table bg-gray">
				        <tr class="bg-green">
				          <th>#</th>
				          <th>@lang('sale.product')</th>
				          <th>@lang('sale.qty')</th>
				          <th>@lang('unit.units')</th>
				          <th class="@cannot('view_purchase_price') show_price_with_permission no-print @endcan">@lang('sale.subtotal')</th>
				        </tr>
				        @php 
				          $total = 0.00;
				        @endphp
				        @foreach($sell_transfer->sell_lines as $sell_lines)
						 @if($sell_lines->product_name)
				          <tr>
				            <td>{{ $loop->iteration }}</td>
				            <td>
				              {{ $sell_lines->product_name }}
				               @if( $sell_lines->product_type == 'variable')
				                - {{ $sell_lines->product_variation_name}}
				                - {{ $sell_lines->variation_name}}
				               @endif
				               - {{ $sell_lines->variation_sku}}
				               
				            </td>
				            <td>{{ @format_quantity($sell_lines->product_quantity) }} </td>
				            <td>@if(!empty($sell_lines->unit_short_name)){{$sell_lines->product_quantity/$sell_lines->unit_multiplier}} {{$sell_lines->unit_short_name}}@else -- @endif</td>
				            <td class="@cannot('view_purchase_price') show_price_with_permission no-print @endcan">
				              <span class="display_currency " data-currency_symbol="true">{{ $sell_lines->unit_price_inc_tax * $sell_lines->product_quantity }}</span>
				            </td>
				          </tr>
				          @php 
				            $total += ($sell_lines->unit_price_inc_tax * $sell_lines->product_quantity);
				          @endphp
						  @endif
				        @endforeach
				      </table>
				    </div>
				  </div>
				</div>
				<br>
				<div class="row">
				  
				  <div class="col-xs-12 col-md-6 col-md-offset-6">
				    <div class="table-responsive">
				      <table class="table">
				        <tr class="@cannot('view_purchase_price') show_price_with_permission no-print @endcan">
				          <th >@lang('purchase.net_total_amount'): </th>
				          <td></td>
				          <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $total }}</span></td>
				        </tr>
				       
				       
				      </table>
				    </div>
				  </div>
				</div>
				<div class="row">
					<div class="col-md-12">
						  <strong>{{ __('lang_v1.activities') }}:</strong><br>
						  @includeIf('activity_log.activities', ['activity_type' => 'sell'])
					  </div>
				  </div>
			
				<div class="row print_section">
				  <div class="col-xs-12">
				    <img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($sell_transfer->ref_no, 'C128', 2,30,array(39, 48, 54), true)}}">
				  </div>
				</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white no-print" aria-label="Print" 
			onclick="$(this).closest('div.modal-content').printThis();"><i class="fa fa-print"></i> @lang( 'messages.print' )
			</button>
			<button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
		</div>
	</div>
</div>