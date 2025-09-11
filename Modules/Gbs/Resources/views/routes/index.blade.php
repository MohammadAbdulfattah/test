@extends('layouts.app')
@section('title', __('gbs::lang.rootes'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('gbs::lang.rootes')</h1>
    </section>
    
    <!-- Main content -->
    <section class="content no-print">
        @component('components.widget', ['class' => 'box-primary',])
        @slot('tool')
        <div class="box-tools">
            <div class="box-tools">
                <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                    href="{{ action('\Modules\Gbs\Http\Controllers\RouteController@create') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg> @lang('messages.add')
                </a>
            </div>
            
        </div>
        @endslot
        <table class="table table-bordered" id="routes_table">
            <thead>
                <tr>
                    <th>@lang('gbs::lang.root_name')</th>
                    <th>@lang('gbs::lang.users')</th>
                    <th>@lang('gbs::lang.actions')</th>
                </tr>
            </thead>
        </table>
        @endcomponent

    </section> 
    <!-- /.content -->
    <!-- Modal -->
<div class="modal fade" id="routeDetailsModal" tabindex="-1" role="dialog" aria-labelledby="routeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header">
              <h4 class="modal-title" id="routeDetailsModalLabel">{{ __('gbs::lang.route_details') }}</h4>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body" id="route-details-body">
              <div class="text-center">
                  <i class="fa fa-spinner fa-spin fa-2x"></i>
              </div>
          </div>
      </div>
    </div>
  </div>
  
@endsection
@section('javascript')
<script>
$(document).ready(function() {
    $(document).on('click', 'button.delete_route_button', function() {
        swal({
            title: LANG.sure,
            text: LANG.confirm_delete_brand,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).data('href');
                var data = $(this).serialize();

                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            route_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });

    //show root 
    $(document).on('click', '.view-route-details', function (e) {
            e.preventDefault();
            let routeId = $(this).data('id');
            $('#route-details-body').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            $('#routeDetailsModal').modal('show');

            $.ajax({
                url: '/gbs/routes/' + routeId + '/details',
                type: 'GET',
                success: function (response) {
                    $('#route-details-body').html(response);
                },
                error: function () {
                    $('#route-details-body').html('<div class="alert alert-danger">فشل في تحميل البيانات.</div>');
                }
            });
        });
   $route_table = $('#routes_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/gbs/routes',
        columns: [
            { data: 'name', name: 'gbs_routes.name'  },
            { data: 'user.username', name: 'user.username',orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
      
    });
});
</script>
@endsection