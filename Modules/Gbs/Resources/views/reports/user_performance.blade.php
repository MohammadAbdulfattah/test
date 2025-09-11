@extends('layouts.app')
@section('title', __('gbs::lang.user_performance'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('gbs::lang.user_performance')</h1>
    </section>
  
    <!-- Main content -->
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')]) 
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('performance_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('performance_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
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
        @component('components.widget', ['class' => 'box-primary', 'title' => __('gbs::lang.user_performance')])
        <table class="table table-bordered table-striped ajax_view hide-footer"  id="perormance_report">
            <thead>
                <tr>
                    <th>@lang('gbs::lang.date')</th>
                    <th>@lang('gbs::lang.users')</th>
                    <th>@lang('gbs::lang.day')</th>
                    <th>@lang('gbs::lang.target_visit')</th>
                    <th>@lang('gbs::lang.actual_visit')</th>
                    <th>@lang('gbs::lang.not_actual_visit')</th>
                    <th>@lang('gbs::lang.sales_count')</th>
                    <th>@lang('gbs::lang.total_sales')</th>
                    <th>@lang('gbs::lang.total_paid')</th>
                </tr>
            </thead>
        </table>
        
        @endcomponent
    </section>

@endsection
@section('javascript')
<script>
    $(document).ready(function() {
        $('#performance_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#performance_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            performance_table.ajax.reload();
        }
    );
    $('#performance_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#performance_filter_date_range').val('');
        performance_table.ajax.reload();
    });
    performance_table =  $('#perormance_report').DataTable({
    processing: true,
    serverSide: true,
    scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                ajax: {
        url: '/gbs/user-performance',
        data: function (d) {
        
            if ($('#performance_filter_date_range').val()) {
                            var start = $('#performance_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#performance_filter_date_range').data('daterangepicker').endDate
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
        { data: 'visit_date', name: 'visit_date' },
        { data: 'user_name', name: 'u.username' },
        { data: 'day_of_week', name: 'day_of_week' },
        { data: 'expected_visits', name: 'expected_visits', searchable: false },
        { data: 'actual_visits', name: 'actual_visits', searchable: false },
        { data: 'missed_visits', name: 'missed_visits', searchable: false },
        { data: 'sales_count', name: 'sales_count', searchable: false },
        { data: 'total_sales', name: 'total_sales', searchable: false },
        { data: 'total_paid', name: 'total_paid', searchable: false },
    ],
    "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#perormance_report'));
                },
    order: [[0, 'desc']],
   
});

$('#user_id').change(function() {
    performance_table.ajax.reload();
});


});
</script>
@endsection