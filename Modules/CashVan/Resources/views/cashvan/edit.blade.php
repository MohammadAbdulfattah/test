
    {!! Form::open(['url' => action([Modules\CashVan\Http\Controllers\CashVanController::class, 'update'], [$cashvan->id]), 'method' => 'PUT', 'id' => 'contact_edit_form']) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('cashvan::role.cashvan.update')</h4>
    </div>

    <div class="modal-body">

      <div class="row">
                <div class="col-md-4 business">
                         <div class="form-group">
                             {!! Form::label('name', __('user.name') . ':*') !!}
                             <div class="input-group">

                                 {!! Form::text('name', $cashvan->name, ['class' => 'form-control', 'required', 'placeholder' => __('user.name')]) !!}
                             </div>
                         </div>
                     </div>
                     <div class="col-md-4 business">
                         <div class="form-group">
                             {!! Form::label('color', __('cashvan::cashvan.color') . ':') !!}
                             <div class="input-group">

                                 {!! Form::text('color', $cashvan->color, ['class' => 'form-control', 'placeholder' => __('cashvan::cashvan.color')]) !!}
                             </div>
                         </div>
                     </div>
                     <div class="col-md-4 business">
                         <div class="form-group">
                             {!! Form::label('license_plate', __('cashvan::cashvan.license_plate') . ':*') !!}
                             <div class="input-group">

                                 {!! Form::text('license_plate', $cashvan->license_plate, [
                                     'class' => 'form-control',
                                     'required',
                                     'placeholder' => __('cashvan::cashvan.license_plate'),
                                 ]) !!}
                             </div>
                         </div>
                     </div>

                     <div class="col-md-4 business">
                         <div class="form-group">
                             {!! Form::label('driver_id', __('cashvan::cashvan.driver') . ':') !!}
                             <div class="input-group">
                                 <span class="input-group-addon">
                                     <i class="fa fa-user"></i>
                                 </span>
                                 {!! Form::select('driver_id', $users ?? [], $cashvan->driver_id, [
                                     'class' => 'form-control select2',
                                     'id' => 'user_id',
                                     'style' => 'width: 100%;',
                                 ]) !!}

                             </div>
                         </div>
                     </div>
                     @php
                         $default_location = null;
                         if (count($business_locations) == 1) {
                             $default_location = array_key_first($business_locations->toArray());
                         }
                     @endphp
                     <div class="col-md-4 business">
                         <div class="form-group">
                             {!! Form::label('van_locations', __('business.business_locations') . ':') !!}
                             <div class="input-group">
                                 <span class="input-group-addon">

                                 </span>
                                 {!! Form::select('van_locations[]', $business_locations, $cashvan->van_locations ?? [], ['class' => 'form-control select2',  
                                     'id' => 'van_locations',
                                     'style' => 'width: 100%;',
                                 ]) !!}
                             </div>
                         </div>
                     </div>
       
                </div>
        </div>

    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang( 'messages.update' )</button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

