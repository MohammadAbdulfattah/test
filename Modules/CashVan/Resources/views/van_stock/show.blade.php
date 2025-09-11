<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			@if(!$sell_transfer->delete_stock)
				<h4 class="modal-title" id="modalTitle"> @lang('cashvan::stock.the_deleted_stock') (<b>@lang('purchase.ref_no'):</b> #{{ $sell_transfer->ref_no }})
			@else
				<h4 class="modal-title" id="modalTitle"> @lang('cashvan::stock.the_added_stock') (<b>@lang('purchase.ref_no'):</b> #{{ $sell_transfer->ref_no }})
			@endif

			
		    
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
				          <tr>
				            <td>{{ $loop->iteration }}</td>
				            <td>
				              {{ $sell_lines->product->name }}
				               @if( $sell_lines->product->type == 'variable')
				                - {{ $sell_lines->variations->product_variation->name}}
				                - {{ $sell_lines->variations->name}}
				               @endif
				               - {{ $sell_lines->variations->sub_sku}}
				            </td>
				            <td>{{ @format_quantity($sell_lines->quantity) }} </td>
				            <td>@if(!empty($sell_lines->sub_unit)) {{$sell_lines->quantity/$sell_lines->sub_unit->base_unit_multiplier}}  {{$sell_lines->sub_unit->short_name}} @else -- @endif</td>
				            <td class="@cannot('view_purchase_price') show_price_with_permission no-print @endcan">
				              <span class="display_currency " data-currency_symbol="true">{{ $sell_lines->unit_price_inc_tax * $sell_lines->quantity }}</span>
				            </td>
				          </tr>
				         
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
				          <th>@lang('purchase.purchase_total'):</th>
				          <td></td>
				          <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $sell_transfer->final_total }}</span></td>
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
					@if(!$sell_transfer->type=="sell")
						<div class="col-xs-12">
							<img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($sell_transfer->ref_no, 'C128', 2,30,array(39, 48, 54), true)}}">
						  </div>
					@endif
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