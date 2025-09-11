<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header mini_print">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h3 class="modal-title">@lang('lang_v1.root_smart_discounts')</h3>
        </div>

        <div class="modal-body">
            <p>@lang('lang_v1.smart_root_discount_type1') </p>
            <hr>

            <div class="row">
                <div class="col-sm-12">
                    <table class="table table-slim">
                        <thead>
                            <tr>
                                <th width="10%" class="text-right">@lang('sale.discount_name')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-center">@lang('messages.condition')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-left">@lang('messages.result')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-left">@lang('sale.discount_start_date')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-left">@lang('sale.discount_end_date')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                                @if($item['smartRootDiscount']->type_smart_root_discount_id == 1)
                                    <tr>
                                        <td class="text-right">{{ $item['smartRootDiscount']->name }}</td>
                                        <td class="text-center">→</td>
                                        <td class="text-right">@format_currency($item['statusDiscount']->invoice_amount)</td>
                                        <td class="text-center">→</td>
                                        @if($item['statusDiscount']->discount_status_id == 1)
                                            <td class="text-right">@format_currency($item['statusDiscount']->discount_amount)</td>
                                        @else 
                                            <td class="text-right">{{ $item['statusDiscount']->discount_amount }}%</td>
                                        @endif
                                        <td class="text-center">→</td>
                                        <td class="text-right">{{ \Carbon\Carbon::parse($item['smartRootDiscount']->start_date)->format('Y-m-d') }}</td>
                                        <td class="text-center">→</td>
                                        <td class="text-right">{{ \Carbon\Carbon::parse($item['smartRootDiscount']->end_date)->format('Y-m-d') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <hr>
            <p>@lang('lang_v1.smart_root_discount_type2') </p>
            <hr>

            <div class="row">
                <div class="col-sm-12">
                    <table class="table table-slim">
                        <thead>
                            <tr>
                                <th width="10%" class="text-right">@lang('sale.discount_name')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-center">@lang('messages.condition')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-left">@lang('messages.result')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-left">@lang('sale.discount_start_date')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-left">@lang('sale.discount_end_date')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                                @if($item['smartRootDiscount']->type_smart_root_discount_id == 2)
                                    <tr>
                                        <td class="text-right">{{ $item['smartRootDiscount']->name }}</td>
                                        <td class="text-center">→</td>
                                        <td class="text-center">
                                            @if($item['subConditions']->count() > 0)
                                                <ul class="list-unstyled">
                                                    @foreach($item['subConditions'] as $condition)
                                                        <li>
                                                            @if(!$loop->first) + @endif
                                                             {{ $condition->quantity }}  {{ $condition->varition->product->name ?? 'N/A' }} 
                                                            ({{ $condition->unit->actual_name ?? '' }})
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">→</td>
                                        <td class="text-left">
                                            @if($item['subResults']->count() > 0)
                                                <ul class="list-unstyled">
                                                    @foreach($item['subResults'] as $result)
                                                        <li>
                                                            @if(!$loop->first) + @endif
                                                           {{ $result->quantity }}  {{ $result->varition->product->name ?? 'N/A' }} 
                                                            ( {{ $result->unit->actual_name ?? '' }})
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">→</td>
                                        <td class="text-right">{{ \Carbon\Carbon::parse($item['smartRootDiscount']->start_date)->format('Y-m-d') }}</td>
                                        <td class="text-center">→</td>
                                        <td class="text-right">{{ \Carbon\Carbon::parse($item['smartRootDiscount']->end_date)->format('Y-m-d') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <hr>
            <p>@lang('lang_v1.smart_root_discount_type3') </p>
            <hr>

            <div class="row">
                <div class="col-sm-12">
                    <table class="table table-slim">
                        <thead>
                            <tr>
                                <th width="10%" class="text-right">@lang('sale.discount_name')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-center">@lang('messages.condition')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-left">@lang('messages.result')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-left">@lang('sale.discount_start_date')</th>
                                <th width="10%">&nbsp;</th>
                                <th width="10%" class="text-left">@lang('sale.discount_end_date')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                                @if($item['smartRootDiscount']->type_smart_root_discount_id == 3)
                                    <tr>
                                        <td class="text-right">{{ $item['smartRootDiscount']->name }}</td>
                                        <td class="text-center">→</td>
                                        <td class="text-center">
                                            @if($item['subConditions']->count() > 0)
                                                <ul class="list-unstyled">
                                                    @foreach($item['subConditions'] as $condition)
                                                        <li>
                                                            {{ $condition->varition->product->name ?? 'N/A' }} 
                                                            (Qty: {{ $condition->quantity }} {{ $condition->unit->actual_name ?? '' }})
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">→</td>
                                        <td class="text-left">
                                            @if($item['statusDiscount']->final_discount)
                                                @if($item['statusDiscount']->final_discount->discount_status_id == 1)
                                                    @format_currency($item['statusDiscount']->final_discount->discount_amount)
                                                @else
                                                    {{ $item['statusDiscount']->final_discount->discount_amount }}%
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">→</td>
                                        <td class="text-right">{{ \Carbon\Carbon::parse($item['smartRootDiscount']->start_date)->format('Y-m-d') }}</td>
                                        <td class="text-center">→</td>
                                        <td class="text-right">{{ \Carbon\Carbon::parse($item['smartRootDiscount']->end_date)->format('Y-m-d') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>