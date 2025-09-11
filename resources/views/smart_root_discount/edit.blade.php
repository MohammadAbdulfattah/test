@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>@lang('lang_v1.edit_smart_discounts')</h4>
        </div>
        <div class="card-body">
            {!! Form::open(['url' => action([\App\Http\Controllers\DiscountController::class, 'update_smart_discount'], [$discount->id]), 'method' => 'PUT', 'id' => 'update_discount_form']) !!}
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('name', __('sale.discount_name') . ':') !!}
                        {!! Form::text('name', $discount->name, ['class' => 'form-control', 'required', 'placeholder' => __('sale.discount_name')]); !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('start_date', __('sale.discount_start_date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('start_date', $start_date, ['class' => 'form-control datetimepicker', 'required']); !!}
                        </div>
                    </div>
                </div>
               
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('end_date', __('sale.discount_end_date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('end_date', $end_date, ['class' => 'form-control datetimepicker', 'required']); !!}
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('business_id', __('sale.business_location') . ':') !!}
                        {!! Form::select('business_id[]', $locations, $businessLocations->pluck('id'), [
                            'class' => 'form-control select2',
                            'multiple' => 'multiple',
                            'required' => true,
                            'style' => 'width: 100%;'
                        ]) !!}      
                   </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('user_id', __('sale.user') . ':') !!}
                        {!! Form::select('user_id[]', $users, $selectedUserModels->pluck('id'), [
                            'class' => 'form-control select2',
                            'multiple' => 'multiple',
                            'required' => true,
                            'style' => 'width: 100%;'
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('type_smart_root_discount_id', __('sale.discount_type') . ':') !!}
                        {!! Form::select('type_smart_root_discount_id', $discountTypes, $discount->type_smart_root_discount_id, ['class' => 'form-control', 'required', 'placeholder' => __('sale.discount_type'), 'disabled']); !!}
                    </div>
                </div>
            </div>

             <!-- Existing fields will be loaded here based on discount type -->
                <div class="col-md-12">
                    <div id="existingFields">
                        @if($discount->type_smart_root_discount_id == 1)
                            <!-- Type 1: Invoice amount based discount -->
                            @foreach($statusDiscount as $smartDiscount)
                            <div class="form-group" id="fieldGroup_{{ $loop->index }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        {!! Form::label('', __('sale.invoice_amount')) !!}
                                        {!! Form::text("invoice_amount[]", @num_format($smartDiscount->invoice_amount), [
                                            'class' => 'form-control invoice-amount input_number', 
                                            'required',
                                        ]) !!}
                                    </div>
                                    <div class="col-md-4">
                                        {!! Form::label('', __('sale.discount_amount')) !!}
                                        {!! Form::text("discount_amount[]", @num_format($smartDiscount->discount_amount), [
                                            'class' => 'form-control discount-amount input_number', 
                                            'required',
                                        ]) !!}
                                    </div>
                                    <div class="col-md-4">
                                        <label>@lang('sale.discount_status')</label>
                                        <select name="discount_status_id[]" class="form-control discount-status">
                                            <option value="">@lang('sale.select_discount_status')</option>
                                            @foreach($discountStatus as $status)
                                                <option value="{{ $status->id }}" {{ $smartDiscount->discount_status_id == $status->id ? 'selected' : '' }}>
                                                    {{ $status->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label>@lang('sale.description')</label>
                                        <div class="description-box">
                                            <p class="description-text">
                                                @lang('messages.Any_invoice_will_reach_total_amount_of')
                                                <span class="dynamic-invoice-amount">{{ @num_format($smartDiscount->invoice_amount) }}</span> 
                                                @lang('messages.The_smart_discount_will_be')
                                                <span class="dynamic-discount-amount">{{ @num_format($smartDiscount->discount_amount) }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="smart_discount_ids[]" value="{{ $smartDiscount->id }}">
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            
             <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang( 'messages.update' )</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection
@section('javascript')
<script>
    $(document).ready(function() {
        // Initialize datetimepicker
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss'
        });

        // Handle form submission
        $('#update_discount_form').submit(function(e) {
            e.preventDefault();
            
            var form = $(this);
            var formData = form.serialize();
            
            $.ajax({
                method: form.attr('method'),
                url: form.attr('action'),
                data: formData,
                beforeSend: function() {
                    // Show loading indicator if needed
                },
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.msg);
                        window.location.href = "{{ route('smart_root_discounts') }}";
                    } else {
                        toastr.error(response.msg);
                    }
                },
                error: function(xhr) {
                    toastr.error("{{ __('messages.something_went_wrong') }}");
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
@endsection
