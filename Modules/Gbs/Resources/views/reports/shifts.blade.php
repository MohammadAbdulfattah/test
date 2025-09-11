@extends('layouts.app')
@section('title', __('gbs::lang.shifts_report'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('gbs::lang.shifts_report')</h1>
    </section>
  
    <!-- Main content -->
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')]) 
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('shifts_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('shifts_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('user_id', __('gbs::lang.users') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-user"></i>
                    </span>
                    {!! Form::select('user_id', $users, null, ['class' => 'form-control select2',  'style' => 'width:100%', 'placeholder' => __('messages.all'), 'required']); !!}
                </div>
            </div>
        </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary', 'title' => __('gbs::lang.shifts_report')])
        <table class="table table-bordered table-striped ajax_view hide-footer"  id="shifts_report">
            <thead>
                 <tr>
                
                    <th>@lang('gbs::lang.users')</th>
                     <th>@lang('gbs::lang.day')</th>
                     <th>@lang('gbs::lang.date')</th>
                     <th>@lang('gbs::lang.start_time')</th>
                     <th>@lang('gbs::lang.end_time')</th>
                     <th>@lang('gbs::lang.start_location')</th>
                     <th>@lang('gbs::lang.end_location')</th>
                     <th>@lang('gbs::lang.working_hours')</th>
                     <th>@lang('gbs::lang.target_visit')</th>
                     <th>@lang('gbs::lang.actual_visit')</th>
                     <th>@lang('gbs::lang.not_actual_visit')</th>
    
                </tr>
            </thead>
        </table>
        
        @endcomponent
    </section>

@endsection
@section('javascript')
<script>
    $(document).ready(function() {
        $('#shifts_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#shifts_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            shifts_table.ajax.reload();
        }
    );
    $('#shifts_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#shifts_filter_date_range').val('');
        shifts_table.ajax.reload();
    });
    shifts_table =  $('#shifts_report').DataTable({
    processing: true,
    serverSide: true,
    
                scrollCollapse: true,
                ajax: {
        url: '/gbs/shifts-report',
        data: function (d) {
        
            if ($('#shifts_filter_date_range').val()) {
                            var start = $('#shifts_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#shifts_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                        if ($('#user_id').val()) {
                            d.user_id = $('#user_id').val();
                        }
        }
        
                   
    },
   
    columns: [
    
        { data: 'user_name', name: 'users.username' },
         { data: 'day_of_week', name: 'created_at' },
         { data: 'created_at', name: 'created_at' },
         { data: 'start_time', name: 'start_time' },
         { data: 'end_time', name: 'end_time' },
         { data: 'start_location', name: 'start_location' , searchable: false},
         { data: 'end_location', name: 'end_location' },
         { data: 'working_hours' },
         { data: 'expected_visits' },
         { data: 'successful_visits' },
         { data: 'missed_visits' },
    //     { data: 'expected_visits', name: 'expected_visits', searchable: false },
    //     { data: 'actual_visits', name: 'actual_visits', searchable: false },
    //     { data: 'missed_visits', name: 'missed_visits', searchable: false },
    //     { data: 'sales_count', name: 'sales_count', searchable: false },
    //     { data: 'total_sales', name: 'total_sales', searchable: false },
    //     { data: 'total_paid', name: 'total_paid', searchable: false },
     ],
    "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#shifts_report'));
                },
    order: [[0, 'desc']],
   
});

$('#user_id').change(function() {
    shifts_table.ajax.reload();
});


});
</script>
@endsection