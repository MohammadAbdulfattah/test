var img_fileinput_setting = {
    showUpload: false,
    showPreview: true,
    browseLabel: LANG.file_browse_label,
    removeLabel: LANG.remove,
    previewSettings: {
        image: { width: 'auto', height: 'auto', 'max-width': '100%', 'max-height': '100%' },
    },
};

$('#upload_image').fileinput(img_fileinput_setting);

fileinput_setting = {
    showUpload: false,
    showPreview: false,
    browseLabel: LANG.file_browse_label,
    removeLabel: LANG.remove,
};

$('#upload_document_property').fileinput(fileinput_setting);

$(document).ready(function() {
    //Check if edit form then don't update price.
    // if (
    //     $('form#edit_sell_property_form').length == 0 &&
    //     $('form#edit_sell_property_form').length == 0
    // ) {
    //     __write_number($('.payment-amount').first(), '15');
    // }

    ////////sell property
    $('.paid_on').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });
    //     function calculateTotal() {
    //         var total = 0;

    //         // اجمع جميع القيم من الحقول التي تحمل الفئة 'subtotal'
    //         $('.subtotal').each(function() {
    //             var value = parseFloat($(this).val()) || 0; // تأكد من أن القيم رقمية
    //             total += value;
    //         });
    //         $('.price_total').text(total.toFixed(2));
    // <<<<<<< HEAD
    // =======
    //         $('#final_total_input').val(total.toFixed(2));

    // >>>>>>> dc83dd0f123f2f22717210a1bdbfa6c635a34de2
    //         if (
    //             $('form#edit_sell_property_form').length == 0 &&
    //             $('form#edit_sell_property_form').length == 0 &&
    //             $('form#sell_property_form').length != 0
    //         ) {
    //             $('input.payment-amount').val(total.toFixed(2));
    //             $('.paid_on')
    //                 .data('DateTimePicker')
    //                 .date(moment());
    //         }
    //     }

    function calculateTotal() {
        var total = 0;

        // اجمع جميع القيم من الحقول التي تحمل الفئة 'subtotal'
        $('.subtotal').each(function() {
            //delete ',' from number firstly
            var value = $(this).val().replace(/,/g, '');
            var value = parseFloat(value) || 0; 
            total += value;
        });
        $('.price_total').text(total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
        $('#final_total_input').val(total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
        if (
            $('form#edit_sell_property_form').length == 0 &&
            $('form#sell_property_form').length != 0
        ) {
            $('input.payment-amount').val(total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('.paid_on')
                .data('DateTimePicker')
                .date(moment());
        }
    }
    // استدعاء الحساب عند تحميل الصفحة لتحديث المجموع الأولي
    calculateTotal();

    if ($('#search_property').length) {
        // Add Product
        $('#search_property')
            .autocomplete({
                delay: 1000,
                source: function(request, response) {
                    var search_fields = [];
                    $('.search_fields:checked').each(function(i) {
                        search_fields[i] = $(this).val();
                    });

                    $.getJSON(
                        '/estates/properties-list',
                        {
                            location_id: $('select#select_location_id').val(),
                            term: request.term,
                        },
                        response
                    );
                },
                minLength: 2,
                response: function(event, ui) {
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];
                    } else if (ui.content.length == 0) {
                        toastr.error('لم يتم العثور على عقار مطابق');
                        $('input#search_property').select();
                    }
                },
                select: function(event, ui) {
                    var searched_term = $(this).val();

                    // Handle selection logic
                    var property = ui.item;
                    var property_id = ui.item.id;

                    $.ajax({
                        method: 'GET',
                        url: '/estates/properties/get_row/' + property_id,
                        dataType: 'json',
                        success: function(result) {
                            if (result.success) {
                                if ($('#row_' + property.id).length) {
                                    toastr.warning('تم اضافة هذا العقار بلفعل');
                                    return false;
                                }

                                console.log('result', result.html_content);
                                $('table#property_table tbody').append(result.html_content);
                                calculateTotal();
                            } else {
                                toastr.error(result.message || 'Unable to add property.');
                            }
                        },
                        error: function() {
                            toastr.error('Error fetching property row.');
                        },
                    });
                },
            })
            .autocomplete('instance')._renderItem = function(ul, item) {
            // Ensure that "item" is valid and has required properties
            if (item) {
                var string = '<div>' + item.name;

                // If selling price exists, include it in the display
                if (item.sell_price) {
                    string += ' (' + item.sku + ')' + '<br> Price: ' + parseFloat(item.sell_price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }

                string += '</div>';

                return $('<li>')
                    .append(string)
                    .appendTo(ul); // Append to list
            } else {
                return $('<li>')
                    .append('<div>No item found</div>')
                    .appendTo(ul);
            }
        };

        $(document).on('keyup change', '.discount, .row_discount_type, .sell_price', function() {
            // إعادة حساب subtotal لكل صف
            var propertyId = $(this)
                .closest('tr')
                .attr('id')
                .split('_')[1];
            var priceField = $('input[name="property[' + propertyId + '][price]"]');
            var discountField = $('input[name="property[' + propertyId + '][discount_value]"]');
            var discountTypeField = $(
                'select[name="property[' + propertyId + '][line_discount_type]"]'
            );
            var subtotalField = $('input[name="property[' + propertyId + '][subtotal]"]');

            // استخراج القيم
            //delete ',' from number firstly
            var price = priceField.val().replace(/,/g, '');
            var price = parseFloat(price) || 0;
            var discount = discountField.val().replace(/,/g, '');
            var discount = parseFloat(discount) || 0;
            var discountType = discountTypeField.val();
            console.log(
                'priceField',
                price,
                'discountField',
                discount,
                'discountTypeField',
                discountType,
                'propertyId',
                propertyId
            );

            // حساب الـ Subtotal
            var subtotal = 0;
            if (discountType === 'fixed') {
                subtotal = price - discount;
            } else if (discountType === 'percentage') {
                subtotal = price - price * (discount / 100);
            }
            subtotal = Math.max(subtotal, 0);
            subtotalField.val(subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));

            // إعادة حساب الإجمالي العام
            calculateTotal();
        });
        $('#property_table').on('click', '.remove-property', function() {
            $(this)
                .closest('tr')
                .remove();
            calculateTotal();
        });

        $('#select_location_id').change(function() {
            $('tr.property_row').remove();
            calculateTotal();
        });
    }

    /////////////////////
    $(document).on('click', 'a.view-property', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function(result) {
                $('#view_property_modal')
                    .html(result)
                    .modal('show');
                __currency_convert_recursively($('#view_property_modal'));
            },
        });
    });
    // تابع لتحقق من القيمة عند التهيئة
    function toggleReservationReason() {
        if ($('#status-select').val() === 'reserved') {
            $('#reservation-reason').show();
        } else {
            $('#reservation-reason')
                .hide()
                .find('input')
                .val(''); // إعادة تعيين القيمة عند الإخفاء
        }
    }

    // فحص القيمة عند تحميل الصفحة
    toggleReservationReason();

    // استماع لتغيير القيمة في القائمة المنسدلة
    $('#status-select').on('change', function() {
        toggleReservationReason();
    });
    if ($('textarea#description').length > 0) {
        tinymce.init({
            selector: 'textarea#description',
            height: 250,
        });
    }

    // $('button#submit-edit-sell-property, button#update-and-print').click(function(e) {
       
    //     //Check if product is present or not.
    //     const selectedvalue = $('#customer_id').val();
    //     if(!selectedvalue){
    //         e.preventDefault();
    //         toastr.warning('حقل العميل اجباري');
    //         $('#customer_id').focus();
    //         return false;

    //     }
    //     if ($('table#property_table tbody').find('.property_row').length <= 0) {
    //         toastr.warning('حقل العقارات اجباري');
    //         return false;
    //     }
    //     edit_sell_property_form = $('form#edit_sell_property_form');
    //     if (
    //         edit_sell_property_form.find('[name="payment_option"]:checked').val() ==
    //         'installment_payment'
    //     ) {
    //         let installment_total = 0;
    //         let pre_installment_date;
    //         let is_date_valid = true;
    //         let is_amount_valid = true;
    //         $('#installment_lines_table tbody tr').each(function(index, element) {
    //             element == this;
    //             if (index == 0) {
    //                 pre_installment_date = moment(
    //                     $(this)
    //                         .find('.installment_line_date')
    //                         .val(),
    //                     moment_date_format
    //                 );
    //             } else {
    //                 let curr = moment(
    //                     $(this)
    //                         .find('.installment_line_date')
    //                         .val(),
    //                     moment_date_format
    //                 );
    //                 if (pre_installment_date.isSameOrAfter(curr)) {
    //                     is_date_valid = false;
    //                 } else {
    //                     pre_installment_date = curr;
    //                 }
    //             }
    
    //             if (__read_number($(this).find('.installment_line_amount')) == 0) {
    //                 is_amount_valid = false;
    //             }
    
    //             installment_total += __read_number($(this).find('.installment_line_amount'));
    //         });
    
    //         if (!is_date_valid) {
    //             toastr.error(LANG.dates_must_be_in_line);
    //             return false;
    //         }
    
    //         if (!is_amount_valid) {
    //             toastr.error(LANG.amount_must_be_not_zero);
    //             return false;
    //         }
    
    //         if (
    //             installment_total !=
    //             parseFloat(
    //                 $('.price_total')
    //                     .text()
    //                     .trim()
    //             ) -
    //                 __read_number(edit_sell_property_form.find('#amount_0'))
    //         ) {
    //             toastr.error(LANG.transaction_total_not_equal_installment_lines);
    //             return false;
    //         }
    //     }


    //     edit_sell_property_form.submit();
    // });

    //     $('button#submit-sell-property, button#save-and-print').click(function(e) {
    // <<<<<<< HEAD
    // =======
        
    // >>>>>>> dc83dd0f123f2f22717210a1bdbfa6c635a34de2
    //         //Check if product is present or not.
    //         const selectedvalue = $('#customer_id').val();
    //         if(!selectedvalue){
    //             e.preventDefault();
    //             toastr.warning('حقل العميل اجباري');
    //             $('#customer_id').focus();
    //             return false;

    //         }
    //         if ($('table#property_table tbody').find('.property_row').length <= 0) {
    //             toastr.warning('حقل العقارات اجباري');
    //             return false;
    //         }


    //         var is_msp_valid = true;
            
    //         sell_property_form.submit();
        
    //     });
    $('button#submit-edit-sell-property, button#update-and-print').click(function(e) {

        //Check if product is present or not.
        const selectedvalue = $('#customer_id').val();
        if (!selectedvalue) {
            e.preventDefault();
            toastr.warning('حقل العميل اجباري');
            $('#customer_id').focus();
            return false;
        }
        if ($('table#property_table tbody').find('.property_row').length <= 0) {
            toastr.warning('حقل العقارات اجباري');
            return false;
        }
        edit_sell_property_form = $('form#edit_sell_property_form');
        if (
            edit_sell_property_form.find('[name="payment_option"]:checked').val() ==
            'installment_payment'
        ) {
            let installment_total = 0;
            let pre_installment_date;
            let is_date_valid = true;
            let is_amount_valid = true;
            $('#installment_lines_table tbody tr').each(function(index, element) {
                element == this;
                if (index == 0) {
                    pre_installment_date = moment(
                        $(this)
                            .find('.installment_line_date')
                            .val(),
                        moment_date_format
                    );
                } else {
                    let curr = moment(
                        $(this)
                            .find('.installment_line_date')
                            .val(),
                        moment_date_format
                    );
                    if (pre_installment_date.isSameOrAfter(curr)) {
                        is_date_valid = false;
                    } else {
                        pre_installment_date = curr;
                    }
                }

                if (__read_number($(this).find('.installment_line_amount')) == 0) {
                    is_amount_valid = false;
                }

                installment_total += __read_number($(this).find('.installment_line_amount'));
            });

            if (!is_date_valid) {
                toastr.error(LANG.dates_must_be_in_line);
                return false;
            }

            if (!is_amount_valid) {
                toastr.error(LANG.amount_must_be_not_zero);
                return false;
            }

            if (
                installment_total !=
                parseFloat(
                    $('.price_total')
                        .text() 
                        .replace(/,/g, '') 
                        .trim() 
                ) -
                __read_number(edit_sell_property_form.find('#amount_0'))
            ) {
                toastr.error(LANG.transaction_total_not_equal_installment_lines);
                return false;
            }
            
        }

        edit_sell_property_form.submit();
    });
    $('button#submit-sell-property, button#save-and-print').click(function(e) {
        //Check if product is present or not.
        const selectedvalue = $('#customer_id').val();
        if (!selectedvalue) {
            e.preventDefault();
            toastr.warning('حقل العميل اجباري');
            $('#customer_id').focus();
            return false;
        }
        if ($('table#property_table tbody').find('.property_row').length <= 0) {
            toastr.warning('حقل العقارات اجباري');
            return false;
        }
        sell_property_form = $('form#sell_property_form');

        var is_msp_valid = true;
        if (
            sell_property_form.find('[name="payment_option"]:checked').val() ==
            'installment_payment'
        ) {
            let installment_total = 0;
            let pre_installment_date;
            let is_date_valid = true;
            let is_amount_valid = true;
            $('#installment_lines_table tbody tr').each(function(index, element) {
                element == this;
                if (index == 0) {
                    pre_installment_date = moment(
                        $(this)
                            .find('.installment_line_date')
                            .val(),
                        moment_date_format
                    );
                } else {
                    let curr = moment(
                        $(this)
                            .find('.installment_line_date')
                            .val(),
                        moment_date_format
                    );
                    if (pre_installment_date.isSameOrAfter(curr)) {
                        is_date_valid = false;
                    } else {
                        pre_installment_date = curr;
                    }
                }

                if (__read_number($(this).find('.installment_line_amount')) == 0) {
                    is_amount_valid = false;
                }

                installment_total += __read_number($(this).find('.installment_line_amount'));
            });

            if (!is_date_valid) {
                toastr.error(LANG.dates_must_be_in_line);
                return false;
            }

            if (!is_amount_valid) {
                toastr.error(LANG.amount_must_be_not_zero);
                return false;
            }

            if (
                installment_total !=
                parseFloat(
                    $('.price_total')
                        .text()
                        .replace(/,/g, '') 
                        .trim()
                ) -
                    __read_number(sell_property_form.find('#amount_0'))
            ) {
                toastr.error(LANG.transaction_total_not_equal_installment_lines);
                return false;
            }
        }

        sell_property_form.submit();
    });

    //Sell Payment Report
    sell_payment_report = $('table#sell_payment_report_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[2, 'desc']],
        ajax: {
            url: '/estates/property-selles/payments-reports',
            data: function(d) {
                d.created_by_id = $('select#created_by_id').val();
                d.customer_id = $('select#customer_id').val();
                d.location_id = $('select#location_id').val();
                d.payment_types = $('select#payment_types').val();
                var start = '';
                var end = '';
                if ($('input#spr_date_filter').val()) {
                    start = $('input#spr_date_filter')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');
                    end = $('input#spr_date_filter')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                }
                d.start_date = start;
                d.end_date = end;
                if ($('#paid_not_today').is(':checked')) {
                    d.paid_not_today = 1;
                }
            },
        },
        columns: [
            {
                orderable: false,
                searchable: false,
                data: null,
                defaultContent: '',
            },
            { data: 'payment_ref_no', name: 'payment_ref_no' },
            { data: 'paid_on', name: 'paid_on' },
            { data: 'amount', name: 'transaction_payments.amount' },
            { data: 'customer', orderable: false, searchable: false },
            { data: 'method', name: 'method' },
            { data: 'created_by', name: 'created_by' },
            { data: 'invoice_no', name: 't.invoice_no' },
            { data: 'action', orderable: false, searchable: false },
        ],
        fnDrawCallback: function() {
            var total_amount = sum_table_col($('#sell_payment_report_table'), 'paid-amount');
            $('#footer_total_amount').text(total_amount);
            __currency_convert_recursively($('#sell_payment_report_table'));
        },
        createdRow: function(row, data) {
            if (!data.transaction_id) {
                $(row)
                    .find('td:eq(0)')
                    .addClass('details-control');
            }
        },
    });
    // Array to track the ids of the details displayed rows
    var spr_detail_rows = [];

    $('#sell_payment_report_table tbody').on('click', 'tr td.details-control', function() {
        var tr = $(this).closest('tr');
        var row = sell_payment_report.row(tr);
        var idx = $.inArray(tr.attr('id'), spr_detail_rows);

        if (row.child.isShown()) {
            tr.removeClass('details');
            row.child.hide();

            // Remove from the 'open' array
            spr_detail_rows.splice(idx, 1);
        } else {
            tr.addClass('details');

            row.child(show_child_payments(row.data())).show();

            // Add to the 'open' array
            if (idx === -1) {
                spr_detail_rows.push(tr.attr('id'));
            }
        }
    });
    [];

    if ($('#spr_date_filter').length == 1) {
        $('#spr_date_filter').daterangepicker(dateRangeSettings, function(start, end) {
            $('#spr_date_filter span').val(
                start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
            );
            sell_payment_report.ajax.reload();
        });
        $('#spr_date_filter').on('cancel.daterangepicker', function(ev, picker) {
            $('#spr_date_filter').val('');
            sell_payment_report.ajax.reload();
        });
    }

    $(
        '#sell_payment_report_form #location_id, #sell_payment_report_form #customer_id, #sell_payment_report_form #payment_types, #sell_payment_report_form #customer_group_filter, #created_by_id'
    ).change(function() {
        sell_payment_report.ajax.reload();
    });

    $('.property_contact_modal').on('shown.bs.modal', function(e) {

        // for assign to select 
        $('.property_contact_modal').find('.select2').each(function() {
            $(this).select2();
        });

        // for submit add and edit modal with validate contact_id and phone
        $('form#property_contact_add_form, form#property_contact_edit_form').submit(function(e) {
            e.preventDefault();
        }).validate({
            rules: {
                contact_id: {
                    remote: {
                        url: '/contacts/check-contacts-id',
                        type: 'post',
                        data: {
                            contact_id: function() {
                                return $('#contact_id').val();
                            },
                            hidden_id: function() {
                                if ($('#hidden_id').length) {
                                    return $('#hidden_id').val();
                                } else {
                                    return '';
                                }
                            },
                        },
                    },
                },
            },
            messages: {
                contact_id: {
                    remote: LANG.contact_id_already_exists,
                },
            },
            submitHandler: function(form) {
                e.preventDefault();
                $.ajax({
                    method: 'POST',
                    url: base_path + '/check-mobile',
                    dataType: 'json',
                    data: {
                        contact_id: function() {
                            return $('#hidden_id').val();
                        },
                        mobile_number: function() {
                            return $('#mobile').val();
                        },
                    },
                    beforeSend: function(xhr) {
                        __disable_submit_button($(form).find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.is_mobile_exists == true) {
                            swal({
                                title: LANG.sure,
                                text: result.msg,
                                icon: 'warning',
                                buttons: true,
                                dangerMode: true,
                            }).then(willContinue => {
                                if (willContinue) {
                                    submitQuickContactForm(form);
                                } else {
                                    $('#mobile').select();
                                }
                            });
                        } else {
                            submitQuickContactForm(form);
                        }
                    },
                });
            },
        });
    });
    
    //get customer
    $('select#customer_id').select2({
        ajax: {
            url: '/estates/contacts/customers',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                };
            },
            processResults: function (data) {
                return {
                    results: data,
                };
            },
        },
        templateResult: function (data) {
            var template = '';
            if (data.supplier_business_name) {
                template += data.supplier_business_name + '<br>';
            }
            template += data.text + '<br>' + LANG.mobile + ': ' + data.mobile;

            if (typeof data.total_rp != 'undefined') {
                var rp = data.total_rp ? data.total_rp : 0;
                template += "<br><i class='fa fa-gift text-success'></i> " + rp;
            }

            $('#contact_info').html('<br>' + template);

            return template;
        },
        minimumInputLength: 1,
        language: {
            noResults: function () {
                var name = $('#customer_id')
                    .data('select2')
                    .dropdown.$search.val();
                return (
                    '<button type="button" data-name="' +
                    name +
                    '" class="btn btn-link add_new_customer"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
                    __translate('add_name_as_new_customer', { name: name }) +
                    '</button>'
                );
            },
        },
        escapeMarkup: function (markup) {
            return markup;
        },
    });

    // check changes of property space and property price_per_meter inputs  for sell price property input
    $('#property_space, #property_price_per_meter').on('input', calculateSellPrice);
});

function submitQuickContactForm(form) {
    var data = $(form).serialize();
    $.ajax({
        method: 'POST',
        url: $(form).attr('action'),
        dataType: 'json',
        data: data,
        beforeSend: function (xhr) {
            __disable_submit_button($(form).find('button[type="submit"]'));
        },
        success: function (result) {
            if (result.success == true) {
                var name = result.data.name;

                if (result.data.supplier_business_name) {
                    name += result.data.supplier_business_name;
                }

                $('select#customer_id').append(
                    $('<option>', { value: result.data.id, text: name })
                );
                $('select#customer_id')
                    .val(result.data.id)
                    .trigger('change');
                    $('div.property_contact_modal').modal('hide');
                    toastr.success(result.msg);
                    update_shipping_address(result.data);

            } else {
                toastr.error(result.msg);
            }
        },
    });
}

function update_shipping_address(data) {
    if ($('#shipping_address_div').length) {
        var shipping_address = '';
        if (data.supplier_business_name) {
            shipping_address += data.supplier_business_name;
        }
        if (data.name) {
            shipping_address += ',<br>' + data.name;
        }
        if (data.text) {
            shipping_address += ',<br>' + data.text;
        }
        shipping_address += ',<br>' + data.shipping_address;
        $('#shipping_address_div').html(shipping_address);
    }
    if ($('#billing_address_div').length) {
        var address = [];
        if (data.supplier_business_name) {
            address.push(data.supplier_business_name);
        }
        if (data.name) {
            address.push('<br>' + data.name);
        }
        if (data.text) {
            address.push('<br>' + data.text);
        }
        if (data.address_line_1) {
            address.push('<br>' + data.address_line_1);
        }
        if (data.address_line_2) {
            address.push('<br>' + data.address_line_2);
        }
        if (data.city) {
            address.push('<br>' + data.city);
        }
        if (data.state) {
            address.push(data.state);
        }
        if (data.country) {
            address.push(data.country);
        }
        if (data.zip_code) {
            address.push('<br>' + data.zip_code);
        }
        var billing_address = address.join(', ');
        $('#billing_address_div').html(billing_address);
    }

    if ($('#shipping_custom_field_1').length) {
        let shipping_custom_field_1 =
            data.shipping_custom_field_details != null
                ? data.shipping_custom_field_details.shipping_custom_field_1
                : '';
        $('#shipping_custom_field_1').val(shipping_custom_field_1);
    }

    if ($('#shipping_custom_field_2').length) {
        let shipping_custom_field_2 =
            data.shipping_custom_field_details != null
                ? data.shipping_custom_field_details.shipping_custom_field_2
                : '';
        $('#shipping_custom_field_2').val(shipping_custom_field_2);
    }

    if ($('#shipping_custom_field_3').length) {
        let shipping_custom_field_3 =
            data.shipping_custom_field_details != null
                ? data.shipping_custom_field_details.shipping_custom_field_3
                : '';
        $('#shipping_custom_field_3').val(shipping_custom_field_3);
    }

    if ($('#shipping_custom_field_4').length) {
        let shipping_custom_field_4 =
            data.shipping_custom_field_details != null
                ? data.shipping_custom_field_details.shipping_custom_field_4
                : '';
        $('#shipping_custom_field_4').val(shipping_custom_field_4);
    }

    if ($('#shipping_custom_field_5').length) {
        let shipping_custom_field_5 =
            data.shipping_custom_field_details != null
                ? data.shipping_custom_field_details.shipping_custom_field_5
                : '';
        $('#shipping_custom_field_5').val(shipping_custom_field_5);
    }

    //update export fields
    if (data.is_export) {
        $('#is_export').prop('checked', true);
        $('div.export_div').show();
        if ($('#export_custom_field_1').length) {
            $('#export_custom_field_1').val(data.export_custom_field_1);
        }
        if ($('#export_custom_field_2').length) {
            $('#export_custom_field_2').val(data.export_custom_field_2);
        }
        if ($('#export_custom_field_3').length) {
            $('#export_custom_field_3').val(data.export_custom_field_3);
        }
        if ($('#export_custom_field_4').length) {
            $('#export_custom_field_4').val(data.export_custom_field_4);
        }
        if ($('#export_custom_field_5').length) {
            $('#export_custom_field_5').val(data.export_custom_field_5);
        }
        if ($('#export_custom_field_6').length) {
            $('#export_custom_field_6').val(data.export_custom_field_6);
        }
    } else {
        $(
            '#export_custom_field_1, #export_custom_field_2, #export_custom_field_3, #export_custom_field_4, #export_custom_field_5, #export_custom_field_6'
        ).val('');
        $('#is_export').prop('checked', false);
        $('div.export_div').hide();
    }

    $('#shipping_address_modal').val(data.shipping_address);
    $('#shipping_address').val(data.shipping_address);
}

function calculateSellPrice() {
    var space = parseFloat($('#property_space').val()) || 0;
    var pricePerMeter = $('#property_price_per_meter').val().replace(/,/g, '');
    var pricePerMeter = parseFloat(pricePerMeter) || 0;

    var sellPrice = space * pricePerMeter;

    // $('#property_sell_price').val(sellPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")).prop('readonly', true);
    $('#property_sell_price').val(sellPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
}