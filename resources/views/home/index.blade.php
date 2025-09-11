@extends('layouts.app')
@section('title', __('home.home'))

@section('content')

  <style>
    
  
    .quick-access-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
  .quick-access-grid2 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    
    .equal-height {
        @apply flex items-center justify-center text-sm font-medium rounded-lg transition text-white text-center;
         padding: 0.25rem 0.5rem; /* Reduced padding */
        min-height: 35px;
        height: 35px;
    }
    @media (min-width: 640px) {
        .quick-access-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        
    }

    @media (min-width: 768px) {
        .quick-access-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .quick-access-grid {
            grid-template-columns: repeat(6, 1fr);
        }
    }
</style>
@php
    $enabled_quick_list = !empty(session('business.enabled_quick_list')) ? (session('business.enabled_quick_list')) : [];
@endphp
<div class="tw-px-4 tw-pt-4 tw-pb-0">
    <div class="tw-flex tw-items-center tw-gap-1 tw-mb-1">
                     <h1 class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-gray-800">
                    {{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }}
                </h1>
    </div>
    @can('dashboard.statistics')
        <div class="tw-p-2 sm:tw-p-2">
            <div class="quick-access-grid">
                <a href="{{route('statistics')}}" id="go-to-statistics" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-1 tw-text-sm tw-transition 
                        tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                        tw-text-white">
                        
                        <span class="tw-mt-1">{{__('lang_v1.statistics')}}</span>
                </a>
            </div>
        </div>
    @endcan
     
    <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-gap-5 lg:tw-grid-cols-2">
        @can('dashboard.quick')
            
        
         @if(count($enabled_quick_list)>0)
            <div class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
            
            <div class="tw-p-4 sm:tw-p-5">
                
                <div class="tw-flex tw-items-center tw-gap-1 tw-mb-1">
                    <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">{{__('lang_v1.quick_access_list')}}</h3>
                </div>

                <div class="quick-access-grid">
                   
                        <a href="{{route('users.create')}}" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_add_user', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_add_user')}}</span>
                        </a>
                         <a href="{{route('purchases.create')}}" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_add_purchase', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_add_purchase')}}</span>
                        </a> <a href="{{route('products.create')}}" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_add_product', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_add_product')}}</span>
                        </a> <a href="{{route('sells.create')}}" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_add_sale', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_add_sale')}}</span>
                        </a> <a href="{{route('pos.create')}}" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_sale_point', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_sale_point')}}</span>
                        </a> <a href="{{route('stock-transfers.create')}}" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_add_stock_transfer', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_add_stock_transfer')}}</span>
                        </a> <a href="{{route('stock-adjustments.create')}}" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_stock_adjustment', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_stock_adjustment')}}</span>
                        </a> <a href="{{route('expenses.create')}}" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_add_expense', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_add_expense')}}</span>
                        </a> <a href="/reports/profit-loss" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_profit_loss_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_profit_loss_report')}}</span>
                        </a> <a href="/reports/purchase-sell" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_purchase_sell_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_purchase_sell_report')}}</span>
                        </a> <a href="/reports/tax-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_taxes_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_taxes_report')}}</span>
                        </a> <a href="/reports/customer-supplier" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_customer_supplier_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_customer_supplier_report')}}</span>
                        </a> <a href="/reports/customer-group" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_customer_group_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_customer_group_report')}}</span>
                        </a> <a href="/reports/stock-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_stock_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_stock_report')}}</span>
                        </a> <a href="/reports/stock-adjustment-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_stock_adjustment_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_stock_adjustment_report')}}</span>
                        </a> <a href="/reports/items-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_items_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_items_report')}}</span>
                        </a> <a href="/reports/product-purchase-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_product_purchase_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_product_purchase_report')}}</span>
                        </a> <a href="/reports/product-sell-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_product_sell_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_product_sell_report')}}</span>
                        </a> <a href="/reports/purchase-payment-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_purchase_payment_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_purchase_payment_report')}}</span>
                        </a> <a href="/reports/sell-payment-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_sell_payment_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_sell_payment_report')}}</span>
                        </a> <a href="/reports/expense-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_expense_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_expense_report')}}</span>
                        </a> <a href="/reports/register-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_register_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_register_report')}}</span>
                        </a> <a href="/reports/sales-representative-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_sales_representative_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_sales_representative_report')}}</span>
                        </a> <a href="/reports/table-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_table_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_table_report')}}</span>
                        </a> <a href="/reports/service-staff-report" class="tw-dw-btn equal-height tw-text-center tw-font-bold tw-rounded tw-py-2 tw-text-sm tw-transition 
                         tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 
                           tw-text-white @if(!in_array('enable_service_staff_report', $enabled_quick_list)) hide @endif">
                            
                            <span class="tw-mt-1">{{__('lang_v1.enable_service_staff_report')}}</span>
                        </a>
                   
                </div>

            </div>
        </div>
        @endif
        @endcan
    </div>
</div>
    @if (auth()->user()->can('dashboard.data'))
        <div class="tw-px-5 tw-py-6">
            <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-gap-5 lg:tw-grid-cols-2">
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view') || auth()->user()->can('dashboard.charts'))
                    @if (!empty($all_locations))
                        <div
                            class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200" >
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5 cursor-pointer" data-toggle="collapse" data-parent="#accordion"
                                    onclick="toggleCollapse('collapseStatus1')">
                                    <div
                                        class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                        <svg aria-hidden="true" class="tw-size-5 tw-text-sky-500 tw-shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 17h-11v-14h-2"></path>
                                            <path d="M6 5l14 1l-1 7h-13"></path>
                                        </svg>
                                    </div>

                                    <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                        {{ __('home.sells_last_30_days') }}
                                    </h3>
                                </div>
                                <div id="collapseStatus1"  class="panel-collapse collapse tw-pt-4 tw-pb-4"
                                        aria-expanded="true">
                                    <div class="tw-mt-5">
                                        <div
                                            class="tw-grid tw-w-full tw-h-100 tw-border tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50 ">
                                            <p class="tw-text-sm tw-italic tw-font-normal tw-text-gray-400">
                                                {!! $sells_chart_1->container() !!}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- @if (!empty($widgets['after_sales_last_30_days']))
                        @foreach ($widgets['after_sales_last_30_days'] as $widget)
                            {!! $widget !!}
                        @endforeach
                    @endif --}}
                    @if (!empty($all_locations))
                       <div
                            class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5 cursor-pointer" data-toggle="collapse" data-parent="#accordion"
                                    onclick="toggleCollapse('collapseStatus')">
                                    <div
                                        class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                        <svg aria-hidden="true" class="tw-size-5 tw-text-sky-500 tw-shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 17h-11v-14h-2"></path>
                                            <path d="M6 5l14 1l-1 7h-13"></path>
                                        </svg>
                                    </div>
                                    <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                        {{ __('home.sells_current_fy') }}
                                    </h3>
                                </div>
                                <div id="collapseStatus"
                                    class="panel-collapse collapse tw-pt-4 tw-pb-4"
                                    aria-expanded="true">
                                    <div class="tw-mt-5">
                                        <div
                                            class="tw-grid tw-w-full tw-h-100 tw-border tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50 ">
                                            <p class="tw-text-sm tw-italic tw-font-normal tw-text-gray-400">
                                                {!! $sells_chart_2->container() !!}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endif
                @endif
                {{-- @if (!empty($widgets['after_sales_current_fy']))
                    @foreach ($widgets['after_sales_current_fy'] as $widget)
                        {!! $widget !!}
                    @endforeach
                @endif --}}
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v4"></path>
                                        <path
                                            d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                        </path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2 cursor-pointer" data-toggle="collapse"
                                    data-parent="#accordion" onclick="toggleCollapse2('collapseStatus2')">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('lang_v1.sales_payment_dues') }}
                                            @show_tooltip(__('lang_v1.tooltip_sales_payment_dues'))
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        {!! Form::select('sales_payment_dues_location', $all_locations, null, [
                                            'class' => 'form-control select2',
                                            'placeholder' => __('lang_v1.select_location'),
                                            'id' => 'sales_payment_dues_location',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                             <div id="collapseStatus2" class="panel-collapse collapse tw-pt-4 tw-pb-4"
                                    aria-expanded="true">

                                <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                    <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                            <table class="table table-bordered table-striped" id="sales_payment_dues_table"
                                                style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('contact.customer')</th>
                                                        <th>@lang('sale.invoice_no')</th>
                                                        <th>@lang('home.due_amount')</th>
                                                        <th>@lang('messages.action')</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                             </div>    
                        </div>
                    </div>
                @endif
                @can('purchase.view')
                    <div
                        class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10" data-toggle="collapse" data-parent="#accordion" href="#collapseStatus3">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v4"></path>
                                        <path
                                            d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                        </path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2 cursor-pointer" data-toggle="collapse"
                                    data-parent="#accordion" onclick="toggleCollapse3('collapseStatus3')">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('lang_v1.purchase_payment_dues') }}
                                            @show_tooltip(__('tooltip.payment_dues'))
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('purchase_payment_dues_location', $all_locations, null, [
                                                'class' => 'form-control select2 ',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'purchase_payment_dues_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>

                            </div>
                               <div id="collapseStatus3" class="panel-collapse collapse tw-pt-4 tw-pb-4"
                                    aria-expanded="true">
                                <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                    <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                            <table class="table table-bordered table-striped" id="purchase_payment_dues_table"
                                                style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('purchase.supplier')</th>
                                                        <th>@lang('purchase.ref_no')</th>
                                                        <th>@lang('home.due_amount')</th>
                                                        <th>@lang('messages.action')</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                             </div>    
                        </div>
                    </div>
                @endcan
                @can('stock_report.view')
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                                        <path d="M12 8v4"></path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2 cursor-pointer" data-toggle="collapse"
                                    data-parent="#accordion" onclick="toggleCollapse4('collapseStatus4')">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('home.product_stock_alert') }}
                                            @show_tooltip(__('tooltip.product_stock_alert'))
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('stock_alert_location', $all_locations, null, [
                                                'class' => 'form-control select2',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'stock_alert_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                               <div id="collapseStatus4" class="panel-collapse collapse tw-pt-4 tw-pb-4"
                                    aria-expanded="true">
                                <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                    <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                            <table class="table table-bordered table-striped" id="stock_alert_table"
                                                style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('sale.product')</th>
                                                        <th>@lang('business.location')</th>
                                                        <th>@lang('report.current_stock')</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                             </div>    
                        </div>
                    </div>
                    @if (session('business.enable_product_expiry') == 1)
                        <div
                            class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5">
                                    <div
                                        class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10" data-toggle="collapse" data-parent="#accordion" href="#collapseStatus8">
                                        <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M12 9v4"></path>
                                            <path
                                                d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                            </path>
                                            <path d="M12 16h.01"></path>
                                        </svg>
                                    </div>
                                    <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                        <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2" data-toggle="collapse" data-parent="#accordion" href="#collapseStatus8">
                                            <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                                {{ __('home.stock_expiry_alert') }}
                                                @show_tooltip(
                                                __('tooltip.stock_expiry_alert', [
                                                'days'
                                                =>session('business.stock_expiry_alert_days', 30) ]) )
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div id="collapseStatus8" class="panel-collapse active tw-pt-4 tw-pb-4 collapse" aria-expanded="false" style="height: 32px;">
                                    <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                        <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                            <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                                <input type="hidden" id="stock_expiry_alert_days"
                                                    value="{{ \Carbon::now()->addDays(session('business.stock_expiry_alert_days', 30))->format('Y-m-d') }}">
                                                <table class="table table-bordered table-striped" id="stock_expiry_alert_table">
                                                    <thead>
                                                        <tr>
                                                            <th>@lang('business.product')</th>
                                                            <th>@lang('business.location')</th>
                                                            <th>@lang('report.stock_left')</th>
                                                            <th>@lang('product.expires_in')</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>    
                            </div>
                        </div>
                    @endif
                @endcan
                @if (auth()->user()->can('so.view_all') || auth()->user()->can('so.view_own'))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                                        <path d="M12 8v4"></path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2 cursor-pointer" data-toggle="collapse"
                                    data-parent="#accordion" onclick="toggleCollapse5('collapseStatus5')">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('lang_v1.sales_order') }}
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('so_location', $all_locations, null, [
                                                'class' => 'form-control select2',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'so_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                             <div id="collapseStatus5" class="panel-collapse collapse tw-pt-4 tw-pb-4"
                                    aria-expanded="true">
                                <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                    <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                            <table class="table table-bordered table-striped ajax_view"
                                                id="sales_order_table">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('messages.action')</th>
                                                        <th>@lang('messages.date')</th>
                                                        <th>@lang('restaurant.order_no')</th>
                                                        <th>@lang('sale.customer_name')</th>
                                                        <th>@lang('lang_v1.contact_no')</th>
                                                        <th>@lang('sale.location')</th>
                                                        <th>@lang('sale.status')</th>
                                                        <th>@lang('lang_v1.shipping_status')</th>
                                                        <th>@lang('lang_v1.quantity_remaining')</th>
                                                        <th>@lang('lang_v1.added_by')</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                             </div>    
                        </div>
                    </div>
                @endif
                @if (
                    !empty($common_settings['enable_purchase_requisition']) &&
                        (auth()->user()->can('purchase_requisition.view_all') || auth()->user()->can('purchase_requisition.view_own')))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10" data-toggle="collapse" data-parent="#accordion" href="#collapseStatus6">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M10 10v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1 -1v-4"></path>
                                        <path d="M9 6h6"></path>
                                        <path d="M10 6v-2a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v2"></path>
                                        <circle cx="12" cy="16" r="2"></circle>
                                        <path d="M5 20h14a2 2 0 0 0 2 -2v-10"></path>
                                        <path d="M15 16v4"></path>
                                        <path d="M9 20v-4"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2" data-toggle="collapse" data-parent="#accordion" href="#collapseStatus6">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.purchase_requisition')
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            @if (count($all_locations) > 1)
                                                {!! Form::select('pr_location', $all_locations, null, [
                                                    'class' => 'form-control select2',
                                                    'placeholder' => __('lang_v1.select_location'),
                                                    'id' => 'pr_location',
                                                ]) !!}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div id="collapseStatus6" class="panel-collapse active tw-pt-4 tw-pb-4 collapse" aria-expanded="false" style="height: 32px;">
                                <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                    <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                            <table class="table table-bordered table-striped ajax_view"
                                                id="purchase_requisition_table" style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('messages.action')</th>
                                                        <th>@lang('messages.date')</th>
                                                        <th>@lang('purchase.ref_no')</th>
                                                        <th>@lang('purchase.location')</th>
                                                        <th>@lang('sale.status')</th>
                                                        <th>@lang('lang_v1.required_by_date')</th>
                                                        <th>@lang('lang_v1.added_by')</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>    
                        </div>
                    </div>
                @endif

                @if (
                    !empty($common_settings['enable_purchase_order']) &&
                        (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own')))

                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10" data-toggle="collapse" data-parent="#accordion" href="#collapseStatus7">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <rect x="4" y="4" width="16" height="16" rx="2" />
                                        <line x1="4" y1="10" x2="20" y2="10" />
                                        <line x1="12" y1="4" x2="12" y2="20" />
                                        <line x1="12" y1="10" x2="16" y2="10" />
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2" data-toggle="collapse" data-parent="#accordion" href="#collapseStatus7">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.purchase_order')
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('po_location', $all_locations, null, [
                                                'class' => 'form-control select2',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'po_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div id="collapseStatus7" class="panel-collapse active tw-pt-4 tw-pb-4 collapse" aria-expanded="false" style="height: 32px;">
                                <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                    <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                            <table class="table table-bordered table-striped ajax_view"
                                                id="purchase_order_table" style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('messages.action')</th>
                                                        <th>@lang('messages.date')</th>
                                                        <th>@lang('purchase.ref_no')</th>
                                                        <th>@lang('purchase.location')</th>
                                                        <th>@lang('purchase.supplier')</th>
                                                        <th>@lang('sale.status')</th>
                                                        <th>@lang('lang_v1.quantity_remaining')</th>
                                                        <th>@lang('lang_v1.added_by')</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>    
                        </div>
                    </div>

                @endif
                @if (auth()->user()->can('access_pending_shipments_only') ||
                        auth()->user()->can('access_shipping') ||
                        auth()->user()->can('access_own_shipping'))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10" data-toggle="collapse" data-parent="#accordion" href="#collapseStatus9">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M5 17h-2v-4m-1 -8h11v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"></path>
                                        <path d="M3 9l4 0"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2 cursor-pointer" data-toggle="collapse"
                                    data-parent="#accordion" onclick="toggleCollapse9('collapseStatus9')">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.pending_shipments')
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('pending_shipments_location', $all_locations, null, [
                                                'class' => 'form-control select2 ',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'pending_shipments_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div id="collapseStatus9" class="panel-collapse collapse tw-pt-4 tw-pb-4"
                                    aria-expanded="true">
                                <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                    <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                            <table class="table table-bordered table-striped ajax_view" id="shipments_table">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('messages.action')</th>
                                                        <th>@lang('messages.date')</th>
                                                        <th>@lang('sale.invoice_no')</th>
                                                        <th>@lang('sale.customer_name')</th>
                                                        <th>@lang('lang_v1.contact_no')</th>
                                                        <th>@lang('sale.location')</th>
                                                        <th>@lang('lang_v1.shipping_status')</th>
                                                        @if (!empty($custom_labels['shipping']['custom_field_1']))
                                                            <th>
                                                                {{ $custom_labels['shipping']['custom_field_1'] }}
                                                            </th>
                                                        @endif
                                                        @if (!empty($custom_labels['shipping']['custom_field_2']))
                                                            <th>
                                                                {{ $custom_labels['shipping']['custom_field_2'] }}
                                                            </th>
                                                        @endif
                                                        @if (!empty($custom_labels['shipping']['custom_field_3']))
                                                            <th>
                                                                {{ $custom_labels['shipping']['custom_field_3'] }}
                                                            </th>
                                                        @endif
                                                        @if (!empty($custom_labels['shipping']['custom_field_4']))
                                                            <th>
                                                                {{ $custom_labels['shipping']['custom_field_4'] }}
                                                            </th>
                                                        @endif
                                                        @if (!empty($custom_labels['shipping']['custom_field_5']))
                                                            <th>
                                                                {{ $custom_labels['shipping']['custom_field_5'] }}
                                                            </th>
                                                        @endif
                                                        <th>@lang('sale.payment_status')</th>
                                                        <th>@lang('restaurant.service_staff')</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>    
                        </div>
                    </div>
                @endif
                @if (auth()->user()->can('account.access') && config('constants.show_payments_recovered_today') == true)
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10" data-toggle="collapse" data-parent="#accordion" href="#collapseStatus10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v4"></path>
                                        <path
                                            d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                        </path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2" data-toggle="collapse" data-parent="#accordion" href="#collapseStatus10">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.payment_recovered_today')
                                        </h3>
                                    </div>

                                </div>
                            </div>
                            <div id="collapseStatus10" class="panel-collapse active tw-pt-4 tw-pb-4 collapse" aria-expanded="false" style="height: 32px;">
                                <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                    <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                            <table class="table table-bordered table-striped" id="cash_flow_table">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('messages.date')</th>
                                                        <th>@lang('account.account')</th>
                                                        <th>@lang('lang_v1.description')</th>
                                                        <th>@lang('lang_v1.payment_method')</th>
                                                        <th>@lang('lang_v1.payment_details')</th>
                                                        <th>@lang('account.credit')</th>
                                                        <th>@lang('lang_v1.account_balance')
                                                            @show_tooltip(__('lang_v1.account_balance_tooltip'))</th>
                                                        <th>@lang('lang_v1.total_balance')
                                                            @show_tooltip(__('lang_v1.total_balance_tooltip'))</th>
                                                    </tr>
                                                </thead>
                                                <tfoot>
                                                    <tr class="bg-gray font-17 footer-total text-center">
                                                        <td colspan="5"><strong>@lang('sale.total'):</strong></td>
                                                        <td class="footer_total_credit"></td>
                                                        <td colspan="2"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
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


<div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade edit_pso_status_modal" tabindex="-1" role="dialog"></div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>

@section('css')
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    @includeIf('sales_order.common_js')
    @includeIf('purchase_order.common_js')
    @if (!empty($all_locations))
        {!! $sells_chart_1->script() !!}
        {!! $sells_chart_2->script() !!}
    @endif
    <script type="text/javascript">
        $(document).ready(function() {
            sales_order_table = $('#sales_order_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}?sale_type=sales_order',
                    "data": function(d) {
                        d.for_dashboard_sales_order = true;

                        if ($('#so_location').length > 0) {
                            d.location_id = $('#so_location').val();
                        }
                    }
                },
                columnDefs: [{
                    "targets": 7,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    {
                        data: 'so_qty_remaining',
                        name: 'so_qty_remaining',
                        "searchable": false
                    },
                    {
                        data: 'added_by',
                        name: 'u.first_name'
                    },
                ]
            });

            @if (auth()->user()->can('account.access') && config('constants.show_payments_recovered_today') == true)

                // Cash Flow Table
                cash_flow_table = $('#cash_flow_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    "ajax": {
                        "url": "{{ action([\App\Http\Controllers\AccountController::class, 'cashFlow']) }}",
                        "data": function(d) {
                            d.type = 'credit';
                            d.only_payment_recovered = true;
                        }
                    },
                    "ordering": false,
                    "searching": false,
                    columns: [{
                            data: 'operation_date',
                            name: 'operation_date'
                        },
                        {
                            data: 'account_name',
                            name: 'account_name'
                        },
                        {
                            data: 'sub_type',
                            name: 'sub_type'
                        },
                        {
                            data: 'method',
                            name: 'TP.method'
                        },
                        {
                            data: 'payment_details',
                            name: 'payment_details',
                            searchable: false
                        },
                        {
                            data: 'credit',
                            name: 'amount'
                        },
                        {
                            data: 'balance',
                            name: 'balance'
                        },
                        {
                            data: 'total_balance',
                            name: 'total_balance'
                        },
                    ],
                    "fnDrawCallback": function(oSettings) {
                        __currency_convert_recursively($('#cash_flow_table'));
                    },
                    "footerCallback": function(row, data, start, end, display) {
                        var footer_total_credit = 0;

                        for (var r in data) {
                            footer_total_credit += $(data[r].credit).data('orig-value') ? parseFloat($(
                                data[r].credit).data('orig-value')) : 0;
                        }
                        $('.footer_total_credit').html(__currency_trans_from_en(footer_total_credit));
                    }
                });
            @endif

            $('#so_location').change(function() {
                sales_order_table.ajax.reload();
            });
            @if (!empty($common_settings['enable_purchase_order']))
                //Purchase table
                purchase_order_table = $('#purchase_order_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    aaSorting: [
                        [1, 'desc']
                    ],
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    ajax: {
                        url: '{{ action([\App\Http\Controllers\PurchaseOrderController::class, 'index']) }}',
                        data: function(d) {
                            d.from_dashboard = true;

                            if ($('#po_location').length > 0) {
                                d.location_id = $('#po_location').val();
                            }
                        },
                    },
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'location_name',
                            name: 'BS.name'
                        },
                        {
                            data: 'name',
                            name: 'contacts.name'
                        },
                        {
                            data: 'status',
                            name: 'transactions.status'
                        },
                        {
                            data: 'po_qty_remaining',
                            name: 'po_qty_remaining',
                            "searchable": false
                        },
                        {
                            data: 'added_by',
                            name: 'u.first_name'
                        }
                    ]
                })

                $('#po_location').change(function() {
                    purchase_order_table.ajax.reload();
                });
            @endif

            @if (!empty($common_settings['enable_purchase_requisition']))
                //Purchase table
                purchase_requisition_table = $('#purchase_requisition_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    aaSorting: [
                        [1, 'desc']
                    ],
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    ajax: {
                        url: '{{ action([\App\Http\Controllers\PurchaseRequisitionController::class, 'index']) }}',
                        data: function(d) {
                            d.from_dashboard = true;

                            if ($('#pr_location').length > 0) {
                                d.location_id = $('#pr_location').val();
                            }
                        },
                    },
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'location_name',
                            name: 'BS.name'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'delivery_date',
                            name: 'delivery_date'
                        },
                        {
                            data: 'added_by',
                            name: 'u.first_name'
                        },
                    ]
                })

                $('#pr_location').change(function() {
                    purchase_requisition_table.ajax.reload();
                });

                $(document).on('click', 'a.delete-purchase-requisition', function(e) {
                    e.preventDefault();
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(willDelete => {
                        if (willDelete) {
                            var href = $(this).attr('href');
                            $.ajax({
                                method: 'DELETE',
                                url: href,
                                dataType: 'json',
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        purchase_requisition_table.ajax.reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                });
            @endif

            sell_table = $('#shipments_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                aaSorting: [
                    [1, 'desc']
                ],
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                "ajax": {
                    "url": '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}',
                    "data": function(d) {
                        d.only_pending_shipments = true;
                        if ($('#pending_shipments_location').length > 0) {
                            d.location_id = $('#pending_shipments_location').val();
                        }
                    }
                },
                columns: [{
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    @if (!empty($custom_labels['shipping']['custom_field_1']))
                        {
                            data: 'shipping_custom_field_1',
                            name: 'shipping_custom_field_1'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_2']))
                        {
                            data: 'shipping_custom_field_2',
                            name: 'shipping_custom_field_2'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_3']))
                        {
                            data: 'shipping_custom_field_3',
                            name: 'shipping_custom_field_3'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_4']))
                        {
                            data: 'shipping_custom_field_4',
                            name: 'shipping_custom_field_4'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_5']))
                        {
                            data: 'shipping_custom_field_5',
                            name: 'shipping_custom_field_5'
                        },
                    @endif {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'waiter',
                        name: 'ss.first_name',
                        @if (empty($is_service_staff_enabled))
                            visible: false
                        @endif
                    }
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#sell_table'));
                },
                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(4)').attr('class', 'clickable_td');
                }
            });

            $('#pending_shipments_location').change(function() {
                sell_table.ajax.reload();
            });
        });
         $(document).ready(function () {
          
             $('#collapseStatus6').on('shown.bs.collapse', function () {
                $('#purchase_requisition_table').DataTable().columns.adjust().draw();
            });
             $('#collapseStatus7').on('shown.bs.collapse', function () {
                $('#purchase_order_table').DataTable().columns.adjust().draw();
            });
             $('#collapseStatus8').on('shown.bs.collapse', function () {
                $('#stock_expiry_alert_table').DataTable().columns.adjust().draw();
            });
             $('#collapseStatus10').on('shown.bs.collapse', function () {
                $('#cash_flow_table').DataTable().columns.adjust().draw();
            });
        });
        function toggleCollapse(id) {
        const element = document.getElementById(id);
        const isExpanded = element.classList.contains('show');

        // Toggle the class and aria-expanded attribute
        element.classList.toggle('show');
        element.setAttribute('aria-expanded', !isExpanded);
        

        // Save the state in localStorage
        localStorage.setItem(id, !isExpanded ? 'expanded' : 'collapsed');
    }

    function toggleCollapse2(id) {
        const element = document.getElementById(id);
        const isExpanded = element.classList.contains('show');

        // Toggle the class and aria-expanded attribute
        element.classList.toggle('show');
        element.setAttribute('aria-expanded', !isExpanded);
        setTimeout(() => {
            $('#sales_payment_dues_table').DataTable().columns.adjust().draw();
        }, 300);

        // Save the state in localStorage
        localStorage.setItem(id, !isExpanded ? 'expanded' : 'collapsed');
    }
    function toggleCollapse3(id) {
        const element = document.getElementById(id);
        const isExpanded = element.classList.contains('show');

        // Toggle the class and aria-expanded attribute
        element.classList.toggle('show');
        element.setAttribute('aria-expanded', !isExpanded);
        setTimeout(() => {
            $('#purchase_payment_dues_table').DataTable().columns.adjust().draw();
        }, 300);

        // Save the state in localStorage
        localStorage.setItem(id, !isExpanded ? 'expanded' : 'collapsed');
    }
    function toggleCollapse4(id) {
        const element = document.getElementById(id);
        const isExpanded = element.classList.contains('show');

        // Toggle the class and aria-expanded attribute
        element.classList.toggle('show');
        element.setAttribute('aria-expanded', !isExpanded);
        setTimeout(() => {
            $('#stock_alert_table').DataTable().columns.adjust().draw();
        }, 300);

        // Save the state in localStorage
        localStorage.setItem(id, !isExpanded ? 'expanded' : 'collapsed');
    }function toggleCollapse5(id) {
        const element = document.getElementById(id);
        const isExpanded = element.classList.contains('show');

        // Toggle the class and aria-expanded attribute
        element.classList.toggle('show');
        element.setAttribute('aria-expanded', !isExpanded);
        setTimeout(() => {
            $('#sales_order_table').DataTable().columns.adjust().draw();
        }, 300);

        // Save the state in localStorage
        localStorage.setItem(id, !isExpanded ? 'expanded' : 'collapsed');
    }function toggleCollapse9(id) {
        const element = document.getElementById(id);
        const isExpanded = element.classList.contains('show');

        // Toggle the class and aria-expanded attribute
        element.classList.toggle('show');
        element.setAttribute('aria-expanded', !isExpanded);
        setTimeout(() => {
            $('#shipments_table').DataTable().columns.adjust().draw();
        }, 300);

        // Save the state in localStorage
        localStorage.setItem(id, !isExpanded ? 'expanded' : 'collapsed');
    }
    document.addEventListener('DOMContentLoaded', () => {
        const element = document.getElementById('collapseStatus');
        const savedState = localStorage.getItem('collapseStatus') || 'expanded';
        
        if (savedState === 'expanded') {
            element.classList.add('show');
            element.setAttribute('aria-expanded', 'true');
        } else {
            element.classList.remove('show');
            element.setAttribute('aria-expanded', 'false');
        }
    });
     document.addEventListener('DOMContentLoaded', () => {
        const element = document.getElementById('collapseStatus1');
        const savedState = localStorage.getItem('collapseStatus1') || 'expanded';
        
        if (savedState === 'expanded') {
            element.classList.add('show');
            element.setAttribute('aria-expanded', 'true');
        } else {
            element.classList.remove('show');
            element.setAttribute('aria-expanded', 'false');
        }
    });
    document.addEventListener('DOMContentLoaded', () => {
        const element3 = document.getElementById('collapseStatus3');
        const savedState3 = localStorage.getItem('collapseStatus3');
        const element2 = document.getElementById('collapseStatus2');
        const savedState2 = localStorage.getItem('collapseStatus2');
        const element4 = document.getElementById('collapseStatus4');
        const savedState4 = localStorage.getItem('collapseStatus4')|| 'expanded';
        const element5 = document.getElementById('collapseStatus5');
        const savedState5 = localStorage.getItem('collapseStatus5');
        const element9 = document.getElementById('collapseStatus9');
        const savedState9 = localStorage.getItem('collapseStatus9');
        if (savedState3 === 'expanded') {
            element3.classList.add('show');
            element3.setAttribute('aria-expanded', 'true');
        } else {
            element3.classList.remove('show');
            element3.setAttribute('aria-expanded', 'false');
        }
        if (savedState2 === 'expanded') {
            element2.classList.add('show');
            element2.setAttribute('aria-expanded', 'true');
        } else {
            element2.classList.remove('show');
            element2.setAttribute('aria-expanded', 'false');
        }
        if (savedState4 === 'expanded') {
            element4.classList.add('show');
            element4.setAttribute('aria-expanded', 'true');
        } else {
            element4.classList.remove('show');
            element4.setAttribute('aria-expanded', 'false');
        }
        if (savedState5 === 'expanded') {
            element5.classList.add('show');
            element5.setAttribute('aria-expanded', 'true');
        } else {
            element5.classList.remove('show');
            element5.setAttribute('aria-expanded', 'false');
        }
        if (savedState9 === 'expanded') {
            element9.classList.add('show');
            element9.setAttribute('aria-expanded', 'true');
        } else {
            element9.classList.remove('show');
            element9.setAttribute('aria-expanded', 'false');
        }
    });
    </script>
    
@endsection
