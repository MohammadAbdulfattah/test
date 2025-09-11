@extends('layouts.app')
@section('title', __('gbs::lang.tags'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1  class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('gbs::lang.tags')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
     
       
        @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
        <div class="box-tools">
            <button type="button" class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-left" data-toggle="modal" data-target="#tagsModal">
                @lang('messages.add')
              </button>
            
        </div>
        @endslot
        <table class="table table-bordered" id="tags_table">
            <thead>
                <tr>
                    <th>@lang('gbs::lang.tag_name')</th>
                    <th>@lang('gbs::lang.tag_color')</th>
                    <th>@lang('gbs::lang.actions')</th>
                </tr>
            </thead>
        </table>
        @endcomponent


    </section>
<!-- Modal -->
<div class="modal fade" id="tagsModal" tabindex="-1" aria-labelledby="tagsModalTitle" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">إضافة تاغ جديد</h5>
          <button type="button" class="close btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
        </div>
  
        <div class="modal-body">
          <form id="tagForm">
            @csrf
            <div class="mb-3">
              <label class="form-label">اسم التاغ</label>
              <input type="text" class="form-control" name="tag_name" id="tag_name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">لون التاغ</label>
              <input type="color" class="form-control form-control-color" name="tag_color" id="tag_color" value="#563d7c">
            </div>
          </form>
          <div id="tagResult" class="text-success fw-bold mt-2" style="display:none;"></div>
        </div>
  
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <button type="button" class="btn btn-primary" onclick="saveTag()">حفظ</button>
        </div>
      </div>
    </div>
  </div>

     <div class="modal fade" id="editTagModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">تعديل التاغ</h5>
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="إغلاق">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <input type="hidden" id="edit_tag_id">
              <div class="form-group">
                <label>اسم التاغ</label>
                <input type="text" class="form-control" id="edit_tag_name" required>
              </div>
              <div class="form-group">
                <label>لون التاغ</label>
                <input type="color" class="form-control" id="edit_tag_color" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
              <button type="button" class="btn btn-primary" onclick="updateTag()">حفظ التغييرات</button>
            </div>
          </div>
        </div>
      </div>
       
      
@stop

@section('javascript')

<script type="text/javascript">

function saveTag() {
   
    const name = document.getElementById('tag_name').value;
    const color = document.getElementById('tag_color').value;

    if (!name || !color) {
        alert("يرجى إدخال الاسم واللون.");
        return;
    }

    fetch("{{ route('tags.store') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ name: name, color: color })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
   
    document.getElementById('tagsModal').style.display = 'none';
    document.getElementById('tagsModal').classList.remove('show');
    document.body.classList.remove('modal-open');
    document.querySelector('.modal-backdrop')?.remove();
    toastr.success(data.message);
   
    $('#tags_table').DataTable().ajax.reload(null, false);
}
 else {
            alert("حدث خطأ أثناء الحفظ");
        }
    })
    .catch(err => {
        console.error(err);
        alert("فشل في الاتصال بالخادم");
    });
}

$(document).ready(function() {
$('#tags_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/gbs/tags',
        columns: [
            { data: 'name', name: 'name'  },
            { data: 'color', name: 'color',orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
      
    });
    $(document).on('click', '.edit-tag-btn', function () {
    const id = $(this).data('id');
    const name = $(this).data('name');
    const color = $(this).data('color');

    $('#edit_tag_id').val(id);
    $('#edit_tag_name').val(name);
    $('#edit_tag_color').val(color);

    $('#editTagModal').modal('show'); 
});

});

    function updateTag() {
        const id = $('#edit_tag_id').val();
        const name = $('#edit_tag_name').val();
        const color = $('#edit_tag_color').val();
        fetch("{{ route('tags.update', ['id' => '__id__']) }}".replace('__id__', id), {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ name: name, color: color })
})

        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#editTagModal').modal('hide');
                toastr.success(data.message);
                $('#tags_table').DataTable().ajax.reload(null, false);
            } else {
                
                alert('Something went wrong');
            }
        })
        .catch(error => {
            console.error(error);
            alert('فشل في الاتصال بالخادم.');
        });
    }


</script>


@endsection