<div class="modal-dialog" role="document">
    <div class="modal-content">
  
      {!! Form::model($sector, ['url' => action([\App\Http\Controllers\CustomerSectorController::class, 'update'], [$sector->id]), 'method' => 'PUT', 'id' => 'sector_edit_form' ]) !!}
      
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">@lang( 'messages.edit' )</h4>
      </div>
  
      <div class="modal-body">
        
        <div class="form-group">
          {!! Form::label('name', __( 'lang_v1.name' ) . ':') !!}
          {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.name')]); !!}
        </div>
  
        @if(!empty($parent_sectors))
          <div class="form-group">
              <div class="checkbox">
                <label>
                   {!! Form::checkbox('add_as_sub_sec', 1, !empty($sector->parent_id),[ 'class' => 'toggler', 'data-toggle_id' => 'parent_sec_div' ]) !!} 
                   @lang( 'lang_v1.add_as_sub_txonomy' )
                </label>
              </div>
          </div>
  
          <div class="form-group {{ empty($sector->parent_id) ? 'hide' : '' }}" id="parent_sec_div">
            {!! Form::label('parent_id', __( 'category.select_parent_category' ) . ':') !!}
            {!! Form::select('parent_id', $parent_sectors, $sector->parent_id, ['class' => 'form-control']); !!}
          </div>
        @endif
  
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang( 'messages.update' )</button>
        <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
  