@extends('layouts.app')

@section('content')

<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>@lang('lang_v1.add_smart_discounts')</h4>
        </div>
        <div class="card-body">
            {!! Form::open(['url' => action([\App\Http\Controllers\DiscountController::class, 'store_smart_discount']), 'method' => 'POST', 'id' => 'discount_form']) !!}
             @csrf
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('name', __('sale.discount_name') . ':') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('sale.discount_name')]); !!}
                    </div>
                </div>
                <div class="col-md-6">
         
                <div class="form-group">
                    {!! Form::label('start_date', __('sale.discount_start_date') . ':*') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        {!! Form::text('start_date', $default_datetime, ['class' => 'form-control datetimepicker', 'required']); !!}
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
                        {!! Form::text('end_date', $default_datetime, ['class' => 'form-control datetimepicker', 'required']); !!}
                    </div>
                </div>
                </div>
            	
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('business_id', __('sale.business_location') . ':') !!}
                        {!! Form::select('business_id[]', $locations, null, ['class' => 'form-control select2', 'required', 'multiple']); !!}
                    </div>
                </div>
               <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('user_id', __('sale.user') . ':') !!}
                        {!! Form::select('user_id[]', $users->mapWithKeys(function ($user) {
                            return [$user->id => $user->first_name . ' ' . $user->last_name];
                        }), null, ['class' => 'form-control select2', 'required', 'multiple']) !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('type_smart_root_discount_id', __('sale.discount_type') . ':') !!}
                        {!! Form::select('type_smart_root_discount_id', $discountTypes->pluck('name', 'id'), null, ['class' => 'form-control', 'required', 'placeholder' => __('sale.discount_type')]); !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <button type="button" class="btn btn-sm btn-primary" id="addFields">+</button>
                        <div id="additionalFields"></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</div>
@stop
<style>
    /* Styling for the description box */
.description-box {
    background-color: #f8f9fa; /* Light gray background */
    border: 1px solid #ced4da; /* Subtle border */
    border-radius: 5px; /* Rounded corners */
    padding: 10px; /* Spacing inside the box */
    min-height: 60px; /* Minimum height for consistency */
    display: flex; /* Align content properly */
    align-items: center; /* Vertically center the text */
    justify-content: flex-start; /* Align text to the left */
}


/* Styling for the description text */
.description-text {
    font-size: 14px; /* Font size */
    color: #495057; /* Dark gray text color */
    margin: 0; /* Remove default paragraph margin */
    line-height: 1.4; /* Improve readability */
}

/* Styling for dynamic values */
.dynamic-invoice-amount,
.dynamic-discount-amount {
    font-weight: bold; /* Highlight dynamic values */
    color: #007bff; /* Blue color for emphasis */
}
/* Styling for Condition and Result containers */
.condition-container,
.result-container {
    border: 2px solid #ced4da; /* Light gray border */
    border-radius: 8px; /* Rounded corners */
    padding: 15px; /* Padding inside the container */
    margin-bottom: 15px; /* Space between containers */
    background-color: #f8f9fa; /* Light background color */
}

/* Header styling for Condition and Result */
.condition-header,
.result-header {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 10px;
    color: #343a40; /* Dark text color */
}


</style>
@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
$(document).ready(function() {
    let fieldIndex = 0;
    let debounceTimeout;
  
 
    $('#addFields').on('click', function() {
        console.log('Add Fields button clicked');

    
        let selectedDiscountType = $('#type_smart_root_discount_id').val();
        let fieldGroupId = 'fieldGroup_' + fieldIndex;
        let fields = '';

        if (selectedDiscountType == 1) {
          
            fields = `
                <div class="form-group" id="${fieldGroupId}">
                <div class="row">
                    <div class="col-md-4">
                        {!! Form::label('', __('sale.invoice_amount')) !!}
                        {!! Form::text("invoice_amount[]", @num_format(0), [
                            'class' => 'form-control invoice-amount input_number', 
                            'required',
                        ]) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('', __('sale.discount_amount')) !!}
                        {!! Form::text("discount_amount[]", @num_format(0), [
                            'class' => 'form-control discount-amount input_number', 
                            'required',
                        ]) !!}
                    </div>
                    <div class="col-md-4">
                        <label>${"<?php echo __('sale.discount_status'); ?>"}</label>
                        <select name="discount_status_id[]" class="form-control discount-status">
                            <option value="">اختر حالة الخصم</option>
                            @foreach($discountStatus as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label>${"<?php echo __('sale.description'); ?>"}</label>
                        <div class="description-box">
                            <p class="description-text">
                                ${"<?php echo __('messages.Any_invoice_will_reach_total_amount_of'); ?>"}
                                <span class="dynamic-invoice-amount">@format_currency(0)</span> 
                                ${"<?php echo __('messages.The_smart_discount_will_be'); ?>"}
                                <span class="dynamic-discount-amount">@format_currency(0)</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-danger removeField" data-field-id="${fieldGroupId}">${"<?php echo __('messages.remove'); ?>"}</button>
                    </div>
                </div>
            </div>
            `;
        } else if (selectedDiscountType == 2) {
            fields = `
        <div class="form-group" id="${fieldGroupId}">
            <div class="row">
                <!-- Condition Section -->
                <div class="col-md-6">
                    <div class="condition-container">
                        <h5 class="condition-header"><?php echo __('messages.condition'); ?> : </h5>
                        <div class="condition-fields">
                            <div class="row condition-row">
                            <div class="col-md-4">
                                            {!! Form::label('condition_product_search', __('report.products') . ':') !!}
                                            <select name="condition_product_search[${fieldGroupId}][]" id="condition-product-search-${fieldGroupId}-0" class="form-control condition-product-search"></select>
                                        </div>
                                <div class="col-md-3">
                                    <label>${"<?php echo __('sale.quantity'); ?>"}</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-secondary decrement-btn">-</button>
                                        <input type="number" name="condition_quantity[${fieldGroupId}][]" class="form-control quantity text-center" value="1" min="1" readonly>
                                        <button type="button" class="btn btn-outline-secondary increment-btn">+</button>
                                    </div>
                                </div>
                            <div class="col-md-4">
                                <label>${"<?php echo __('sale.unit'); ?>"}</label>
                                <select name="condition_unit_id[${fieldGroupId}][]" class="form-control unit_id">
                                   
                                </select>
                            </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-success add-condition" style="margin-top: 30px;">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Result Section -->
                <div class="col-md-6">
                    <div class="result-container">
                        <h5 class="result-header"><?php echo __('messages.result'); ?> </h5>
                        <div class="result-fields">
                            <div class="row result-row">
                                 <div class="col-md-4">
                                            {!! Form::label('result_product_search', __('report.products') . ':') !!}
                                            <select name="result_product_search[${fieldGroupId}][]" id="result-product-search-${fieldGroupId}-0" class="form-control result-product-search"></select>
                                        </div>
                                <div class="col-md-3">
                                    <label>${"<?php echo __('sale.quantity'); ?>"}</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-secondary decrement-btn">-</button>
                                        <input type="number" name="result_quantity[${fieldGroupId}][]" class="form-control quantity text-center" value="1" min="1" readonly>
                                        <button type="button" class="btn btn-outline-secondary increment-btn">+</button>
                                    </div>
                                </div>
                                 <div class="col-md-4">
                                    <label>${"<?php echo __('sale.unit'); ?>"}</label>
                                   <select name="result_unit_id[${fieldGroupId}][]" class="form-control unit_id">
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->actual_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-success add-result" style="margin-top: 30px;">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Remove Button -->
                     <div class="col-md-12 mt-3">
                    <label>${"<?php echo __('sale.description'); ?>"}</label>
                    <div class="description-box">
                        <p class="description-text">
                            ${"<?php echo __('messages.Meaning_any_invoice_that_contains'); ?>"}
                            <span class="dynamic-condition-products">0</span> 
                            ${"<?php echo __('messages.You_will_get'); ?>"}
                            <span class="dynamic-result-products">0</span>
                        </p>
                    </div>
                </div>
                <div class="col-md-12 mt-2">
                    <button type="button" class="btn btn-sm btn-danger removeField" data-field-id="${fieldGroupId}">
                        ${"<?php echo __('messages.remove'); ?>"}
                    </button>
                    <button type="button" class="btn btn-sm btn-primary save-all-and-final" data-field-id="${fieldGroupId}" style="margin-left: 10px;">
                        ${"<?php echo __('messages.confirm'); ?>"}
                    </button>
                </div>
            </div>
        </div>
    `;
        }else if (selectedDiscountType == 3){
            fields = `
                <div class="form-group" id="${fieldGroupId}">
                    <div class="row">
                            <!-- Condition Section -->
                        <div class="col-md-6">
                            <div class="condition-container">
                                <h5 class="condition-header"><?php echo __('messages.condition'); ?></h5>
                                <div class="condition-fields">
                                    <div class="row condition-row">
                                  <div class="col-md-4">
                                            {!! Form::label('condition_product_search', __('report.products') . ':') !!}
                                            <select name="condition_product_search[${fieldGroupId}][]" id="condition-product-search-${fieldGroupId}-0" class="form-control condition-product-search"></select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>${"<?php echo __('sale.quantity'); ?>"}</label>
                                            <div class="input-group">
                                                <button type="button" class="btn btn-outline-secondary decrement-btn">-</button>
                                                <input type="number" name="condition_quantity[${fieldGroupId}][]" class="form-control quantity text-center" value="1" min="1" readonly>
                                                <button type="button" class="btn btn-outline-secondary increment-btn">+</button>
                                            </div>
                                        </div>
                                    <div class="col-md-4">
                                        <label>${"<?php echo __('sale.unit'); ?>"}</label>
                                        <select name="condition_unit_id[${fieldGroupId}][]" class="form-control unit_id">
                                            <option value="">Select Unit</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->actual_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Discount Section -->
                        <div class="col-md-6">
                             <div class="condition-container">
                                <h5 class="discount-header"><?php echo __('messages.result'); ?> </h5>
                                <div class="row discount-row">
                                    <div class="col-md-4">
                                       {!! Form::label('', __('sale.discount_amount')) !!}
                        {!! Form::text("discount_amount[]", @num_format(0), [
                            'class' => 'form-control discount-amount input_number', 
                            'required',
                        ]) !!}

                                    </div>
                                    <div class="col-md-4">
                                        <label>${"<?php echo __('sale.discount_status'); ?>"}</label>
                                        <select name="discount_status_id[]" class="form-control discount-status">
                                            <option value="">اختر حالة الخصم</option>
                                            @foreach($discountStatus as $status)
                                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div class="col-md-12">
                            <label>${"<?php echo __('sale.description'); ?>"}</label>
                            <div class="description-box">
                                <p class="description-text">
                                    ${"<?php echo __('messages.Meaning_any_invoice_that_contains'); ?>"}
                                    <span class="dynamic-invoice-amount">0</span> 
                                      ${"<?php echo __('messages.The_smart_discount_will_be'); ?>"}
                                    <span class="dynamic-discount-amount">0</span>
                                </p>
                            </div>
                        </div>
                    <div class="col-md-12 mt-2">
                        <button type="button" class="btn btn-sm btn-danger removeField" data-field-id="${fieldGroupId}">
                            ${"<?php echo __('messages.remove'); ?>"}
                        </button>
                          <button type="button" class="btn btn-sm btn-primary save-third-final-discount" data-field-id="${fieldGroupId}" style="margin-left: 10px;">
                              ${"<?php echo __('messages.confirm'); ?>"}
                         </button>
                    </div>
                </div>
                </div>
            `;
             
        }
            
            if (fields) {
            $('#additionalFields').append(fields);
            
            // Initialize select2 for the new fields
            $(`#condition-product-search-${fieldGroupId}-0`).select2({
                ajax: {
                    url: '/purchases/get_products?check_enable_stock=false&only_variations=true',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: data.map(item => ({
                                id: item.variation_id,
                                text: item.text
                            }))
                        };
                    },
                },
                minimumInputLength: 1,
                closeOnSelect: false
            }).on('change', function() {
                const variationId = $(this).val(); 
                const unitDropdown = $(this).closest('.condition-row').find('.unit_id');
                console.log('Product changed - Variation ID:', variationId);
                updateUnitDropdown(variationId, unitDropdown);
            }).on('select2:select', function() {
                $(this).select2('close');
            });
            
            $(`#result-product-search-${fieldGroupId}-0`).select2({
                ajax: {
                    url: '/purchases/get_products?check_enable_stock=false&only_variations=true',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: data.map(item => ({
                                id: item.variation_id,
                                text: item.text
                            }))
                        };
                    },
                },
                minimumInputLength: 1,
                closeOnSelect: false
            }).on('change', function() {
                const variationId = $(this).val();
                const unitDropdown = $(this).closest('.result-row').find('.unit_id');
                updateUnitDropdownForResult(variationId, unitDropdown);
            }).on('select2:select', function() {
                $(this).select2('close');
            });

            fieldIndex++;
        
        }
        
    });


    $(document).on('click', '.removeField', function() {
        let fieldGroupId = $(this).data('field-id');
        $('#' + fieldGroupId).remove();
    });



  // Faster typing effect function
        function fastTypingEffect(element, newText) {
            element.html(newText); // Immediate update without animation
        }

   $(document).on('input change', '.invoice-amount, .discount-amount, .discount-status', function() {
    let fieldGroupId = $(this).closest('.form-group').attr('id'); 
    clearTimeout(debounceTimeout); 

    debounceTimeout = setTimeout(() => {
        let invoiceAmount = parseFloat($(`#${fieldGroupId} .invoice-amount`).val().replace(/,/g, '')) || 0;
        let discountAmount = parseFloat($(`#${fieldGroupId} .discount-amount`).val().replace(/,/g, '')) || 0;
        let discountStatus = $(`#${fieldGroupId} .discount-status`).val();
     
        // Format the amounts before using them
        let formattedInvoiceAmount = formatCurrency(invoiceAmount);
        let formattedDiscountAmount = formatCurrency(discountAmount);

        if (invoiceAmount && discountAmount && discountStatus) {
               let descriptionText = getDescriptionText(formattedInvoiceAmount, formattedDiscountAmount, discountStatus);
               simulateTypingEffect($(`#${fieldGroupId} .description-text`), descriptionText);
        } else {
            $(`#${fieldGroupId} .dynamic-invoice-amount`).text('0');
            $(`#${fieldGroupId} .dynamic-discount-amount`).text('0');
        }
    }, 300);
});


function formatCurrency(amount) {
   
    const symbol = 'د.ع'; 
    const decimalSeparator = '.'; 
    const thousandSeparator = ',';
    const precision = 2; 
    const symbolBefore = true; 

    let formatted = parseFloat(amount).toFixed(precision)
        .replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator)
        .replace('.', decimalSeparator);

    return symbolBefore 
        ? symbol + ' ' + formatted 
        : formatted + ' ' + symbol;
}

// Updated description text function
function getDescriptionText(invoiceAmount, discountAmount, discountStatus) {
    if (discountStatus == 2) {
        return `<?php echo __('messages.Any_invoice_will_reach_total_amount_of'); ?> ${invoiceAmount} <?php echo __('messages.The_smart_discount_will_be'); ?> ${discountAmount} <?php echo __('messages.one_hundred_percent'); ?>`;
    } else {
        return `<?php echo __('messages.Any_invoice_will_reach_total_amount_of'); ?> ${invoiceAmount} <?php echo __('messages.The_smart_discount_will_be'); ?> ${discountAmount}`;
    }
}

    function simulateTypingEffect(descriptionElement, fullDescription) {
        console.log('Simulating typing effect:', fullDescription); 
        let index = 0;
        const typingSpeed = 10; 

        // Clear previous text
        descriptionElement.text('');

    
        const typingInterval = setInterval(() => {
            if (index < fullDescription.length) {
                descriptionElement.text(descriptionElement.text() + fullDescription.charAt(index));
                index++;
            } else {
                clearInterval(typingInterval);
            }
        }, typingSpeed);
    }
    $(document).on('click', '.add-condition', function() {
        let fieldGroupId = $(this).closest('.form-group').attr('id');
        let newId = `condition-product-search-new-${fieldGroupId}-${Date.now()}`;
    let conditionRow = `
        <div class="row condition-row mt-2">
            <div class="col-md-4">
                <select name="condition_product_search[${fieldGroupId}][]" 
                        id="${newId}"
                        class="form-control condition-product-search">
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <button type="button" class="btn btn-outline-secondary decrement-btn">-</button>
                    <input type="number" name="condition_quantity[${$(this).closest('.form-group').attr('id')}][]" class="form-control quantity text-center" value="1" min="1" readonly>
                    <button type="button" class="btn btn-outline-secondary increment-btn">+</button>
                </div>
            </div>
                 <div class="col-md-4">
                                <select name="condition_unit_id[${$(this).closest('.form-group').attr('id')}][]" class="form-control unit_id">
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->actual_name }}</option>
                                    @endforeach
                                </select>
                </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger remove-row" style="margin-top: 30px;">-</button>
            </div>
         
        </div>
    `;

    $(this).closest('.condition-fields').append(conditionRow);

    $(`#${newId}`).select2({
        ajax: {
            url: '/purchases/get_products?check_enable_stock=false&only_variations=true',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data.map(item => ({
                        id: item.variation_id,
                        text: item.text
                    }))
                };
            },
        },
        minimumInputLength: 1,
        closeOnSelect: false
    }).on('change', function() {
        const variationId = $(this).val();
        const unitDropdown = $(this).closest('.condition-row').find('.unit_id');
        updateUnitDropdown(variationId, unitDropdown);
    }).on('select2:select', function() {
                $(this).select2('close');
    });
});


$(document).on('click', '.add-result', function() {
    let fieldGroupId = $(this).closest('.form-group').attr('id');
    let newId = `result-product-search-new-${fieldGroupId}-${Date.now()}`;
    let resultRow = `
        <div class="row result-row mt-2">
           <div class="col-md-4">
                <select name="result_product_search[${fieldGroupId}][]" 
                        id="${newId}"
                        class="form-control result-product-search">
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <button type="button" class="btn btn-outline-secondary decrement-btn">-</button>
                    <input type="number" name="result_quantity[${$(this).closest('.form-group').attr('id')}][]" class="form-control quantity text-center" value="1" min="1" readonly>
                    <button type="button" class="btn btn-outline-secondary increment-btn">+</button>
                </div>
            </div>
                 <div class="col-md-4">
                                <select name="result_unit_id[${$(this).closest('.form-group').attr('id')}][]" class="form-control unit_id">
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->actual_name }}</option>
                                    @endforeach
                                </select>
                            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger remove-row" style="margin-top: 30px;">-</button>
            </div>
        </div>
    `;
    $(this).closest('.result-fields').append(resultRow);
    
        $(`#${newId}`).select2({
        ajax: {
            url: '/purchases/get_products?check_enable_stock=false&only_variations=true',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data.map(item => ({
                        id: item.variation_id,
                        text: item.text
                    }))
                };
            },
        },
        minimumInputLength: 1,
        closeOnSelect: false
    }).on('change', function() {
        const variationId = $(this).val();
        const unitDropdown = $(this).closest('.result-row').find('.unit_id');
        updateUnitDropdownForResult(variationId, unitDropdown);
    }).on('select2:select', function() {
                $(this).select2('close');
     });


});

    // Remove a specific row
    $(document).on('click', '.remove-row', function() {
        $(this).closest('.row').remove();
    });
function updateUnitDropdownForResult(variationId, unitDropdown) {
    // تحقق مما إذا كان variationId هو عنصر select بدلاً من ID
    if (typeof variationId === 'object' || typeof variationId === 'undefined') {
        console.error('Invalid variationId received:', variationId);
        unitDropdown.html('<option value="">Select Unit</option>');
        return;
    }

    console.log('Updating unit dropdown for variation ID:', variationId);
    unitDropdown.html('<option value="">Loading units...</option>');

    $.ajax({
        url: '/get_pecies_by_products/' + variationId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Response received:', response);
            
            unitDropdown.empty();
            
            if (response && response.length > 0) {
                // إضافة الخيار الأول كمحدد افتراضي
                unitDropdown.append(new Option(response[0].actual_name, response[0].id, true, true));
                
                // إضافة بقية الخيارات
                for (let i = 1; i < response.length; i++) {
                    unitDropdown.append(new Option(response[i].actual_name, response[i].id));
                }
                console.log('Units populated:', response.length);
            } else {
                console.warn('No units found for product ID:', variationId);
                unitDropdown.append('<option value="" selected>No units available</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching units for ID '+variationId+':', error);
            unitDropdown.html('<option value="" selected>Error loading units</option>');
        }
    });
}
// Function to update unit dropdown based on selected product variation
function updateUnitDropdown(variationId, unitDropdown) {
    // تحقق مما إذا كان variationId هو عنصر select بدلاً من ID
    if (typeof variationId === 'object' || typeof variationId === 'undefined') {
        console.error('Invalid variationId received:', variationId);
        unitDropdown.html('<option value="">Select Unit</option>');
        return;
    }

    console.log('Updating unit dropdown for variation ID:', variationId);
    unitDropdown.html('<option value="">Loading units...</option>');

    $.ajax({
        url: '/get_sub_units_by_product/' + variationId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Response received:', response);
            
            unitDropdown.empty();
            unitDropdown.append('<option value="">Select Unit</option>');
            
            if (response && response.length > 0) {
                response.forEach(function(unit) {
                    unitDropdown.append(new Option(unit.actual_name, unit.id));
                });
                console.log('Units populated:', response.length);
            } else {
                console.warn('No units found for product ID:', variationId);
                unitDropdown.append('<option value="">No units available</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching units for ID '+variationId+':', error);
            unitDropdown.html('<option value="">Error loading units</option>');
        }
    });
}

// Handle product selection change for condition products
$(document).on('change', '.condition-product-search', function() {
    const fieldGroup = $(this).closest('.condition-row');
    const unitDropdown = fieldGroup.find('.unit_id');
    updateUnitDropdown($(this), unitDropdown);
});

// Handle product selection change for result products
$(document).on('change', '.result-product-search', function() {
    const fieldGroup = $(this).closest('.result-row');
    const unitDropdown = fieldGroup.find('.unit_id');
    updateUnitDropdownForResult($(this), unitDropdown);
});

    




    function saveConditionData(conditionFields) {
    return new Promise((resolve, reject) => {
        let productIds = [];
        let quantities = [];
        let units = [];

        conditionFields.find('.condition-row').each(function() {
            let productSelect = $(this).find('.condition-product-search');
            let productId = productSelect.select2('data')[0]?.id;
            let quantity = $(this).find('.quantity').val();
            let unit_id = $(this).find('.unit_id').val();

            if (productId && quantity && unit_id) {
                // إذا كان الصف جديداً ولا يحتوي على ID
                if (!$(this).find('.condition-id').length) {
                    productIds.push(productId);
                    quantities.push(quantity);
                    units.push(unit_id);
                }
            }
        });

        if (productIds.length > 0) {
            $.ajax({
                url: '/add-sub-conditions', 
                method: 'POST',
                data: {
                    product_id: productIds,
                    quantity: quantities,
                    unit_id: units,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    let ids = response.ids;
                    let rows = conditionFields.find('.condition-row').filter(function() {
                        return !$(this).find('.condition-id').length;
                    });
                    
                    rows.each(function(index) {
                        if (ids[index]) {
                            $(this).append(`<input type="hidden" class="condition-id" name="condition_ids[]" value="${ids[index]}">`);
                        }
                    });
                    resolve(response);
                },
                error: function(error) {
                    reject(error);
                }
            });
        } else {
            resolve(); // لا توجد صفوف جديدة لحفظها
        }
    });
}

function saveResultData(resultFields) {
    return new Promise((resolve, reject) => {
        let productIds = [];
        let quantities = [];
        let units = [];

        resultFields.find('.result-row').each(function() {
            let productSelect = $(this).find('.result-product-search');
            let productId = productSelect.select2('data')[0]?.id;
            let quantity = $(this).find('.quantity').val();
            let unit_id = $(this).find('.unit_id').val();

            if (productId && quantity && unit_id) {
                if (!$(this).find('.result-id').length) {
                    productIds.push(productId);
                    quantities.push(quantity);
                    units.push(unit_id);
                }
            }
        });

        if (productIds.length > 0) {
            $.ajax({
                url: '/add-sub-results', 
                method: 'POST',
                data: {
                    product_id: productIds,
                    quantity: quantities,
                    unit_id: units,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    let ids = response.ids;
                    let rows = resultFields.find('.result-row').filter(function() {
                        return !$(this).find('.result-id').length;
                    });
                    
                    rows.each(function(index) {
                        if (ids[index]) {
                            $(this).append(`<input type="hidden" class="result-id" name="result_ids[]" value="${ids[index]}">`);
                        }
                    });
                    resolve(response);
                },
                error: function(error) {
                    reject(error);
                }
            });
        } else {
            resolve(); // لا توجد صفوف جديدة لحفظها
        }
    });
}

function saveFinalDiscount(fieldGroupId) {
    return new Promise((resolve, reject) => {
        let conditionIds = [];
        $(`#${fieldGroupId} .condition-row`).each(function() {
            let conditionId = $(this).find('.condition-id').val();
            if (conditionId) {
                conditionIds.push(conditionId);
            }
        });

        let resultIds = [];
        $(`#${fieldGroupId} .result-row`).each(function() {
            let resultId = $(this).find('.result-id').val();
            if (resultId) {
                resultIds.push(resultId);
            }
        });

        if (conditionIds.length > 0 && resultIds.length > 0) {
            $.ajax({
                url: '/add-final-discount',
                method: 'POST',
                data: {
                    condition_id: conditionIds,
                    result_id: resultIds,
                    _token: '{{ csrf_token() }}'
                }
            })
            .done(function(response) {
                let finalDiscountId = response.id; 
                $(`#${fieldGroupId}`).append(`<input type="hidden" class="final-discount-id" value="${finalDiscountId}">`);
                resolve(response);
            })
            .fail(function(error) {
                reject(error);
            });
        } else {
            resolve();
        }
    });
}

$(document).on('click', '.save-all-and-final', function() {
    let $button = $(this);
    
    // تحقق إذا كان الزر قد تم النقر عليه بالفعل
    if ($button.hasClass('confirmed')) {
        return;
    }
    
    let fieldGroupId = $button.closest('.form-group').attr('id');
    let conditionFields = $(`#${fieldGroupId} .condition-fields`);
    let resultFields = $(`#${fieldGroupId} .result-fields`);
    let hasErrors = false;

    // Validate condition fields
    conditionFields.find('.condition-row').each(function(index) {
        let productSelect = $(this).find('.condition-product-search');
        let selectedData = productSelect.select2('data');
        let productId = selectedData && selectedData.length > 0 ? selectedData[0].id : null;
        let quantity = $(this).find('.quantity').val();
        let unitId = $(this).find('.unit_id').val();
        
        if (!productId || !quantity || !unitId) {
            alert(`Please fill all fields in condition row ${index + 1}`);
            hasErrors = true;
            return false;
        }
    });

    if (hasErrors) return;

    // Validate result fields
    resultFields.find('.result-row').each(function(index) {
        let productSelect = $(this).find('.result-product-search');
        let selectedData = productSelect.select2('data');
        let productId = selectedData && selectedData.length > 0 ? selectedData[0].id : null;
        let quantity = $(this).find('.quantity').val();
        let unitId = $(this).find('.unit_id').val();
        
        if (!productId || !quantity || !unitId) {
            alert(`Please fill all fields in result row ${index + 1}`);
            hasErrors = true;
            return false;
        }
    });

    if (hasErrors) return;

    // Build the description text
    let conditionTexts = [];
    let resultTexts = [];
    
    conditionFields.find('.condition-row').each(function() {
        let productData = $(this).find('.condition-product-search').select2('data')[0];
        let productName = productData ? productData.text : 'Unknown Product';
        let quantity = $(this).find('.quantity').val();
        let unit = $(this).find('.unit_id option:selected').text();
        conditionTexts.push(`${quantity} ${productName} (${unit})`);
    });

    resultFields.find('.result-row').each(function() {
        let productData = $(this).find('.result-product-search').select2('data')[0];
        let productName = productData ? productData.text : 'Unknown Product';
        let quantity = $(this).find('.quantity').val();
        let unit = $(this).find('.unit_id option:selected').text();
        resultTexts.push(`${quantity} ${productName} (${unit})`);
    });

    const description = `<?php echo __('messages.Meaning_any_invoice_that_contains'); ?> ${conditionTexts.join(' + ')} <?php echo __('messages.You_will_get'); ?> ${resultTexts.join(' + ')}`;
    
    // Clear existing text and apply typing effect
    const descriptionElement = $(`#${fieldGroupId} .description-text`);
    descriptionElement.html('');
    
    // Apply typing effect
    simulateTypingEffect(descriptionElement, description);

    // وضع علامة أن الزر قد تم النقر عليه
    $button.addClass('confirmed');
    
    // If everything is valid, proceed with saving
    saveConditionData(conditionFields)
        .then(() => saveResultData(resultFields))
        .then(() => saveFinalDiscount(fieldGroupId))
        .then(() => {
            $(`#${fieldGroupId} .removeField`).hide();
            $button.addClass('btn-success').text('تم التأكيد');
        })
        .catch((error) => {
            console.error('Error:', error);
            descriptionElement.html('حدث خطأ أثناء الحفظ: ' + (error.responseJSON?.message || error.message));
            // إزالة علامة التأكيد في حالة الخطأ للسماح بالمحاولة مرة أخرى
            $button.removeClass('confirmed');
        });
});

// Typing effect function
function simulateTypingEffect(element, fullText, speed = 10) {
    let i = 0;
    element.text(''); // Clear existing text
    
    const typingInterval = setInterval(() => {
        if (i < fullText.length) {
            element.text(element.text() + fullText.charAt(i));
            i++;
        } else {
            clearInterval(typingInterval);
        }
    }, speed);
}

function validateFields(fields) {
    let isValid = true;
    fields.find('.condition-row, .result-row').each(function() {
        let productSearch = $(this).find('.condition-product-search, .result-product-search');
        let productId = productSearch.select2('data')[0]?.id;
        let quantity = $(this).find('.quantity').val();
        let unitId = $(this).find('.unit_id').val();
        
        if (!productId || !quantity || !unitId) {
            isValid = false;
            return false; // Break the loop
        }
    });
    return isValid;
}

function saveFinalDiscount(fieldGroupId) {
 
    let conditionIds = [];
    $(`#${fieldGroupId} .condition-row`).each(function() {
        let conditionId = $(this).find('.condition-id').val();
        if (conditionId) {
            conditionIds.push(conditionId);
        }
    });

    let resultIds = [];
    $(`#${fieldGroupId} .result-row`).each(function() {
        let resultId = $(this).find('.result-id').val();
        if (resultId) {
            resultIds.push(resultId);
        }
    });

    if (conditionIds.length > 0 && resultIds.length > 0) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: '/add-final-discount',
                method: 'POST',
                data: {
                    condition_id: conditionIds,
                    result_id: resultIds,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('Final discount saved:', response);
                    let finalDiscountId = response.id;
                    $(`#${fieldGroupId}`).append(`<input type="hidden" class="final-discount-id" value="${finalDiscountId}">`);
                    resolve(response);
                },
                error: function(error) {
                    console.error('Error saving final discount:', error);
                    reject(error);
                }
            });
        });
    } else {
        console.warn('No valid condition or result IDs to save.');
        return Promise.resolve();
    }
}


$(document).on('click', '.save-final-discount', function() {
    let fieldGroupId = $(this).data('field-id');


    let conditionIds = [];
    $(`#${fieldGroupId} .condition-row`).each(function() {
        let conditionId = $(this).find('.condition-id').val();
        if (conditionId) {
            conditionIds.push(conditionId);
        }
    });


    let resultIds = [];
    $(`#${fieldGroupId} .result-row`).each(function() {
        let resultId = $(this).find('.result-id').val();
        if (resultId) {
            resultIds.push(resultId);
        }
    });



    if (conditionIds.length > 0 && resultIds.length > 0) {
        $.ajax({
            url: '/add-final-discount', 
            method: 'POST',
            data: {
                condition_id: conditionIds,
                result_id: resultIds,
                _token: '{{ csrf_token() }}' 
            },
            success: function(response) {
                console.log('Final discount saved:', response);

               
                let finalDiscountId = response.id;
                $(`#${fieldGroupId}`).append(`<input type="hidden" class="final-discount-id" value="${finalDiscountId}">`);
            },
            error: function(error) {
                console.error('Error saving final discount:', error);
            }
        });
    } else {
        console.warn('No valid condition or result IDs to save.');
    }
    });

$(document).on('click', '.save-third-final-discount', function() {
    let fieldGroupId = $(this).data('field-id'); 
    let $button = $(this);
     if ($button.hasClass('confirmed')) {
        return;
    }
    let conditionFields = $(`#${fieldGroupId} .condition-fields`);
    let saveButton = $(this); // Store reference to the button
    
    saveConditionData(conditionFields)
        .then(() => {
            // Collect condition IDs
            let conditionIds = [];
            $(`#${fieldGroupId} .condition-row`).each(function() {
                let conditionId = $(this).find('.condition-id').val();
                if (conditionId) {
                    conditionIds.push(conditionId);
                }
            });

            if (conditionIds.length === 0) {
                console.log('Please add at least one condition.');
                return;
            }

            // Get discount data
            let discountAmount = $(`#${fieldGroupId} .discount-amount`).val();
            let discountStatusId = $(`#${fieldGroupId} .discount-status`).val();
            let discountStatusText = $(`#${fieldGroupId} .discount-status option:selected`).text();

            if (!discountAmount || isNaN(discountAmount)) {
                console.log('Please enter a valid discount amount.');
                return;
            }
            if (!discountStatusId) {
                console.log('Please select a discount status.');
                return;
            }

            // Build description text
            let conditionTexts = [];
            
            $(`#${fieldGroupId} .condition-row`).each(function() {
                let productData = $(this).find('.condition-product-search').select2('data')[0];
                let productName = productData ? productData.text : 'Unknown Product';
                let quantity = $(this).find('.quantity').val();
                let unit = $(this).find('.unit_id option:selected').text();
                conditionTexts.push(`${quantity} ${productName} (${unit})`);
            });

            // Format discount amount based on status
            let formattedDiscount;
            if (discountStatusId == 1) { // Fixed amount
                formattedDiscount = formatCurrency(parseFloat(discountAmount));
            } else if (discountStatusId == 2) { // Percentage
                formattedDiscount = `${discountAmount}%`;
            } else {
                formattedDiscount = discountAmount; // Fallback
            }

            // Format the description for type 3 discount
            const description = `<?php echo __('messages.Meaning_any_invoice_that_contains'); ?> ${conditionTexts.join(' + ')} <?php echo __('messages.The_smart_discount_will_be'); ?> ${formattedDiscount}`;
            
            // Update the description with typing effect
            const descriptionElement = $(`#${fieldGroupId} .description-text`);
            descriptionElement.html('');
            simulateTypingEffect(descriptionElement, description);
             $button.addClass('confirmed');

            // AJAX call to save the data
            $.ajax({
                url: '/add-third-final-discount',
                method: 'POST',
                data: {
                    condition_id: conditionIds,
                    discount_amount: discountAmount,
                    discount_status_id: discountStatusId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('Final discount saved:', response);
                    $(`#${fieldGroupId}`).append(`<input type="hidden" class="final-discount-id" value="${response.id}">`);
                    $(`#${fieldGroupId} .removeField`).hide();
                    saveButton.addClass('btn-success').text('تم التأكيد');
                },
                error: function(error) {
                    console.error('Error saving final discount:', error);
                    saveButton.addClass('btn-danger').text('خطأ في الحفظ');
                }
            });
        })
        .catch((error) => {
            console.error('Error saving conditions:', error);
            saveButton.addClass('btn-danger').text('خطأ في الحفظ');
             $button.removeClass('confirmed');
        });
});

    $(document).on('click', '.increment-btn', function() {
        let input = $(this).siblings('.quantity');
        let currentValue = parseInt(input.val());
        input.val(currentValue + 1);
    });

    $(document).on('click', '.decrement-btn', function() {
        let input = $(this).siblings('.quantity');
        let currentValue = parseInt(input.val());
        if (currentValue > 1) {
            input.val(currentValue - 1);
        }
    });

 

     $('#discount_form').on('submit', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation(); // Add this to prevent multiple submissions
    
    var $form = $(this);
    var $submitBtn = $form.find('button[type="submit"]');
    
    // Disable submit button to prevent multiple clicks
    $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    // Collect final discount IDs
    let finalDiscountIds = [];
    $('.form-group').each(function() {
        $(this).find('.final-discount-id').each(function() {
            let finalDiscountId = $(this).val();
            if (finalDiscountId) {
                finalDiscountIds.push(finalDiscountId);
            }
        });
    });

    // Remove any existing hidden inputs to avoid duplicates
    $form.find('input[name="final_discount_ids[]"]').remove();
    
    // Add final discount IDs to form
    $('<input>').attr({
        type: 'hidden',
        name: 'final_discount_ids[]',
        value: finalDiscountIds.join(',')
    }).appendTo($form);

    // Submit form via AJAX
    $.ajax({
        url: $form.attr('action'),
        method: $form.attr('method'),
        data: $form.serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success(response.msg);
                if (response.redirect_url) {
                    setTimeout(function() {
                        window.location.href = response.redirect_url;
                    }, 1500);
                }
            } else {
                toastr.error(response.msg);
            }
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON?.msg || __('messages.something_went_wrong'));
        },
        complete: function() {
            $submitBtn.prop('disabled', false).html('Save');
        }
    });
    
    return false; // Additional prevention
});
                });
</script>
@endsection