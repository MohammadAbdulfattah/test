
{!! Form::open([
    'url' => action([Modules\Goals\Http\Controllers\GoalsController::class, 'store']),
    'method' => 'post',
]) !!}

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">@lang('goals::goals.add_category_goal')</h4>
</div>

<div class="modal-body">
    <div class="row">
        <input type="hidden" name="type" value="category">
        <input type="hidden" name="id" value="{{$id}}">

        <div class="col-md-4 col-md-offset-4">
            <div class="form-group">
                {!! Form::label('category_id', __('product.category') . ':') !!}
                <div class="input-group">
                    {{-- Addon on the left (optional icon or label) --}}
                    <span class="input-group-addon">
                        <i class="fa fa-tags"></i> {{-- Or any icon or text --}}
                    </span>
        
                    {{-- The Select2 dropdown --}}
                    {!! Form::select('category_id', $categories ?? [], null, [
                        'class' => 'form-control select2',
                        'id' => 'category_id',
                        'style' => 'width: 100%;',
                    ]) !!}
                </div>
            </div>
        </div>
        

    </div>

</div>

<div class="modal-footer">
    <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
    <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
        data-dismiss="modal">@lang('messages.close')</button>
</div>

{!! Form::close() !!}

