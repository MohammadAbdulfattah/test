@extends('layouts.app')
@section('title', __('goals::goals.group_details'))

@section('content')
    @if (!empty($status))
        <div class="alert alert-{{ $status['success'] ? 'success' : 'danger' }}">
            {{ $status['msg'] }}
        </div>
    @endif
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('goals::goals.group_details')

        </h1>
        <!-- <ol class="breadcrumb">
                            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                            <li class="active">Here</li>
                        </ol> -->
    </section>
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('goals::goals.group_details')])
            @can('group_details.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="groups_table">
                        <thead>
                            <tr>
                                <th>@lang('lang_v1.type')</th>
                                <th>@lang('brand.brands')</th>
                                <th>@lang('product.category')</th>
                                <th>@lang('product.product_name')</th>

                            </tr>
                        </thead>
                    </table>
                </div>
            @endcan
        @endcomponent


    </section>
        <!-- /.content -->
@endsection   
@section('javascript')

<script src="{{ asset('js/stock_transfer.js?v=' . $asset_v) }}"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var groups_table = $('#groups_table').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader: false,
            ajax: {
                url: '/goals/group-details/{{ $id }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            },
           
            columns: [{
                    data: "type_translated",
                    defaultContent: "--"
                },
                {
                    data: "brand_name",
                    defaultContent: "--"
                },
                {
                    data: "category_name",
                    defaultContent: "--"
                },
                {
                    data: "product_name",
                    defaultContent: "--"
                }
            ]
        });
    });
</script>

@endsection
