@extends('layouts.app')
@section('title', __('gbs::lang.visit_report'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('gbs::lang.visit_report')</h1>
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
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('customer_id', __('gbs::lang.customer_name') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-user"></i>
                    </span>
                    {!! Form::select('customer_id', $customers, null, ['class' => 'form-control select2',  'style' => 'width:100%', 'placeholder' => __('messages.all'), 'required']); !!}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="cg_filter">@lang('lang_v1.customer_group'):</label>
                {!! Form::select('cg_filter', $customer_groups, null, ['class' => 'form-control', 'id' => 'cg_filter']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="tags">@lang('gbs::lang.tags'):</label>
                {!! Form::select('tags', $tags, null, ['class' => 'form-control', 'id' => 'tags']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="visit_status_filter">@lang('gbs::lang.visit_status'):</label>
                {!! Form::select('visit_status_filter', [
                    '' => __('messages.all'),
                    'true_with_sell' => __('gbs::lang.completed_visit_with_sell'),     
                    'true_without_sell' => __('gbs::lang.completed_visit_without_sell'), 
                    'false' => __('gbs::lang.incomplete_visit')
                ], null, ['class' => 'form-control', 'id' => 'visit_status_filter']) !!}
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('location_filter', __('business.business_locations') . ':') !!}
                {!! Form::select('location_filter[]', $business_locations, null, ['class' => 'form-control', 'id' => 'location_filter', 'placeholder' => __('messages.all')]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('parent_sectors', __('gbs::lang.customer_sector') . ':') !!}
                {!! Form::select('parent_sectors', $parent_sectors, null, ['class' => 'form-control', 'id' => 'parent_sectors', 'placeholder' => __('messages.all')]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sector_id', __('gbs::lang.customer_sub_sector') . ':') !!}
                {!! Form::select('sector_id', [], null, ['class' => 'form-control', 'id' => 'sector_id']) !!}
            </div>
    </div>
        
        @endcomponent
        @component('components.widget', ['class' => 'box-primary', 'title' => __('gbs::lang.visit_report')])
        
        
        <table class="table table-bordered table-striped ajax_view hide-footer" style="width:100%"  id="visits_report">
            <thead>
                <tr>
               
                    <th>@lang('gbs::lang.users')</th>
                    <th>@lang('gbs::lang.customer_name')</th>
                    <th>@lang('gbs::lang.date')</th>
                    <th>@lang('gbs::lang.started_at')</th>
                    <th>@lang('gbs::lang.ended_at')</th>
                    <th>@lang('gbs::lang.visit_status')</th>
                    <th>@lang('gbs::lang.invoice_no')</th>
            
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
            visits_table.ajax.reload();
        }
    );
    $('#performance_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#performance_filter_date_range').val('');
        visits_table.ajax.reload();
    });
    visits_table =  $('#visits_report').DataTable({
    processing: true,
    serverSide: true,
    scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                ajax: {
        url: '/gbs/visits-report',
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
                        if ($('#customer_id').val()) {
                       
                            d.customer_id = $('#customer_id').val();
                        }
                        if ($('#cg_filter').val()) {
                          
                            d.customer_group_id = $('#cg_filter').val();
                        }
                        if ($('#tags').val()) {
                          
                          d.tag_id = $('#tags').val();
                      }
                      if ($('#parent_sectors').val()) {
                       
                          d.parent_sector_id = $('#parent_sectors').val();
                      }
                      if ($('#sector_id').val()) {
                        
                          d.sector_id = $('#sector_id').val();
                      }
                      if ($('#visit_status_filter').val()) {
                        d.visit_status = $('#visit_status_filter').val();
                        }
                        if ($('#location_filter').val()) {
                        d.location_id = $('#location_filter').val();
                        }
        }
    },
   
    columns: [
    { data: 'user_name', name: 'u.username' },
    { data: 'client_name', name: 'c.name' },
    { data: 'visit_date', name: 'visit_date' },
    { data: 'started_at', name: 'dv.started_at' },
    { data: 'ended_at', name: 'dv.ended_at' },
    { data: 'visit_status', name: 'visit_status', searchable: false, orderable: false },
    { data: 'invoice_no', name: 't.invoice_no' }
]
,
    "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#visits_report'));
                },
    order: [[0, 'desc']],
   
});

$('#user_id,#sector_id,#parent_sectors,#customer_id,#cg_filter,#tags,#visit_status_filter,#location_filter').change(function() {
    visits_table.ajax.reload();
});

$('#parent_sectors').change(function() {
        var parent_id = $(this).val();
        if (parent_id) {
            $.ajax({
                url: '/sectors/children/' + parent_id,
                method: 'GET',
                success: function(data) {
                    if (data.length > 0) {
                        $('#sector_id').empty();
                        $.each(data, function(index, sector) {
                            $('#sector_id').append('<option value="' + sector.id + '">' + sector.name + '</option>');
                        });
                        $('#child_sector_div').show();
                    } else {
                        $('#child_sector_div').hide();
                        $('#sector_id').empty();
                    }
                }
            });
        } else {
            $('#child_sector_div').hide();
            $('#sector_id').empty();
        }
    });
});
</script>
@endsection