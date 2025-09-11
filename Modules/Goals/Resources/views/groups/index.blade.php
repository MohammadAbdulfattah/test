@extends('layouts.app')
@section('title', __('goals::goals.groups'))

@section('content')
    @if (!empty($status))
        <div class="alert alert-{{ $status['success'] ? 'success' : 'danger' }}">
            {{ $status['msg'] }}
        </div>
    @endif
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('goals::goals.groups')
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('goals::goals.manage_cashvans')</small>
        </h1>
        <!-- <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                    <li class="active">Here</li>
                </ol> -->
    </section>
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('goals::goals.groups')])
            @can('goals.create')
                @slot('tool')
                    <div class="box-tools">

                        <a href="{{route('group.create')}}"
                            class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full"
                            >
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24V0H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg>
                            @lang('messages.add')
                        </a>
                    </div>
                @endslot
            @endcan
            @can('goals.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="groups_table">
                        <thead>
                            <tr>
                                <th>@lang('user.name')</th>
                                <th>@lang('goals::goals.start_date')</th>
                                <th>@lang('goals::goals.end_date')</th>
                                <th>@lang('goals::goals.supervisor')</th>
                                <th>@lang('lang_v1.added_by')</th> 
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>.
                </div>
            @endcan
        @endcomponent

        <div class="modal fade" id="goal_create_modal" tabindex="-1" role="dialog"
        aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" id="goal_create_modal_body">
                    <!-- Content will be loaded here dynamically -->
                </div>
            </div>
        </div>
      
    <!-- /.content -->
@stop
@section('javascript')

	<script src="{{ asset('js/stock_transfer.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript">

        $(document).ready(function() {
            var groups_table = $('#groups_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                ajax: '/goals/groups',
                columnDefs: [{
                    "targets": [5],
                    "orderable": false,
                    "searchable": false
                }],
                "columns": [{
                        "data": "name"
                    },
                    {
                        "data": "start_date"
                    },
                    {
                        "data": "end_date"
                    },
                    {
                        "data": "supervisor"
                    },
                    {
                        "data": "added_by"
                    },
                    {
                        "data": "action"
                    }
                ]

            });
           
            $(document).on('click', 'a.delete_group', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).data('href');
                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        success: function(result) {
                            if (result.success) {
                                

                                toastr.success(result.msg);
                                groups_table.ajax.reload(); 
                               
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
       

        });
       
        $(document).on('click', '.goal_create_modal_button', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            $('#goal_create_modal_body').html('<div class="p-4 text-center">@lang("messages.loading")...</div>');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#goal_create_modal_body').html(response);
                },
                error: function() {
                    $('#goal_create_modal_body').html(
                        '<div class="p-4 text-danger">@lang("messages.something_went_wrong")</div>'
                    );
                   
                }
            });
        });
        $('#goal_create_modal').on('shown.bs.modal', function () {
            $(this).find('select.select2').select2({
                dropdownParent: $('#goal_create_modal'), // very important for modals
              
                minimumResultsForSearch: 0,
            });
        });
        
      
       

    </script>

@endsection
