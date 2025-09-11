    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('type_smart_root_discount_id', __('sale.discount_type') . ':') !!}
                {!! Form::select('type_smart_root_discount_id', $type_smart_root_discounts->pluck('name', 'id'), null, [
                    'class' => 'form-control select2',
                    'style' => 'width:100%',
                    'placeholder' => __('lang_v1.all'),
                    'id' => 'type_smart_root_discount_id_filter'
                ]); !!}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('business_id', __('sale.business_location') . ':') !!}
                {!! Form::select('business_id', $locations, null, [
                    'class' => 'form-control select2',
                    'style' => 'width:100%',
                    'placeholder' => __('lang_v1.all'),
                    'id' => 'business_id'
                ]); !!}
            </div>
        </div>


        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('user_id', __('sale.user') . ':') !!}
                {!! Form::select('user_id', $users, null, [
                    'class' => 'form-control select2',
                    'style' => 'width:100%',
                    'placeholder' => __('lang_v1.all'),
                    'id' => 'user_id'
                ]); !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                 {!! Form::label('smart_root_discount_date_range', __('report.date_range') . ':') !!}
                 {!! Form::text('smart_root_discount_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    </div>





