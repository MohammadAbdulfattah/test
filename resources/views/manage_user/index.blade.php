@extends('layouts.app')
@section('title', __( 'user.users' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'user.users' )
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang( 'user.manage_users' )</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'user.all_users' )])
        @can('user.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full" href="{{action([\App\Http\Controllers\ManageUserController::class, 'create'])}}">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>                        @lang( 'messages.add' )
                    </a>
                 </div>
            @endslot
        @endcan
        @can('user.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="users_table">
                    <thead>
                        <tr>
                            <th class="text-center"><input type="checkbox" id="check_all"></th>
                            <th>@lang( 'business.username' )</th>
                            <th>@lang( 'user.name' )</th>
                            <th>@lang( 'user.role' )</th>
                            <th>@lang( 'business.email' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade user_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

 <!-- Shift Modal -->
<div class="modal fade" id="shiftModal" tabindex="-1" role="dialog" aria-labelledby="shiftModalLabel">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 95%; direction: rtl;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="shiftModalLabel">@lang('lang_v1.manage_shifts')</h4>
                <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body px-3 py-2">
                <div class="table-responsive">
                    <table class="table table-bordered w-100">
                        <thead>
                            <tr>
                                <th style="background-color: white;">@lang('lang_v1.day')</th>
                                <th style="background-color: #007bff; color: white;">@lang('lang_v1.work_time')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                            <tr class="day-row align-middle">
                                <td class="align-middle"> @lang('lang_v1.'.$day)</td>
                                <td>
                                    <div class="table-responsive">
                                        <table class="table table-striped inner-shift-table mb-0" data-day="{{ strtolower($day) }}">
                                            <thead>
                                                <tr style="background-color: #007bff; color: white;">
                                                    <th class="text-center">@lang('lang_v1.start_shift')</th>
                                                    <th class="text-center">@lang('lang_v1.end_shift')</th>
                                                    <th class="text-center">
                                                        <button class="btn btn-success btn-sm toggle-shift" data-day="{{ strtolower($day) }}">+</button>
                                                        <button class="btn btn-danger btn-sm toggle-shift-full" data-day="{{ strtolower($day) }}">-</button>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Shifts will be dynamically added here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        @lang('lang_v1.close')
                    </button>
                    <button type="button" class="btn btn-primary" id="saveShiftsBtn">
                        @lang('lang_v1.save')
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Include Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">

    $(document).ready( function(){
        var users_table = $('#users_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    ajax: '/users',
                    columnDefs: [ {
                        "targets": [0,5],
                        "orderable": false,
                        "searchable": false
                    } ],
                    "columns":[
                        {"data":"checkbox"},
                        {"data":"username"},
                        {"data":"full_name"},
                        {"data":"role"},
                        {"data":"email"},
                        {"data":"action"}
                    ]

                });
               

            users_table.button().add(0, { 
                 
                 text: '<i class="fa fa-calendar" aria-hidden="true"></i>' +'@lang('lang_v1.shift')',
                 className: 'tw-dw-btn tw-dw-btn-outline tw-my-2 tw-rounded-none',
                 action: function () {
        
                    $('#shiftModal').modal('show');
                }
            });
             $('#check_all').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('#users_table tbody input[type="checkbox"]').prop('checked', isChecked);
            });
            $('#users_table tbody').on('click', 'input.row_checkbox', function(e) {
              e.stopPropagation(); 
            });
        $(document).on('click', 'button.delete_user_button', function(){
            swal({
              title: LANG.sure,
              text: LANG.confirm_delete_user,
              icon: "warning",
              buttons: true,
              dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: data,
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                users_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
             });
        
        
    });
        function initializeFlatpickr() {
            flatpickr("#timePicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K", 
            allowInput: true,
            time_24hr: false, 
        });
    }
        $(document).on('click', '.toggle-shift', function () {
        let day = $(this).data('day');
        const table = $('table[data-day="' + day + '"] tbody');
            
        
        table.append(`
            <tr class="shift-row">
                <td class="p-1">
                      <input type="text" class="form-control form-control-sm w-100 mb-1"id="timePicker" name="start_time[${day}][]" required/>
                    
                </td>
                <td class="p-1">
                    <input type="text" class="form-control form-control-sm w-100 mb-1" id="timePicker"name="end_time[${day}][]" required/>
                    
                </td>
                <td class="text-center p-1">
                    <button class="btn btn-danger btn-sm delete-shift w-100">@lang('lang_v1.remove')</button>
                </td>
            </tr>
        `);
        initializeFlatpickr();
    });

    
    $(document).on('click', '.toggle-shift-full', function () {
        let day = $(this).data('day');
        const table = $('table[data-day="' + day + '"] tbody');

        
        table.empty();

       
        table.append(`
            <tr class="shift-row">
                <td class="p-1">
                      <input type="text" class="form-control form-control-sm w-100 mb-1"value="00:00" id="timePicker"name="start_time[${day}][]" required/>
                      
                </td>
                 <td class="p-1">
                    <input type="text" class="form-control form-control-sm w-100 mb-1"value="23:59" id="timePicker"name="end_time[${day}][]" required/>
                    
                </td>
                <td class="text-center p-1">
                    <button class="btn btn-danger btn-sm delete-shift w-100">@lang('lang_v1.remove')</button>
                </td>
            </tr>
        `);
        initializeFlatpickr();
    });

    // Delete shift
    $(document).on('click', '.delete-shift', function () {
        $(this).closest('tr').remove();
    });

    // Save shifts for selected users
    $('#saveShiftsBtn').click(function () {
      
        var selectedUserIds = [];
        $('#users_table tbody input[type="checkbox"]:checked').each(function () {
            selectedUserIds.push($(this).val());  
        });

        if (selectedUserIds.length === 0) {
            toastr.error('@lang('lang_v1.select_one_row')');
            return;
        }

        
        let shiftsData = {};
       
        $('table .shift-row').each(function () {
            let day = $(this).closest('table').data('day');
            let start_time = $(this).find('input[name="start_time[' + day + '][]"]').val() || $(this).find('td').eq(0).text();
            let end_time = $(this).find('input[name="end_time[' + day + '][]"]').val() || $(this).find('td').eq(1).text();

            if (!shiftsData[day]) shiftsData[day] = [];
            shiftsData[day].push({ start_time, end_time });
           
        });
        
     
        $.ajax({
            url: '/shifts', 
            method: 'POST',
            data: {
                user_ids: selectedUserIds,  
                shifts: shiftsData,  
                _token: $('meta[name="csrf-token"]').attr('content')  
            },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.msg);
                    $('#shiftModal').modal('hide');
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function (err) {
                toastr.error('@lang('messages.something_went_wrong')');
            }
        });
    });
  
});

    
</script>

<style>
    #shiftModal .modal-dialog {
        max-width: 95%;
        margin: 1.75rem auto;
        direction: rtl;
    }

    #shiftModal .modal-body table {
        margin-bottom: 20px;
    }

    #shiftModal .modal-body th, 
    #shiftModal .modal-body td {
        vertical-align: middle;
        text-align: center;
    }

    #shiftModal .inner-shift-table th,
    #shiftModal .inner-shift-table td {
        font-size: 0.875rem;
        padding: 0.4rem;
    }

    @media (max-width: 768px) {
        #shiftModal .modal-body {
            padding: 1rem;
        }

        #shiftModal .inner-shift-table th,
        #shiftModal .inner-shift-table td {
            font-size: 0.75rem;
        }

        #shiftModal .toggle-shift, 
        #shiftModal .delete-shift {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }



</style>



@endsection
