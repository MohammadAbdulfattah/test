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

                        <a href="{{ route('goal.create',$id) }}"
                            class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24V0H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg>
                            @lang('goals::goals.add_user')
                        </a>
                    </div>
                @endslot
            @endcan
            @can('goals.view')
                <table class="table table-bordered" id="group_goal_summary_table">
                    <thead>
                        <tr>
                            <th>@lang('user.name')</th>
                            <th>@lang('goals::goals.goal_type')</th>
                            <th>@lang('goals::goals.item_name')</th>
                            <th>@lang('goals::goals.target_amount')</th>
                            <th>@lang('goals::goals.reward')</th>
                            <th>@lang('goals::goals.actual_sales')</th>
                            <th>@lang('goals::goals.reward_taken')</th>
                            <th>@lang('goals::goals.percentage_done')</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        @forelse($goal_summary as $entry)
                            @php
                                $childCount = !empty($entry['child_goals']) ? count($entry['child_goals']) : 0;
                                $rowspan = 1 + $childCount;
                                $rewardTaken = round($entry['percentage'], 2) >= 100 ? $entry['reward'] : 0;
                            @endphp
                    
                            {{-- Parent row --}}
                            <tr class="bg-gray" style="background-color: #2c2626;">
                                <td rowspan="{{ $rowspan }}">{{ $entry['user_name'] }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $entry['goal_type'] }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $entry['item_name'] }}</td>
                                @if(isset($entry['target_amount']))
                                    <td>@format_currency($entry['target_amount'])</td>
                                    <td>@format_currency($entry['reward'] ?? 0) </td>
                                    <td>@format_currency($entry['actual_sales'])</td>
                                @else
                                    <td>{{$entry['target_qty']}}</td>
                                    <td>@format_currency($entry['reward'] ?? 0) </td>
                                    <td>{{$entry['actual_sales']}}</td>
                                @endif
                                
                                <td>@format_currency($rewardTaken)</td>
                                <td>{{ round($entry['percentage'], 2) }}%</td>
                            </tr>
                    
                            {{-- Child rows --}}
                            @if (!empty($entry['child_goals']))
                                @foreach ($entry['child_goals'] as $child)
                                    @php
                                        $childRewardTaken = round($child['percentage'], 2) >= 100 ? $child['reward'] : 0;
                                    @endphp
                                    <tr>
                                    @if(isset($child['target_amount']))
                                        <td>@format_currency($child['target_amount'])</td>
                                        <td>@format_currency($child['reward'] ?? 0) </td>
                                        <td>@format_currency($child['actual_sales'])</td>
                                    @else
                                        <td>{{$child['target_qty']}}</td>
                                        <td>@format_currency($child['reward'] ?? 0) </td>
                                        <td>{{$child['actual_sales']}}</td>
                                    @endif
                                        <td>@format_currency($childRewardTaken)</td>
                                        <td>{{ round($child['percentage'], 2) }}%</td>
                                    </tr>
                                @endforeach
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">@lang('purchase.no_records_found')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endcan
        @endcomponent



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
        </script>

    @endsection
