<div class="pos-tab-content">
      @php
            // Get enabled products modules from the session
        $enabled_products_modules = !empty(session('business.enabled_products_modules')) ? (session('business.enabled_products_modules')) : [];
        $enabled_sale_modules = !empty(session('business.enabled_sale_modules')) ? (session('business.enabled_sale_modules')) : [];
        $enabled_purchases_modules = !empty(session('business.enabled_purchases_modules')) ? (session('business.enabled_purchases_modules')) : [];
        $enabled_user_modules = !empty(session('business.enabled_user_modules')) ? (session('business.enabled_user_modules')) : [];
        $enabled_contacts_modules = !empty(session('business.enabled_contacts_modules')) ? (session('business.enabled_contacts_modules')) : [];
        $enabled_expenses_modules = !empty(session('business.enabled_expenses_modules')) ? (session('business.enabled_expenses_modules')) : [];
        @endphp
    <div class="row">

        @if (!empty($products_modules))
                <h4>@lang('lang_v1.enable_disable_products_modules')</h4>
                @foreach ($products_modules as $k => $v)
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="checkbox">
                                <br>
                                <label>
                                    {!! Form::checkbox("enabled_products_modules[]", $k, in_array($k, $enabled_products_modules),
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
    <div class="row">

        @if (!empty($sale_modules))
    
                <h4>@lang('lang_v1.enable_disable_sale_modules')</h4>
                @foreach ($sale_modules as $k => $v)
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="checkbox">
                                <br>
                                <label>
                                    {!! Form::checkbox("enabled_sale_modules[]", $k,in_array($k,$enabled_sale_modules),
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
    <div class="row">

        @if (!empty($purchases_modules))
      
                <h4>@lang('lang_v1.enable_disable_purchases_modules')</h4>
                @foreach ($purchases_modules as $k => $v)
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="checkbox">
                                <br>
                                <label>
                                    {!! Form::checkbox('enabled_purchases_modules[]', $k, in_array($k, $enabled_purchases_modules),
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
  <div class="row">

        @if (!empty($user_modules))
      
                <h4>@lang('lang_v1.enable_disable_user_modules')</h4>
                @foreach ($user_modules as $k => $v)
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="checkbox">
                                <br>
                                <label>
                                    {!! Form::checkbox('enabled_user_modules[]', $k, in_array($k, $enabled_user_modules),
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
    <div class="row">

        @if (!empty($expenses_modules))
      
                <h4>@lang('lang_v1.enable_disable_expenses_modules')</h4>
                @foreach ($expenses_modules as $k => $v)
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="checkbox">
                                <br>
                                <label>
                                    {!! Form::checkbox('enabled_expenses_modules[]', $k, in_array($k, $enabled_expenses_modules),
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
    <div class="row">

        @if (!empty($contacts_modules))
      
                <h4>@lang('lang_v1.enable_disable_contacts_modules')</h4>
                @foreach ($contacts_modules as $k => $v)
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="checkbox">
                                <br>
                                <label>
                                    {!! Form::checkbox('enabled_contacts_modules[]', $k, in_array($k, $enabled_contacts_modules),
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
