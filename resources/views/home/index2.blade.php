@extends('layouts.app')
@section('title', __('home.home'))

@section('content')
    <style>
        .total-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            /* smaller gap */
        }

        .total-grid>div {
            padding: 6px !important;
            min-height: 80px;
        }

        /* shrink icon container */
        .total-grid .tw-w-10,
        .total-grid .sm\:tw-w-12,
        .total-grid .tw-h-10,
        .total-grid .sm\:tw-h-12 {
            width: 28px !important;
            height: 28px !important;
        }

        /* shrink SVG inside */
        .total-grid svg {
            width: 16px !important;
            height: 16px !important;
        }

        /* reduce padding inside the card content */
        .total-grid .tw-p-4,
        .total-grid .sm\:tw-p-5 {
            padding: 6px !important;
        }

        /* adjust font sizes */
        .total-grid .tw-text-xl {
            font-size: 0.875rem !important;
            /* smaller text */
        }

        .total-grid .tw-text-sm {
            font-size: 0.7rem !important;
        }

        @media (max-width: 768px) {
            .total-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .total-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>


    <div class="tw-pb-6">

        <div class="tw-px-5 tw-pt-3">

            <div class="sm:tw-flex sm:tw-items-center sm:tw-justify-between sm:tw-gap-12">
                

                @if (auth()->user()->can('dashboard.data'))
                    @if ($is_admin)
                        <div class="tw-mt-2 sm:tw-w-1/3 md:tw-w-1/4">
                            @if (count($all_locations) > 1)
                                {!! Form::select('dashboard_location', $all_locations, null, [
                                    'class' => 'form-control select2 ',
                                    'placeholder' => __('lang_v1.select_location'),
                                    'id' => 'dashboard_location',
                                ]) !!}
                            @endif
                        </div>

                        <div class="tw-mt-2 sm:tw-w-1/3 md:tw-w-1/4 tw-text-right">
                            @if ($is_admin)
                                <button type="button" id="dashboard_date_filter"
                                    class="tw-inline-flex tw-items-center tw-justify-center tw-w-full tw-gap-1 tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-900 tw-transition-all tw-duration-200 tw-bg-white tw-rounded-lg sm:tw-w-auto hover:tw-bg-primary-50">
                                    <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                        <path d="M16 3v4" />
                                        <path d="M8 3v4" />
                                        <path d="M4 11h16" />
                                        <path d="M7 14h.013" />
                                        <path d="M10.01 14h.005" />
                                        <path d="M13.01 14h.005" />
                                        <path d="M16.015 14h.005" />
                                        <path d="M13.015 17h.005" />
                                        <path d="M7.01 17h.005" />
                                        <path d="M10.01 17h.005" />
                                    </svg>
                                    <span>
                                        {{ __('messages.filter_by_date') }}
                                    </span>
                                    <svg aria-hidden="true" class="tw-size-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M6 9l6 6l6 -6" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
            @if (auth()->user()->can('dashboard.data'))
                @if ($is_admin)
                    <div
                        class="tw-grid total-grid tw-grid-cols-1 tw-gap-4 tw-mt-6 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">

                        <div
                            class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md  tw-ring-1 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-4">
                                    <div
                                        class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0 tw-bg-sky-100 tw-text-sky-500">
                                        <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M17 17h-11v-14h-2" />
                                            <path d="M6 5l14 1l-1 7h-13" />
                                        </svg>
                                    </div>

                                    <div class="tw-flex-1 tw-min-w-0">
                                        <p
                                            class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                            {{ __('home.total_sell') }}
                                        </p>
                                        <p
                                            class="total_sell tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md  hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-4">
                                    <div
                                        class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-green-500 tw-bg-green-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0">
                                        <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path
                                                d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2">
                                            </path>
                                            <path
                                                d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1">
                                            </path>
                                            <path d="M12 6v10"></path>
                                        </svg>
                                    </div>

                                    <div class="tw-flex-1 tw-min-w-0">
                                        <p
                                            class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                            {{ __('lang_v1.net') }} @show_tooltip(__('lang_v1.net_home_tooltip'))
                                        </p>
                                        <p
                                            class="net tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md  hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-4">
                                    <div
                                        class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-yellow-500 tw-bg-yellow-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                        <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                            <path
                                                d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                            <path d="M9 7l1 0" />
                                            <path d="M9 13l6 0" />
                                            <path d="M13 17l2 0" />
                                        </svg>
                                    </div>

                                    <div class="tw-flex-1 tw-min-w-0">
                                        <p
                                            class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                            {{ __('home.invoice_due') }}
                                        </p>
                                        <p
                                            class="invoice_due tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md  hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-4">
                                    <div
                                        class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                        <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M21 7l-18 0" />
                                            <path d="M18 10l3 -3l-3 -3" />
                                            <path d="M6 20l-3 -3l3 -3" />
                                            <path d="M3 17l18 0" />
                                        </svg>
                                    </div>

                                    <div class="tw-flex-1 tw-min-w-0">
                                        <p
                                            class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                            {{ __('lang_v1.total_sell_return') }}
                                            <i class="fa fa-info-circle text-info hover-q no-print" aria-hidden="true"
                                                data-container="body" data-toggle="popover" data-placement="auto bottom"
                                                id="total_srp"
                                                data-value="{{ __('lang_v1.total_sell_return') }}-{{ __('lang_v1.total_sell_return_paid') }}"
                                                data-content="" data-html="true" data-trigger="hover"></i>
                                        </p>
                                        <p
                                            class="total_sell_return tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                        </p>
                                        {{-- <p class="mb-0 text-muted fs-10 mt-5">{{ __('lang_v1.total_sell_return') }}: <span
                                                        class="total_sr"></span><br>
                                                    {{ __('lang_v1.total_sell_return_paid') }}<span class="total_srp"></span></p> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

        </div>
        @if (auth()->user()->can('dashboard.data'))
            @if ($is_admin)
                <div class="tw-relative">

                    <div class="tw-px-5 tw-isolate">
                        <div
                            class="tw-grid total-grid tw-grid-cols-1 tw-gap-4 tw-mt-4 sm:tw-mt-6 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">
                            <div
                                class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm  hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0 bg-sky-100 tw-text-sky-500">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M12 3v12"></path>
                                                <path d="M16 11l-4 4l-4 -4"></path>
                                                <path d="M3 12a9 9 0 0 0 18 0"></path>
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0">
                                            <p
                                                class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('home.total_purchase') }}
                                            </p>
                                            <p
                                                class="total_purchase tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm  hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-yellow-500 tw-bg-yellow-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M12 9v4" />
                                                <path
                                                    d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                                                <path d="M12 16h.01" />
                                            </svg>
                                        </div>

                                        <div>
                                            <p class="tw-text-sm tw-font-medium tw-text-gray-500">
                                                {{ __('home.purchase_due') }}
                                            </p>
                                            <p
                                                class="purchase_due tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm  hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" />
                                                <path d="M15 14v-2a2 2 0 0 0 -2 -2h-4l2 -2m0 4l-2 -2" />
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0">
                                            <p
                                                class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('lang_v1.total_purchase_return') }}
                                                <i class="fa fa-info-circle text-info hover-q no-print" aria-hidden="true"
                                                    data-container="body" data-toggle="popover"
                                                    data-placement="auto bottom" id="total_prp"
                                                    data-value="{{ __('lang_v1.total_purchase_return') }}-{{ __('lang_v1.total_purchase_return_paid') }}"
                                                    data-content="" data-html="true" data-trigger="hover"></i>
                                            </p>
                                            <p
                                                class="total_purchase_return tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                            </p>
                                            {{-- <p class="mb-0 text-muted fs-10 mt-5">
                                                {{ __('lang_v1.total_purchase_return') }}: <span
                                                    class="total_pr"></span><br>
                                                {{ __('lang_v1.total_purchase_return_paid') }}<span
                                                    class="total_prp"></span></p> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm  hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2">
                                                </path>
                                                <path
                                                    d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1">
                                                </path>
                                                <path d="M12 6v10"></path>
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0">
                                            <p
                                                class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('lang_v1.expense') }}
                                            </p>
                                            <p
                                                class="total_expense tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- @if (!empty($widgets['after_sale_purchase_totals']))
                    @foreach ($widgets['after_sale_purchase_totals'] as $widget)
                        {!! $widget !!}
                    @endforeach
                @endif --}}
            @endif
        @endif

    </div>
    @if (auth()->user()->can('dashboard.data'))
        <div class="tw-px-5 tw-py-6 ">
            <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-gap-5 lg:tw-grid-cols-2">

                {{-- @if (!empty($widgets['after_sales_current_fy']))
                    @foreach ($widgets['after_sales_current_fy'] as $widget)
                        {!! $widget !!}
                    @endforeach
                @endif --}}
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                    <div
                        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm  tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h5 class="tw-font-bold">
                                            {{ __('lang_v1.most_selling_users') }}

                                        </h5>
                                    </div>

                                </div>
                            </div>

                            <div class="tw-flex tw-justify-start tw-mb-2">
                                <button id="toggleViewBtn4"
                                    class="tw-bg-blue-500 tw-text-white tw-px-3 tw-py-1 tw-rounded hover:tw-bg-blue-600">
                                    @lang('lang_v1.show_chart')
                                </button>
                            </div>
                            <div class="tw-flow-root tw-mt-1  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="most_selling_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('contact.name')</th>
                                                    <th>@lang('sale.sells')</th>
                                                    <th>@lang('sale.total_paid')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id="chartContainer4" class="tw-hidden tw-mt-4">
                                <canvas id="barChart4"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
                @can('expense.view')
                    <div
                        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h5 class="tw-font-bold">
                                            {{ __('lang_v1.most_expense_categories') }}
                                        </h5>
                                    </div>
                                </div>
                            </div>

                            <div class="tw-flow-root tw-mt-4 tw-border-gray-200">
                                <div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-6">

                                    <!-- Pie Chart Here -->
                                    <div class="tw-flex tw-justify-center">
                                        <canvas id="expensePieChart" class="tw-max-w-[100px] tw-max-h-[100px]"></canvas>
                                    </div>
                                    <div class="tw-font-bold" id="expensePieChartContainer"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                @endcan
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                    <div
                        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm  tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h5 class="tw-font-bold">
                                            {{ __('lang_v1.most_selling_products') }}

                                        </h5>
                                    </div>

                                </div>
                            </div>
                            <div class="tw-flex tw-justify-start tw-mb-2">
                                <button id="toggleViewBtn3"
                                    class="tw-bg-blue-500 tw-text-white tw-px-3 tw-py-1 tw-rounded hover:tw-bg-blue-600">
                                    @lang('lang_v1.show_chart')
                                </button>
                            </div>

                            <div class="tw-flow-root tw-mt-1  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="most_selling_product_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('contact.name')</th>
                                                    <th>@lang('sale.qty')</th>
                                                    <th>@lang('sale.total_amount')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id="chartContainer3" class="tw-hidden tw-mt-4">
                                <canvas id="barChart3"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                    <div
                        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm  tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h5 class="tw-font-bold">
                                            {{ __('lang_v1.most_clients') }}

                                        </h5>
                                    </div>

                                </div>
                            </div>

                            <div class="tw-flex tw-justify-start tw-mb-2">
                                <button id="toggleViewBtn2"
                                    class="tw-bg-blue-500 tw-text-white tw-px-3 tw-py-1 tw-rounded hover:tw-bg-blue-600">
                                    @lang('lang_v1.show_chart')
                                </button>
                            </div>
                            <div class="tw-flow-root tw-mt-1  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="most_clients"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('contact.name')</th>
                                                    <th>@lang('report.total_due')</th>
                                                    <th>@lang('report.total_sell')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id="chartContainer2" class="tw-hidden tw-mt-4">
                                <canvas id="barChart2"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                    <div
                        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm  tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h5 class="tw-font-bold">
                                            {{ __('lang_v1.most_selling') }}

                                        </h5>
                                    </div>

                                </div>
                            </div>

                            <div class="tw-flex tw-justify-start tw-mb-2">
                                <button id="toggleViewBtn1"
                                    class="tw-bg-blue-500 tw-text-white tw-px-3 tw-py-1 tw-rounded hover:tw-bg-blue-600">
                                    @lang('lang_v1.show_chart')
                                </button>
                            </div>
                            <div class="tw-flow-root tw-mt-1  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="most_users_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('contact.name')</th>
                                                    <th>@lang('sale.sells')</th>
                                                    <th>@lang('sale.total_paid')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id="chartContainer1" class="tw-hidden tw-mt-4">
                                <canvas id="barChart1"></canvas>
                            </div>
                        </div>
                    </div>
                @endif



                {{-- @if (!empty($widgets['after_dashboard_reports']))
                    @foreach ($widgets['after_dashboard_reports'] as $widget)
                        {!! $widget !!}
                    @endforeach
                @endif --}}
            </div>
        </div>
    @endif


@endsection
@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    @includeIf('sales_order.common_js')
    @includeIf('purchase_order.common_js')
    <script type="text/javascript">
        var expenseChart;
        var start = moment().format('YYYY-MM-DD');
        var end = moment().format('YYYY-MM-DD');
        var location_id = $('#dashboard_location').val() || '';
        var sell_table;
        var product_table;
        var clients_table;
        $(document).ready(function() {
            sell_table = $('#most_selling_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                aaSorting: [
                    [1, 'desc']
                ],
                buttons: false,
                pageLength: 10,
                lengthMenu: [10, 15, 20, 25],
                dom: '<"row"<"col-sm-12"l>>tip',
                language: {
                    lengthMenu: "_MENU_",
                },
                scrollY: "75vh",
                scrollX: true,
                info: false,
                scrollCollapse: true,
                ajax: {
                    url: '{{ action([\App\Http\Controllers\HomeController::class, 'getMostSellingCustomer']) }}',
                    type: 'GET',
                    data: function(d) {
                        d.start_date = start;
                        d.end_date = end;
                        d.location_id = location_id;
                    }
                },

                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'sells',
                        name: 'sells'
                    },
                    {
                        data: 'total_paid',
                        name: 'total_paid'
                    },
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#most_selling_table'));
                },
            });
            users_table = $('#most_users_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                aaSorting: [
                    [1, 'desc']
                ],
                buttons: false,
                pageLength: 10,
                lengthMenu: [10, 15, 20, 25],
                dom: '<"row"<"col-sm-12"l>>tip',
                language: {
                    lengthMenu: "_MENU_",
                },
                scrollY: "75vh",
                scrollX: true,
                info: false,
                scrollCollapse: true,
                ajax: {
                    url: '{{ action([\App\Http\Controllers\HomeController::class, 'getMostSellingUsers']) }}',
                    type: 'GET',
                    data: function(d) {
                        d.start_date = start;
                        d.end_date = end;
                        d.location_id = location_id;
                    }
                },

                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'sells',
                        name: 'sells'
                    },
                    {
                        data: 'total_paid',
                        name: 'total_paid'
                    },
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#most_users_table'));
                },
            });
            product_table = $('#most_selling_product_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                buttons: false,
                aaSorting: [
                    [1, 'desc']
                ],
                pageLength: 10,
                lengthMenu: [10, 15, 20, 25],
                dom: '<"row"<"col-sm-12"l>>tip',
                language: {
                    lengthMenu: "_MENU_",
                },
                scrollY: "75vh",
                scrollX: true,
                info: false,
                scrollCollapse: true,
                ajax: {
                    url: '{{ action([\App\Http\Controllers\HomeController::class, 'getMostSellingProducts']) }}',
                    type: 'GET',
                    data: function(d) {
                        d.start_date = start;
                        d.end_date = end;
                        d.location_id = location_id;
                    }
                },
                columns: [{
                        data: 'name',
                        name: 'name',
                        orderable: true
                    },
                    {
                        data: 'total_quantity',
                        name: 'total_quantity',
                        orderable: true
                    },
                    {
                        data: 'final_total',
                        name: 'final_total',
                        orderable: true
                    },
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#most_selling_product_table'));
                },
            });

            clients_table = $('#most_clients').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                aaSorting: [
                    [1, 'desc']
                ],
                buttons: false,
                pageLength: 10,
                lengthMenu: [10, 15, 20, 25],
                dom: '<"row"<"col-sm-12"l>>tip',
                language: {
                    lengthMenu: "_MENU_",
                },
                scrollY: "75vh",
                scrollX: true,
                info: false,
                scrollCollapse: true,
                ajax: {
                    url: '{{ action([\App\Http\Controllers\HomeController::class, 'getMostIndebtedCustomer']) }}',
                    type: 'GET',
                    data: function(d) {
                        d.start_date = start;
                        d.end_date = end;
                        d.location_id = location_id;
                    }
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'due',
                        name: 'due',
                        orderable: false
                    },
                    {
                        data: 'total_sell',
                        name: 'total_sell'
                    },
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#most_clients'));
                },
            });

            if ($('#dashboard_date_filter').length == 1) {
                dateRangeSettings.startDate = moment();
                dateRangeSettings.endDate = moment();

                $('#dashboard_date_filter').daterangepicker(dateRangeSettings, function(startDate, endDate) {
                    start = startDate.format('YYYY-MM-DD');
                    end = endDate.format('YYYY-MM-DD');

                    $('#dashboard_date_filter span').html(
                        startDate.format(moment_date_format) + ' ~ ' + endDate.format(
                            moment_date_format)
                    );

                    reloadAll();
                });

                reloadAll();
            }

            $('#dashboard_location').change(function(e) {
                location_id = $('#dashboard_location').val() || '';
                reloadAll();
            });


        });

        function reloadAll() {
            update_statistics(start, end);
            get_expense(start, end);

            sell_table.ajax.reload();
            product_table.ajax.reload();
            clients_table.ajax.reload();
            users_table.ajax.reload();
        }

        function get_expense(start, end) {
            $.ajax({
                url: '/home/get-expenses',
                method: 'GET',
                dataType: 'json',
                data: {
                    start_date: start,
                    end_date: end,
                    location_id: location_id
                },
                success: function(response) {
                    const expenseData = response.data;

                    const colors = generateColors(expenseData.length);
                    const container = document.getElementById('expensePieChartContainer');
                    container.innerHTML = '';
                    if (expenseChart) {
                        expenseChart.destroy();
                        expenseChart = null;
                    }
                    if (expenseData.length === 0) {
                        document.getElementById('expensePieChartContainer').innerHTML =
                            '<p>@lang('lang_v1.no_data').</p>';
                        return;
                    }
                    const ctx = document.getElementById('expensePieChart').getContext('2d');
                    expenseChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: expenseData.map(item => item.category),
                            datasets: [{
                                data: expenseData.map(item => item.amount),
                                backgroundColor: colors,
                                hoverOffset: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 12
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        }


        function generateColors(count) {
            const colorPalette = [
                '#14b8a6', '#3b82f6', '#f59e0b', '#ef4444',
                '#6366f1', '#ec4899', '#22d3ee',
                '#84cc16', '#f43f5e', '#a855f7', '#10b981', '#f97316'
            ];
            let colors = [];
            for (let i = 0; i < count; i++) {
                colors.push(colorPalette[i % colorPalette.length]);
            }
            return colors;
        }

        function update_statistics(start, end) {
            var location_id = '';
            if ($('#dashboard_location').length > 0) {
                location_id = $('#dashboard_location').val();
            }
            var data = {
                start: start,
                end: end,
                location_id: location_id
            };
            //get purchase details
            var loader = '<i class="fas fa-sync fa-spin fa-fw margin-bottom"></i>';
            $('.total_purchase').html(loader);
            $('.purchase_due').html(loader);
            $('.total_sell').html(loader);
            $('.invoice_due').html(loader);
            $('.total_expense').html(loader);
            $('.total_purchase_return').html(loader);
            $('.total_sell_return').html(loader);
            $('.net').html(loader);
            $.ajax({
                method: 'get',
                url: '/home/get-totals',
                dataType: 'json',
                data: data,
                success: function(data) {
                    //purchase details
                    $('.total_purchase').html(__currency_trans_from_en(data.total_purchase, true));
                    $('.purchase_due').html(__currency_trans_from_en(data.purchase_due, true));

                    //sell details
                    $('.total_sell').html(__currency_trans_from_en(data.total_sell, true));
                    $('.invoice_due').html(__currency_trans_from_en(data.invoice_due, true));
                    //expense details
                    $('.total_expense').html(__currency_trans_from_en(data.total_expense, true));
                    var total_purchase_return = data.total_purchase_return - data.total_purchase_return_paid;
                    $('.total_purchase_return').html(__currency_trans_from_en(total_purchase_return, true));
                    var total_sell_return_due = data.total_sell_return - data.total_sell_return_paid;
                    $('.total_sell_return').html(__currency_trans_from_en(total_sell_return_due, true));
                    $('.total_sr').html(__currency_trans_from_en(data.total_sell_return, true));
                    $('.total_srp').html(__currency_trans_from_en(data.total_sell_return_paid, true));
                    $('.total_pr').html(__currency_trans_from_en(data.total_purchase_return, true));
                    $('.total_prp').html(__currency_trans_from_en(data.total_purchase_return_paid, true));
                    $('.net').html(__currency_trans_from_en(data.net, true));

                    // assign tooltip total_sell_return 
                    var lang = $('#total_srp').data('value');
                    var splitlang = lang.split('-');

                    var newContent = "<p class='mb-0 text-muted fs-10 mt-5'>" + splitlang[0] +
                        ": <span class=''>" + __currency_trans_from_en(data.total_sell_return, true) +
                        "</span><br>" + splitlang[1] + ": <span class=''>" + __currency_trans_from_en(data
                            .total_sell_return_paid, true) + "</span></p>";
                    $('#total_srp').attr('data-content', newContent)
                    // assign tooltip total_purchase_return 
                    var lang = $('#total_prp').data('value');
                    var splitlang = lang.split('-');

                    var newContent = "<p class='mb-0 text-muted fs-10 mt-5'>" + splitlang[0] +
                        ": <span class=''>" + __currency_trans_from_en(data.total_purchase_return, true) +
                        "</span><br>" + splitlang[1] + ": <span class=''>" + __currency_trans_from_en(data
                            .total_purchase_return_paid, true) + "</span></p>";

                    $('#total_prp').attr('data-content', newContent);

                },
            });

        }
        let barChart = null;
        document.getElementById('toggleViewBtn1').addEventListener('click', function() {
            const tableWrapper = document.getElementById('most_users_table').closest('.tw-inline-block');
            const chartContainer = document.getElementById('chartContainer1');
            const btn = this;

            if (chartContainer.classList.contains('tw-hidden')) {
                chartContainer.classList.remove('tw-hidden');
                tableWrapper.classList.add('tw-hidden');
                btn.textContent = '@lang('lang_v1.show_table')';

                // Create or update chart from DataTable
                const table = $('#most_users_table').DataTable();

                table.one('draw', function() {
                    const chartLabels = [];
                    const chartData = [];

                    table.rows({
                        search: 'applied'
                    }).every(function() {
                        const data = this.data();
                        chartLabels.push(data.name);

                        // Use unformatCurrency to clean and parse the numeric value
                        const cleanedValue = unformatCurrency(data.sells);
                        chartData.push({
                            raw: cleanedValue,
                            display: data
                                .sells // Keep the original formatted string for the tooltip
                        });
                    });

                    // Destroy old chart if exists
                    if (barChart) barChart.destroy();

                    const ctx = document.getElementById('barChart1').getContext('2d');
                    barChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                label: '@lang('sale.sells')',
                                data: chartData.map(d => d
                                .raw), // Use raw cleaned values for the chart
                                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: '@lang('lang_v1.most_selling')'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const index = context.dataIndex;
                                            return chartData[index]
                                            .display; // Show original formatted value in tooltip
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: function(value, index, ticks) {
                                            // Display in 'k' for thousands and 'M' for millions
                                            if (value >= 1000000) {
                                                return (value / 1000000).toFixed(1) + 'M';
                                            } else if (value >= 1000) {
                                                return (value / 1000).toFixed(1) + 'k';
                                            } else {
                                                return value;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });
                });

                // Force re-draw if not triggered
                table.draw();

            } else {
                chartContainer.classList.add('tw-hidden');
                tableWrapper.classList.remove('tw-hidden');
                btn.textContent = '@lang('lang_v1.show_chart')';

                // Reinitialize the DataTable to reapply the styles when returning to the table view
                const table = $('#most_users_table').DataTable();
                table.ajax.reload(); // Reload the table data
                table.columns.adjust().draw(); // Adjust column widths and redraw the table
            }
        });

        let barChart2 = null;
        document.getElementById('toggleViewBtn2').addEventListener('click', function() {
            const tableWrapper = document.getElementById('most_clients').closest('.tw-inline-block');
            const chartContainer = document.getElementById('chartContainer2');
            const btn = this;

            if (chartContainer.classList.contains('tw-hidden')) {
                chartContainer.classList.remove('tw-hidden');
                tableWrapper.classList.add('tw-hidden');
                btn.textContent = '@lang('lang_v1.show_table')';

                // Create or update chart from DataTable
                const table = $('#most_clients').DataTable();

                table.one('draw', function() {
                    const chartLabels = [];
                    const chartData = [];

                    table.rows({
                        search: 'applied'
                    }).every(function() {
                        const data = this.data();
                        chartLabels.push(data.name);


                        const rawValue = $(this.node()).find('.total_due').data('orig-value');
                        const displayValue = $(this.node()).find('.total_due').text();
                        chartData.push({
                            raw: parseFloat(rawValue),
                            display: displayValue
                        });
                    });


                    // Destroy old chart if exists
                    if (barChart2) barChart2.destroy();

                    const ctx = document.getElementById('barChart2').getContext('2d');
                    barChart2 = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                label: '@lang('report.total_due')',
                                data: chartData.map(d => d
                                .raw), // Use raw cleaned values for the chart
                                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: '@lang('lang_v1.most_clients')'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const index = context.dataIndex;
                                            return chartData[index]
                                            .display; // Show original formatted value in tooltip
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: function(value, index, ticks) {
                                            // Display in 'k' for thousands and 'M' for millions
                                            if (value >= 1000000) {
                                                return (value / 1000000).toFixed(1) + 'M';
                                            } else if (value >= 1000) {
                                                return (value / 1000).toFixed(1) + 'k';
                                            } else {
                                                return value;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });
                });

                // Force re-draw if not triggered
                table.draw();

            } else {
                chartContainer.classList.add('tw-hidden');
                tableWrapper.classList.remove('tw-hidden');
                btn.textContent = '@lang('lang_v1.show_chart')';

                // Reinitialize the DataTable to reapply the styles when returning to the table view
                const table = $('#most_clients').DataTable();
                table.ajax.reload(); // Reload the table data
                table.columns.adjust().draw(); // Adjust column widths and redraw the table
            }
        });
        let barChart3 = null;
        document.getElementById('toggleViewBtn3').addEventListener('click', function() {
            const tableWrapper = document.getElementById('most_selling_product_table').closest('.tw-inline-block');
            const chartContainer = document.getElementById('chartContainer3');
            const btn = this;

            if (chartContainer.classList.contains('tw-hidden')) {
                chartContainer.classList.remove('tw-hidden');
                tableWrapper.classList.add('tw-hidden');
                btn.textContent = '@lang('lang_v1.show_table')';

                // Create or update chart from DataTable
                const table = $('#most_selling_product_table').DataTable();

                table.one('draw', function() {
                    const chartLabels = [];
                    const chartData = [];

                    table.rows({
                        search: 'applied'
                    }).every(function() {
                        const data = this.data();
                        chartLabels.push(data.name);

                        // Use unformatCurrency to clean and parse the numeric value

                        chartData.push({
                            raw: data.total_quantity,
                            display: data
                                .total_quantity // Keep the original formatted string for the tooltip
                        });
                    });

                    // Destroy old chart if exists
                    if (barChart3) barChart3.destroy();

                    const ctx = document.getElementById('barChart3').getContext('2d');
                    barChart3 = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                label: '@lang('sale.qty')',
                                data: chartData.map(d => d
                                .raw), // Use raw cleaned values for the chart
                                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: '@lang('lang_v1.most_selling_products')'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const index = context.dataIndex;
                                            return chartData[index]
                                            .display; // Show original formatted value in tooltip
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: function(value, index, ticks) {
                                            // Display in 'k' for thousands and 'M' for millions
                                            if (value >= 1000000) {
                                                return (value / 1000000).toFixed(1) + 'M';
                                            } else if (value >= 1000) {
                                                return (value / 1000).toFixed(1) + 'k';
                                            } else {
                                                return value;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });
                });

                // Force re-draw if not triggered
                table.draw();

            } else {
                chartContainer.classList.add('tw-hidden');
                tableWrapper.classList.remove('tw-hidden');
                btn.textContent = '@lang('lang_v1.show_chart')';

                // Reinitialize the DataTable to reapply the styles when returning to the table view
                const table = $('#most_selling_product_table').DataTable();
                table.ajax.reload(); // Reload the table data
                table.columns.adjust().draw(); // Adjust column widths and redraw the table
            }
        });
        let barChart4 = null;
        document.getElementById('toggleViewBtn4').addEventListener('click', function() {
            const tableWrapper = document.getElementById('most_selling_table').closest('.tw-inline-block');
            const chartContainer = document.getElementById('chartContainer4');
            const btn = this;

            if (chartContainer.classList.contains('tw-hidden')) {
                chartContainer.classList.remove('tw-hidden');
                tableWrapper.classList.add('tw-hidden');
                btn.textContent = '@lang('lang_v1.show_table')';

                // Create or update chart from DataTable
                const table = $('#most_selling_table').DataTable();

                table.one('draw', function() {
                    const chartLabels = [];
                    const chartData = [];

                    table.rows({
                        search: 'applied'
                    }).every(function() {
                        const data = this.data();
                        chartLabels.push(data.name);

                        // Use unformatCurrency to clean and parse the numeric value
                        const cleanedValue = unformatCurrency(data.sells);
                        chartData.push({
                            raw: cleanedValue,
                            display: data
                                .sells // Keep the original formatted string for the tooltip
                        });
                    });

                    // Destroy old chart if exists
                    if (barChart4) barChart4.destroy();

                    const ctx = document.getElementById('barChart4').getContext('2d');
                    barChart4 = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                label: '@lang('sale.sells')',
                                data: chartData.map(d => d
                                .raw), // Use raw cleaned values for the chart
                                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: '@lang('lang_v1.most_selling_users')'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const index = context.dataIndex;
                                            return chartData[index]
                                            .display; // Show original formatted value in tooltip
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: function(value, index, ticks) {
                                            // Display in 'k' for thousands and 'M' for millions
                                            if (value >= 1000000) {
                                                return (value / 1000000).toFixed(1) + 'M';
                                            } else if (value >= 1000) {
                                                return (value / 1000).toFixed(1) + 'k';
                                            } else {
                                                return value;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });
                });

                // Force re-draw if not triggered
                table.draw();

            } else {
                chartContainer.classList.add('tw-hidden');
                tableWrapper.classList.remove('tw-hidden');
                btn.textContent = '@lang('lang_v1.show_chart')';

                // Reinitialize the DataTable to reapply the styles when returning to the table view
                const table = $('#most_selling_table').DataTable();
                table.ajax.reload(); // Reload the table data
                table.columns.adjust().draw(); // Adjust column widths and redraw the table
            }
        });

        function unformatCurrency(value) {
            const symbol = '{{ session('currency.symbol') }}';
            const decimal = '{{ session('currency.decimal_separator') }}';
            const thousand = '{{ session('currency.thousand_separator') }}';

            // Remove symbol
            let cleaned = value.replace(symbol, '').trim();

            // Remove thousand separator
            cleaned = cleaned.split(thousand).join('');

            // Convert decimal separator to dot
            if (decimal !== '.') {
                cleaned = cleaned.replace(decimal, '.');
            }

            return parseFloat(cleaned);
        }
    </script>

@endsection
<style>
    .dataTables_paginate {
        display: none;
    }

    .dataTables_wrapper .dataTables_length {
        float: right;
        /* Default for LTR */
        margin-bottom: 10px;
    }

    html[dir="rtl"] .dataTables_wrapper .dataTables_length {
        float: left;
        /* For RTL */
    }
</style>
