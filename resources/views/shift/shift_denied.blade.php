@extends('layouts.app')

@section('content')
<div class="container text-center mt-5">
    <h1 class="text-danger">{{ __('lang_v1.access_denied') }}</h1>
    <p>{{ __('lang_v1.you_are_not_within') }}</p>
    <p>{{ __('lang_v1.if_now') }}</p>
    <a href="/home" class="btn btn-primary mt-3">{{ __('lang_v1.go_back') }}</a>
</div>
@endsection