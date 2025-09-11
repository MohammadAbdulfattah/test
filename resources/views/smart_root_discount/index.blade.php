@extends('layouts.app')
@section('title', __('sale.root_smart_discounts'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('sale.root_smart_discounts')
        </h1>

    </section>

    <!-- Main content -->
    <section class="content">
          @component('components.filters', ['title' => __('report.filters')])
            @include('smart_root_discount.smart_root_discount_filters')
      
        @endcomponent
        <div
            class=" tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
            <div class="tw-p-4 sm:tw-p-5">
            <div class="tw-flex tw-gap-2.5 tw-justify-end">
                    @can('smart_root_discount.create')
                    <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                        href="{{ action([\App\Http\Controllers\DiscountController::class,'create_smart_root_discount']) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg> @lang('messages.add')
                    </a>
                    @endcan
                </div>
                <div class="tw-flow-root tw-mt-5 tw-border-b tw-border-gray-200">
                    <div class="tw-mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                          
                        <table class="table    data-table     dataTable-table rt-table" id="requestTable">
                                    <thead>
                                        <tr>
                                            
                            
                                            <th>@lang('sale.discount_name')</th>
                                            <th>@lang('sale.discount_type')</th>
                                            <th>@lang('sale.discount_start_date')</th>
                                            <th>@lang('sale.discount_end_date')</th>
                                            <th>@lang('sale.created_by')</th>
                                            <th>@lang('sale.action')</th>
                                            
                                        </tr>
                                    </thead>
                            
                                </table>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteDiscountModal" tabindex="-1" role="dialog" aria-labelledby="deleteDiscountModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="deleteDiscountModalLabel">@lang('messages.delete_confirmation')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @lang('messages.are_you_sure_delete')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
                <a id="confirmDeleteButton" href="#" class="btn btn-danger">@lang('messages.delete')</a>
            </div>
        </div>
    </div>
</div>


        <div class="modal fade discount_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).on('click', '#deactivate-selected', function(e) {
            e.preventDefault();
            var selected_rows = [];
            var i = 0;
            $('.row-select:checked').each(function() {
                selected_rows[i++] = $(this).val();
            });

            if (selected_rows.length > 0) {
                $('input#selected_discounts').val(selected_rows);
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $('form#mass_deactivate_form').submit();
                    }
                });
            } else {
                $('input#selected_discounts').val('');
                swal('@lang('lang_v1.no_row_selected')');
            }
        });
          $('#deleteDiscountModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var deleteUrl = button.data('href'); // Extract href from data-href attribute
        
        // Update the modal's confirm button href
        var modal = $(this);
        modal.find('#confirmDeleteButton').attr('href', deleteUrl);
    });
    
    // Optional: Prevent the default action when clicking delete buttons
    $('.delete_discount_button').click(function(e) {
        e.preventDefault();
    });
        var table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    scrollX: false,
                    ajax: {
                        url: "{{ route('smart_root_discounts') }}",
                        data: function(d) {
                                d.type_smart_root_discount_id = $('#type_smart_root_discount_id_filter').val();
                                d.business_id = $('#business_id').val();
                                d.user_id = $('#user_id').val();

                                var date_range = $('#smart_root_discount_date_range').val();
                                if (date_range) {
                                    var dates = date_range.split(' ~ ');
                                    if (dates.length === 2) {
                                        // Convert to YYYY-MM-DD format for backend
                                        d.start_date = moment(dates[0], moment_date_format).format('YYYY-MM-DD');
                                        d.end_date = moment(dates[1], moment_date_format).format('YYYY-MM-DD');
                                    }
                                }
                            },
                    },
                   

                    columns: [
                        
                        {
                            data: 'discount_name',
                            name: 'discount_name'
                        },
                        {
                            data: 'discount_type',
                            name: 'discount_type'
                        },
                        {
                            data: 'discount_start_date',
                            name: 'discount_start_date'
                        },
                        {
                            data: 'discount_end_date',
                            name: 'discount_end_date'
                        },
                        {
                            data: 'created_by',
                            name: 'created_by'
                        },
                        {
                            data: 'action',
                            name: 'action'
                        },
                   
                    ],

                });

        $(document).on('click', '.activate-discount', function(e) {
            e.preventDefault();
            var href = $(this).data('href');
            $.ajax({
                method: "get",
                url: href,
                dataType: "json",
                success: function(result) {
                    if (result.success == true) {
                        toastr.success(result.msg);
                        discounts_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });
        $(document).on('shown.bs.modal', '.discount_modal', function() {
            $('#variation_ids').select2({
                ajax: {
                    url: '/purchases/get_products?check_enable_stock=false&only_variations=true',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        var results = [];
                        for (var item in data) {
                            results.push({
                                id: data[item].variation_id,
                                text: data[item].text,
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                },
                minimumInputLength: 1,
                closeOnSelect: false
            });
        });

        $(document).on('click', '.toggle-discount-status', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var button = $(this);
            
            $.ajax({
                method: 'POST',
                url: url,
                data: { 
                    _token: '{{ csrf_token() }}' 
                },
                beforeSend: function() {
                    button.prop('disabled', true);
                    button.append(' <i class="fa fa-spinner fa-spin"></i>');
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.msg);
                        // Reload the table or update the UI as needed
                        discount_table.ajax.reload();
                    } else {
                        toastr.error(response.msg);
                    }
                },
                error: function(xhr) {
                    toastr.error(__('messages.something_went_wrong'));
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.find('.fa-spinner').remove();
                }
            });
        });
            $(document).on('change', '#type_smart_root_discount_id_filter', function() {
               
                var type_id = $(this).val();
                table.ajax.url("{{ route('smart_root_discounts') }}?type_smart_root_discount_id=" + type_id).load();
            });

            $(document).on('change', '#business_id', function() {
               
                var business_id = $(this).val();
                table.ajax.url("{{ route('smart_root_discounts') }}?business_id=" + business_id).load();
            });

              $(document).on('change', '#user_id', function() {
               
                var user_id = $(this).val();
                table.ajax.url("{{ route('smart_root_discounts') }}?user_id=" + user_id).load();
            });

             $('#smart_root_discount_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#smart_root_discount_date_range').val(
                        start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                    );
                    // Just reload the table - the data function will handle parameters
                    table.ajax.reload();
                }
            );

            // Clear date range
            $('#smart_root_discount_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                table.ajax.reload();
            });

        $(document).on('hidden.bs.modal', '.discount_modal', function() {
            $("#variation_ids").select2('destroy');
        });
    </script>
@endsection
