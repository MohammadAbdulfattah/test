 <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            {!! Form::open([
                'url' => action([App\Http\Controllers\SellController::class, 'assignToDrivers']),
                'method' => 'post',
            ]) !!}

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('lang_v1.choose_driver')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 col-md-offset-4">
                        <div class="form-group">
                            {!! Form::label('driver_id', __('cashvan::cashvan.driver') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                {!! Form::select('driver_id', $users ?? [], null, [
                                    'class' => 'form-control select2',
                                    'id' => 'user_id',
                                    'style' => 'width: 100%;',
                                ]) !!}

                            </div>
                        </div>
                    </div>
                    @foreach ($transaction_ids as $id)
                        <input type="hidden" name="transaction_ids[]" value="{{ $id }}">
                    @endforeach
                    
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
                <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                    data-dismiss="modal">@lang('messages.close')</button>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</div>
</div>
