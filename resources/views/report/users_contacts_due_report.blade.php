@extends('layouts.app')
@section('title', __('report.user_contacts_due_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('report.user_contacts_due_report')}}</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('cs_report_location_id', __( 'sale.location' ) . ':') !!}
                        {!! Form::select('cs_report_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'cs_report_location_id']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('scr_contact_id', __( 'report.contact' ) . ':') !!}
                        {!! Form::select('scr_contact_id', $contact_dropdown, null , ['class' => 'form-control select2', 'id' => 'scr_contact_id', 'placeholder' => __('lang_v1.all'), 'style' => 'width:100%']); !!}
                    </div>
                </div>

                  <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('user_filter', __( 'report.user' ) . ':') !!}
                        {!! Form::select('user_filter', $usersFilter, null , ['class' => 'form-control select2', 'id' => 'user_filter_id', 'placeholder' => __('lang_v1.all'), 'style' => 'width:100%']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('scr_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'scr_date_filter', 'readonly']); !!}
                    </div>
                </div>

            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
    <style>
        #users_contacts_due_report_tbl td {
            text-align: right;
        }
        #users_contacts_due_report_tbl td:empty:after {
            content: "0.00";
        }
            #users_contacts_due_report_tbl th:first-child,
    #users_contacts_due_report_tbl td:first-child {
        width: 150px; /* Adjust as needed */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    </style>
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="users_contacts_due_report_tbl">
            <thead>
                    <tr>
                        <th>@lang('report.contact')</th>
                        @foreach ($users as $user_id => $username)
                            <th>{{ $username }}</th>
                        @endforeach
                        <th>@lang('sale.total'):</th>
                    </tr>
            </thead>
           <tfoot>
                <tr class="bg-gray font-17 footer-total text-center">
                    <th>@lang('sale.total'):</th>
                    @foreach ($users as $user_id => $username)
                        <th></th>
                    @endforeach
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
@endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    <script>
    let all_users = @json($users);
   </script>
   @if(count($users) == 0)
<script>
    $(document).ready(function() {
        $('#users_contacts_due_report_tbl').DataTable({
            data: [],
            columns: [{ data: 'contact_name' }],
            paging: false,
            searching: false,
            info: false,
            language: {
                emptyTable: "لا توجد بيانات متاحة"
            }
        });
    });
</script>
@endif
@endsection