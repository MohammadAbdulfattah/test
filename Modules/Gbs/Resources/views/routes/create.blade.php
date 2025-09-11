@extends('layouts.app')
@section('title', __('gbs::lang.add_new_visit'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('gbs::lang.add_new_visit')</h1>

    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open([
            'url' => action([\Modules\Gbs\Http\Controllers\RouteController::class, 'store']),
            'method' => 'post',
            'id' => 'add_daily_visites',
            'files' => true,
        ]) !!}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
               
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('name', __('gbs::lang.route_name') . ':*') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('gbs::lang.route_name')]) !!}
                    </div>
                  <div class="form-group">
                    {!! Form::label('user_id', __('gbs::lang.users') . ':*') !!}
                    {!! Form::select('user_id', $users, null, ['class' => 'form-control select2', 'required']) !!}
                  </div>
                
                </div>
                @php
                $days = [
                    'sunday' => __('gbs::lang.sunday'),
                    'monday' => __('gbs::lang.monday'),
                    'tuesday' => __('gbs::lang.tuesday'),
                    'wednesday' => __('gbs::lang.wednesday'),
                    'thursday' => __('gbs::lang.thursday'),
                    'saturday' => __('gbs::lang.saturday'),
                    'friday' => __('gbs::lang.friday'),
                ];
                $contactsDay=__('gbs::lang.contacts_for')
            @endphp
             <div class="col-sm-4">
            @foreach($days as $key => $day)
                <div class="form-group">
                    <label for="contacts_{{ $key }}">{{ $contactsDay}}  {{ $day }}</label>
                    {!! Form::select("contacts[$key][]", [], null, [
                        'class' => 'form-control select2-ajax',
                        'multiple',
                        'data-placeholder' => 'اختر العملاء...',
                        'data-day' => $key,
                        'id' => "contacts_$key"
                    ]) !!}
                    
                </div>
            @endforeach
        </div>
            </div>    

        @endcomponent
        <button type="submit" value="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white submit_product_form">@lang('messages.save')</button>
        {!! Form::close() !!}
    </section> 
    <!-- /.content -->


 

@endsection
@section('javascript')

<script>
    $(document).ready(function () {
        $('.select2-ajax').select2({
            placeholder: 'اختر العملاء...',
            minimumInputLength: 1,
            ajax: {
                url: '{{ route("gbs.clients.search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term 
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                },
                cache: true
            }
        });
       

    });
    </script>
    
@endsection
