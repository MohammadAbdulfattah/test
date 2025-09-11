@extends('layouts.app')
@section('title', __('gbs::lang.add_new_visit'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('gbs::lang.add_new_visit')</h1>

    </section>
    <section class="content">
        {!! Form::model($route, [
            'url' => action([\Modules\Gbs\Http\Controllers\RouteController::class, 'update'], [$route->id]),
            'method' => 'PUT',
            'id' => 'edit_route_form',
        ]) !!}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('name', __('gbs::lang.route_name') . ':*') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
                    </div>
                </div>
    
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('user_id', __('gbs::lang.users') . ':*') !!}
                        {!! Form::select('user_id', $users, $route->user_id, ['class' => 'form-control select2', 'required']) !!}
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
                @endphp
    
                <div class="col-sm-4">
   
                
                @foreach($days as $key => $dayName)
                
                   
                        <div class="form-group">
                            <label for="contacts_{{ $key }}">{{ __('gbs::lang.contacts_for') }} {{ $dayName }}</label>
                            {!! Form::select("contacts[$key][]", $clients, $selectedContacts[$key] ?? [], [
                                'class' => 'form-control select2-ajax',
                                'multiple',
                                'data-placeholder' => __('gbs::lang.select_clients'),
                                'data-day' => $key,
                                'id' => "contacts_$key"
                            ]) !!}
                        </div>
                  
                @endforeach
            </div>
        </div>
        @endcomponent
    
        <button type="submit" class="btn btn-primary">
            {{ __('messages.update') }}
        </button>
        {!! Form::close() !!}
    </section>
    
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
    
   