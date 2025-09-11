<div class="pos-tab-content">
      @php
            // Get enabled products modules from the session
        $enabled_quick_list = !empty(session('business.enabled_quick_list')) ? (session('business.enabled_quick_list')) : [];
       
        @endphp
    <div class="row">
        @if (!empty($quick_list))
                <h4>@lang('lang_v1.enable_disable_quick_access_list')</h4>
                @foreach ($quick_list as $k => $v)
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="checkbox">
                                <br>
                                <label>
                                    {!! Form::checkbox("enabled_quick_list[]", $k, in_array($k, $enabled_quick_list),
                                     ['class' => 'input-icheck']) !!} {{ $v['name'] }}
                                </label>
                                @if (!empty($v['tooltip']))
                                    @show_tooltip($v['tooltip'])
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
        @endif
    </div>
</div>
