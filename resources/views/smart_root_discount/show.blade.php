@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-percentage mr-2"></i>@lang('lang_v1.show_smart_discount')
            </h4>
            <a href="{{ URL::previous() }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> @lang('messages.back')
            </a>
        </div>
        
        <div class="card-body">
            <!-- Main Container with subtle shadow -->
            <div class="border p-4 rounded bg-white shadow-sm">
                <div class="row">
                    <!-- Discount Name Section -->
                    <div class="col-md-12 mb-4">
                        <div class="form-group">
                            <label class="font-weight-bold text-primary">@lang('sale.discount_name'):</label>
                            <p class="mt-2 lead">{{ $smartRootDiscount->name }}</p>
                        </div>
                        <hr class="my-3">
                    </div>

                    <!-- Date Section -->
                    <div class="col-md-12 mb-4">
                        <div class="row">
                            <!-- Start Date -->
                            <div class="col-md-6 mb-3 pr-md-4">
                                <div class="form-group border-right pr-md-3">
                                    <label class="font-weight-bold text-muted">@lang('sale.discount_start_date'):</label>
                                    <p class="mt-2 text-dark">
                                        <i class="far fa-calendar-alt mr-2"></i>
                                        {{ \Carbon\Carbon::parse($smartRootDiscount->start_date)->format('Y-m-d') }}
                                    </p>
                                </div>
                            </div>

                            <!-- End Date -->
                            <div class="col-md-6 mb-3 pl-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted">@lang('sale.discount_end_date'):</label>
                                    <p class="mt-2 text-dark">
                                        <i class="far fa-calendar-alt mr-2"></i>
                                        {{ \Carbon\Carbon::parse($smartRootDiscount->end_date)->format('Y-m-d') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <hr class="my-3">
                    </div>

                    <!-- Business Locations & Users -->
                    <div class="col-md-12 mb-4">
                        <div class="row">
                            <!-- Business Locations -->
                            <div class="col-md-6 mb-3 pr-md-4">
                                <div class="form-group border-right pr-md-3">
                                    <label class="font-weight-bold text-muted">@lang('sale.business_location'):</label>
                                    <div class="mt-2">
                                        @if($businessLocations->count() > 0)
                                            <div class="d-flex flex-wrap">
                                                @foreach($businessLocations as $location)
                                                    <span class="badge badge-primary mr-2 mb-2 p-2">
                                                        <i class="fas fa-store mr-1"></i>
                                                        {{ $location->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="badge badge-secondary p-2">
                                                @lang('messages.none')
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Users -->
                            <div class="col-md-6 mb-3 pl-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted">@lang('sale.user'):</label>
                                    <div class="mt-2">
                                        @if($selectedUserModels->count() > 0)
                                            <div class="d-flex flex-wrap">
                                                @foreach($selectedUserModels as $user)
                                                    <span class="badge badge-info mr-2 mb-2 p-2">
                                                        <i class="fas fa-user mr-1"></i>
                                                        {{ $user->username }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="badge badge-secondary p-2">
                                                @lang('messages.none')
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-3">
                    </div>

                    <!-- Discount Type -->
                    <div class="col-md-12 mb-4">
                        <div class="form-group">
                            <label class="font-weight-bold text-muted">@lang('sale.discount_type'):</label>
                            <p class="mt-2">
                                <span class="badge badge-success p-2">
                                    <i class="fas fa-tag mr-1"></i>
                                    {{ $smartRootDiscount->type_smart_root_discount->name }}
                                </span>
                            </p>
                        </div>
                        <hr class="my-3">
                    </div>

                    <!-- Discount Details - Different display based on type -->
                    @if($smartRootDiscount->type_smart_root_discount_id == 1)
                        <!-- Type 1: Invoice Amount Based Discount -->
                        <div class="col-md-12 mt-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-money-bill-wave mr-2"></i>
                                        @lang('sale.invoice_amount_based_discounts')
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="border-top-0">@lang('sale.invoice_amount')</th>
                                                    <th class="border-top-0">@lang('sale.discount_amount')</th>
                                                    <th class="border-top-0">@lang('sale.discount_status')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($statusOfSmartRootDiscounts as $status)
                                                    <tr>
                                                        <td class="font-weight-bold">{{ number_format($status->invoice_amount, 2) }}</td>
                                                        <td class="text-success font-weight-bold">{{ number_format($status->discount_amount, 2) }}</td>
                                                        <td>
                                                            <span class="badge 
                                                                @if(($status->discount_status->name ?? '') === 'Active') badge-success
                                                                @elseif(($status->discount_status->name ?? '') === 'Expired') badge-danger
                                                                @else badge-secondary
                                                                @endif">
                                                                {{ $status->discount_status->name ?? 'N/A' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($smartRootDiscount->type_smart_root_discount_id == 2)
                    <!-- Type 2: Buy X Get Y Free -->
                    <div class="col-md-12 mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-gift mr-2"></i>
                                    @lang('sale.discount_statuses')
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach($finalDiscountsWithRelations as $discount)
                                    <div class="mb-4 border-bottom pb-3">
                                        <div class="row">
                                            <!-- Conditions -->
                                            <div class="col-md-6">
                                                <div class="condition-container bg-light p-3 rounded">
                                                    <h5 class="condition-header text-primary">
                                                        <i class="fas fa-list-ul mr-2"></i>
                                                        @lang('messages.condition')
                                                    </h5>
                                                    <div class="condition-fields mt-3">
                                                        @foreach($discount->subConditions as $condition)
                                                            <div class="condition-item mb-2 p-2 bg-white rounded border">
                                                                <div class="d-flex justify-content-between">
                                                                    <div>
                                                                        <strong>{{ $condition->varition->product->name ?? 'N/A' }}</strong>
                                                                    
                                                                            {{ number_format($condition->quantity,2) }} {{ $condition->unit->actual_name ?? 'N/A' }}
                                                                      
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Results -->
                                            <div class="col-md-6">
                                                <div class="result-container bg-light p-3 rounded">
                                                    <h5 class="result-header text-success">
                                                        <i class="fas fa-gift mr-2"></i>
                                                        @lang('messages.result')
                                                    </h5>
                                                    <div class="result-fields mt-3">
                                                        @foreach($discount->subResults as $result)
                                                            <div class="result-item mb-2 p-2 bg-white rounded border">
                                                                <div class="d-flex justify-content-between">
                                                                    <div>
                                                                        <strong>{{ $result->varition->product->name ?? 'N/A' }}</strong>
                                                                       
                                                                            {{ number_format($result->quantity,2) }} {{ $result->unit->actual_name ?? 'N/A' }}
                                                                    
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Description -->
                                        <div class="col-md-12 mt-3">
                                            <div class="description-box bg-light p-3 rounded">
                                                <p class="description-text mb-0">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    @lang('messages.Meaning_any_invoice_that_contains')
                                                    @foreach($discount->subConditions as $index => $condition)
                                                        {{ $condition->quantity }} {{ $condition->varition->product->name }}@if(!$loop->last) + @endif
                                                    @endforeach
                                                    @lang('messages.You_will_get')
                                                    @foreach($discount->subResults as $index => $result)
                                                        {{ $result->quantity }} {{ $result->varition->product->name }}@if(!$loop->last) + @endif
                                                    @endforeach
                                                    @lang('messages.free')
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif($smartRootDiscount->type_smart_root_discount_id == 3)
                    <!-- Type 3: Buy X Get Discount -->
                    <div class="col-md-12 mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-tags mr-2"></i>
                                    @lang('sale.discount_statuses')
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach($finalDiscountsWithRelations3 as $discount)
                                    <div class="mb-4 border-bottom pb-3">
                                        <div class="row">
                                            <!-- Conditions -->
                                            <div class="col-md-6">
                                                <div class="condition-container bg-light p-3 rounded">
                                                    <h5 class="condition-header text-primary">
                                                        <i class="fas fa-list-ul mr-2"></i>
                                                        @lang('messages.condition')
                                                    </h5>
                                                    <div class="condition-fields mt-3">
                                                        @foreach($discount->subConditions as $condition)
                                                            <div class="condition-item mb-2 p-2 bg-white rounded border">
                                                                <div class="d-flex justify-content-between">
                                                                    <div>
                                                                        <strong>{{ $condition->varition->product->name ?? 'N/A' }}</strong>
                                                                        <div class="text-muted small">
                                                                            {{ $condition->quantity }} {{ $condition->unit->actual_name ?? 'N/A' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Discount -->
                                            <div class="col-md-6">
                                                <div class="discount-container bg-light p-3 rounded">
                                                    <h5 class="discount-header text-success">
                                                        <i class="fas fa-percent mr-2"></i>
                                                        @lang('messages.result')
                                                    </h5>
                                                    <div class="discount-details mt-3 p-2 bg-white rounded border">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <strong>@lang('sale.discount_amount'):</strong>
                                                                <p class="text-success font-weight-bold">{{ number_format($discount->discount_amount, 2) }}</p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>@lang('sale.discount_status'):</strong>
                                                                <p>
                                                                    <span class="badge 
                                                                        @if($discount->discount_status->name === 'Active') badge-success
                                                                        @elseif($discount->discount_status->name === 'Expired') badge-danger
                                                                        @else badge-secondary
                                                                        @endif">
                                                                        {{ $discount->discount_status->name ?? 'N/A' }}
                                                                    </span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Description -->
                                        <div class="col-md-12 mt-3">
                                            <div class="description-box bg-light p-3 rounded">
                                                <p class="description-text mb-0">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    @lang('messages.Meaning_any_invoice_that_contains')
                                                    @foreach($discount->subConditions as $index => $condition)
                                                        {{ $condition->quantity }} {{ $condition->varition->product->name }}@if(!$loop->last) + @endif
                                                    @endforeach
                                                    @lang('messages.The_smart_discount_will_be')
                                                    {{ number_format($discount->discount_amount, 2) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .card-header {
        border-radius: 0.35rem 0.35rem 0 0 !important;
    }
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    hr {
        border-top: 1px dashed #e0e0e0;
    }
    .condition-container, .result-container, .discount-container {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
    }
    .condition-header, .result-header, .discount-header {
        font-size: 1.1rem;
    }
    .condition-item, .result-item {
        transition: all 0.3s ease;
    }
    .condition-item:hover, .result-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .description-box {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }
</style>
@endpush