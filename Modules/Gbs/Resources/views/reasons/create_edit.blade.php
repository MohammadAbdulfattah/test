<div class="modal-dialog" role="document">
    <div class="modal-content">
{!! Form::model($reason ?? null, [
    'route' => isset($reason) 
        ? ['failure_reasons.update', $reason->id] 
        : 'failure_reasons.store',
    'method' => isset($reason) ? 'PUT' : 'POST',
    'id' => 'failure_reason_form'
]) !!}

        <div class="modal-header">
            <h4 class="modal-title">@lang(isset($reason) ? 'messages.edit' : 'messages.add')</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('reason', __('messages.name') . ':') !!}
                {!! Form::text('reason', $reason->reason ?? '', ['class' => 'form-control', 'required']) !!}
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
