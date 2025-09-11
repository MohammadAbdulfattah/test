@if(!empty($activities))
<table class="table table-condensed">
    <tr>
        <th>@lang('lang_v1.date')</th>
        <th>@lang('messages.action')</th>
        <th>@lang('lang_v1.by')</th>
        <th>@lang('brand.note')</th>
    </tr>
    @forelse($activities as $activity)
        <tr>
            <td>{{@format_datetime($activity->created_at)}}</td>
            <td>
                {{__('lang_v1.' . $activity->description)}}
            </td>
            <td>
                {{$activity->causer->user_full_name ?? ''}}
                @if(!empty($activity->getExtraProperty('from_api')))
                    <br>
                    <span class="label bg-gray">{{$activity->getExtraProperty('from_api')}}</span>
                @endif

                @if(!empty($activity->getExtraProperty('is_automatic')))
                    <span class="label bg-gray">@lang('lang_v1.automatic')</span>
                @endif
            </td>
           
        </tr>
    @empty
        <tr>
          <td colspan="3" class="text-center">
            @lang('purchase.no_records_found')
          </td>
        </tr>
    @endforelse
</table>
@endif