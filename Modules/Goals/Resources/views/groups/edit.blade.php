@extends('layouts.app')

@section('content')
    <div id="group-form">
        <section class="content-header">
            <h2 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('messages.edit')</h2>
        </section>

        {{ Form::open(['url' => route("goal_group.update", $group->id), 'method' => 'POST', 'id' => 'groupForm']) }}
        <div class="row">
            <div class="col-md-12 col-sm-12">
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('name', __('user.name') . ':*') !!}
                            {!! Form::text('name', $group->name, ['class' => 'form-control', 'required', 'placeholder' => __('user.name')]) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('start_date', __('goals::goals.start_date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('start_date',@format_datetime($group->start_date), ['class' => 'form-control',  'required']); !!}
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('end_date', __('goals::goals.end_date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('end_date',@format_datetime($group->end_date), ['class' => 'form-control',  'required']); !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('supervisor_id', __('goals::goals.supervisor') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                {!! Form::select('supervisor_id', $users ?? [], $group->supervisor_id, [
                                    'class' => 'form-control select2',
                                    'id' => 'user_id',
                                    'style' => 'width: 100%;',
                                ]) !!}

                            </div>
                        </div>
                    </div>
                @endcomponent
            </div>
           
        </div>

      
            

        <div class="row">
            <div class="col-sm-12 text-center mt-4">
                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">
                    @lang('messages.save')
                </button>
            </div>
        </div>
            
        
        {!! Form::close() !!}
    </div>
@endsection
@section('javascript')
   
   
    <script>
       
    
     
        $(document).ready(function () {
           
          
    
            // Initialize datetimepickers
            $('#start_date, #end_date').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });
        }); 
    </script>
    
    
    
@endsection
