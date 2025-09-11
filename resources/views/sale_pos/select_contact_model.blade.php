<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">{{ __('lang_v1.pay_for_contact') }}</h4>
        </div>
        <div class="modal-body">
            <!-- Your modal content here -->
            <div class="form-group" style="width: 100% !important">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-user"></i>
                    </span>
                    {!! Form::select('modal_contact_id', 
                        [], null, ['class' => 'form-control', 'id' => 'modal_customer_id', 'placeholder' => 'Enter Customer name / phone', 'required', 'style' => 'width: 100%;']); !!}
                  
                </div>
                <small class="text-danger hide contact_due_text_modal">
                    <strong>@lang('account.customer_due'):</strong> 
                    <span class="customer_due_amount"></span>
                </small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            <button type="button" class="btn btn-primary" id="confirm_pay_contact">@lang('messages.confirm')</button>
        </div>
    </div>
</div>
