@extends('layouts.app')
@section('title', __('lang_v1.customer_sector'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('lang_v1.customer_sector')
    </h1>

</section>
<section class="content">
    <div class="row">
      
        @component('components.widget', ['class' => 'box-solid'])
        @slot('tool')
                    <div class="box-tools">
                      
        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full btn-modal"
    data-href="{{ action([\App\Http\Controllers\CustomerSectorController::class, 'create']) }}"
    data-container=".sector_modal">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M12 5l0 14" />
        <path d="M5 12l14 0" />
    </svg> @lang('messages.add')
</a>

                    </div>
                @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="sectors_table">
                <thead>
                    <tr>
                       
                        <th>@lang('lang_v1.name')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    @endcomponent
</section>
<!-- /.content -->
<div class="modal fade sector_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@section('javascript')
<script>
$(document).ready(function() {
    

     const sector_table = $('#sectors_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/customer-sectors',
        columns: [
            { data: 'name', name: 'name'  },
            // { data: 'user.username', name: 'user.username',orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
      
    });
    $(document).on('click', 'a.delete-sector', function(e) {
                    e.preventDefault();
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(willDelete => {
                        if (willDelete) {
                            var href = $(this).attr('href');
                            $.ajax({
                                method: 'DELETE',
                                url: href,
                                dataType: 'json',
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        sector_table.ajax.reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                });
    $(document).on('submit', 'form#sector_add_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            beforeSend: function(xhr) {
                __disable_submit_button(form.find('button[type="submit"]'));
            },
            success: function(result) {
                if (result.success === true) {
                    $('div.sector_modal').modal('hide');
                    toastr.success(result.msg);
                    if(typeof  sector_table !== 'undefined') {
                        sector_table.ajax.reload();
                    }

                    var evt = new CustomEvent("categoryAdded", {detail: result.data});
                    window.dispatchEvent(evt);

                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });
    $(document).on('submit', 'form#sector_edit_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            beforeSend: function(xhr) {
                __disable_submit_button(form.find('button[type="submit"]'));
            },
            success: function(result) {
                if (result.success === true) {
                    $('div.sector_modal').modal('hide');
                    toastr.success(result.msg);
                    if(typeof  sector_table !== 'undefined') {
                        sector_table.ajax.reload();
                    }

                   

                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });
});
</script>
@endsection