@extends('layouts.app')

@section('content')
    <div id="goal-form">
        <section class="content-header">
            <h2 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('goals::goals.add_goal')</h2>
        </section>

        {{ Form::open(['url' => route('goal.store', $id), 'method' => 'POST', 'id' => 'goalForm']) }}

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                @component('components.widget', ['class' => 'box-solid'])
                <div class="row mb-4">
                    <div class="col-md-6 offset-md-3">
                        <div class="form-group">
                            {!! Form::label('user_id', __('user.name') . ':') !!}
                            {!! Form::select('user_id', $users ?? [], null, [
                                'class' => 'form-control select2',
                                'id' => 'user_id',
                                'required',
                                'placeholder' => __('messages.please_select')
                            ]) !!}
                        </div>
                    </div>
                </div>
                    @foreach ($goals as $index => $goal)
                        <div class="goal-parent border p-3 mb-4" data-index="{{ $index }}">
                            <h5><strong>@lang('goals::goals.goal') #{{ $index + 1 }}</strong></h5>

                            {{-- Item Name (readonly) --}}
                            <div class="form-group">
                                {!! Form::label("goals[$index][item_name]", __('goals::goals.item_name')) !!}
                                {!! Form::text("goals[$index][item_name]", $goal->item_name, ['class' => 'form-control', 'readonly']) !!}
                            </div>

                            {{-- Type (readonly) --}}
                            <div class="form-group">
                                {!! Form::label("goals[$index][goal_type]", __('lang_v1.type')) !!}
                                {!! Form::text("goals[$index][goal_type]", $goal->goal_type, ['class' => 'form-control goal-type', 'readonly']) !!}
                            </div>

                            {{-- Amount Type (only for product type, selectable) --}}
                            @if (strtolower($goal->goal_type) == 'product')
                                <div class="form-group">
                                    {!! Form::label("goals[$index][amount_type]", __('goals::goals.amount_type')) !!}
                                    {!! Form::select(
                                        "goals[$index][amount_type]",
                                        ['amount' => __('sale.amount'), 'quantity' => __('lang_v1.quantity')],
                                        null,
                                        ['class' => 'form-control amount-type-select'],
                                    ) !!}
                                </div>
                            @else
                                {!! Form::hidden("goals[$index][amount_type]", 'amount') !!}
                            @endif

                            {{-- Amount (editable) --}}
                            <div class="form-group">
                                {!! Form::label("goals[$index][amount]", __('sale.amount') . ':*') !!}
                                {!! Form::number("goals[$index][amount]", null, [
                                    'class' => 'form-control amount-input',
                                    'min' => 0,
                                    'step' => 'any',
                                    'required',
                                ]) !!}
                            </div>

                            {{-- Reward Amount (editable) --}}
                            <div class="form-group">
                                {!! Form::label("goals[$index][reward_amount]", __('goals::goals.reward_amount') . ':*') !!}
                                {!! Form::number("goals[$index][reward_amount]", null, [
                                    'class' => 'form-control reward-amount-input',
                                    'min' => 0,
                                    'step' => 'any',
                                    'required',
                                ]) !!}
                            </div>

                            {{-- Hidden item_id (for backend) --}}
                            {!! Form::hidden("goals[$index][item_id]", $goal->item_id) !!}

                            {{-- Child Goals container --}}
                            <div class="child-goals-container mb-3" data-parent-index="{{ $index }}">
                                <h6>@lang('goals::goals.child_goal')</h6>
                                {{-- Child goals will be appended here dynamically --}}
                            </div>

                            <button type="button" class="btn btn-sm btn-primary add-child-goal"
                                data-parent-index="{{ $index }}">
                                + @lang('goals::goals.add_child_goal')
                            </button>
                        </div>
                    @endforeach
                @endcomponent
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-sm-12 text-center">
                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">
                    @lang('messages.save')
                </button>
            </div>
        </div>

        {{ Form::close() }}
    </div>
@endsection


@section('javascript')
    <script>
        $(document).ready(function() {

            // Add Child Goal block function
            function addChildGoal(parentIndex) {
                const parentGoalDiv = $(`.goal-parent[data-index='${parentIndex}']`);
                const childContainer = parentGoalDiv.find('.child-goals-container');
                const childCount = childContainer.children('.child-goal').length;

                const parentType = parentGoalDiv.find(`input[name="goals[${parentIndex}][goal_type]"]`).val()
                    .toLowerCase();
                const parentName = parentGoalDiv.find(`input[name="goals[${parentIndex}][item_name]"]`).val();

                let amountTypeSelectHtml = '';
                if (parentType === 'product') {
                    amountTypeSelectHtml = `
                    <select name="goals[${parentIndex}][child_goals][${childCount}][amount_type]" class="form-control amount-type-select" required>
                        <option value="amount">@lang('sale.amount')</option>
                        <option value="quantity">@lang('lang_v1.quantity')</option>
                    </select>`;
                } else {
                    amountTypeSelectHtml =
                        `<input type="hidden" name="goals[${parentIndex}][child_goals][${childCount}][amount_type]" value="amount">`;
                }

                const childGoalHtml = `
            <div class="child-goal border rounded p-3 mb-2 d-flex align-items-start" data-child-index="${childCount}">
                <div style="flex-grow:1;">
                    <div class="form-group">
                        <label>@lang('goals::goals.item_name')</label>
                        <input type="text" name="goals[${parentIndex}][child_goals][${childCount}][item_name]" class="form-control" value="${parentName}" readonly>
                    </div>
                    <div class="form-group">
                        <label>@lang('lang_v1.type')</label>
                        <input type="text" name="goals[${parentIndex}][child_goals][${childCount}][goal_type]" class="form-control goal-type" value="${parentType}" readonly>
                    </div>
                    <div class="form-group">
                        <label>@lang('goals::goals.amount_type')</label>
                        ${amountTypeSelectHtml}
                    </div>
                    <div class="form-group">
                        <label>@lang('sale.amount')</label>
                        <input type="number" min="0" step="any" name="goals[${parentIndex}][child_goals][${childCount}][amount]" class="form-control child-amount-input" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('goals::goals.reward_amount')</label>
                        <input type="number" min="0" step="any" name="goals[${parentIndex}][child_goals][${childCount}][reward_amount]" class="form-control child-reward-amount-input" required>
                    </div>
                </div>
                <div class="ml-3 pt-3">
                    <button type="button" class="btn btn-danger btn-sm delete-child-goal" title="@lang('goals::goals.delete_child_goal')">&times;</button>
                </div>
            </div>`;

                childContainer.append(childGoalHtml);
            }


            // Add child goal button click
            $('.add-child-goal').on('click', function() {
                const parentIndex = $(this).data('parent-index');
                addChildGoal(parentIndex);
            });

            // Validate child amount ≤ parent amount on child input change
            $(document).on('input', '.child-amount-input', function() {
                const childInput = $(this);
                const parentDiv = childInput.closest('.goal-parent');
                const parentAmount = parseFloat(parentDiv.find('.amount-input').val()) || 0;
                const childAmount = parseFloat(childInput.val()) || 0;

                if (childAmount > parentAmount) {
                    alert('@lang('goals::goals.child_goal_amount_cannot_be_more_than_parent_amount')');
                    childInput.val('');
                }
            });

            // Also validate if parent amount changed — clear child inputs if invalid
            $('.amount-input').on('input', function() {
                const parentInput = $(this);
                const parentDiv = parentInput.closest('.goal-parent');
                const parentAmount = parseFloat(parentInput.val()) || 0;

                parentDiv.find('.child-amount-input').each(function() {
                    const childAmount = parseFloat($(this).val()) || 0;
                    if (childAmount > parentAmount) {
                        alert('@lang('goals::goals.child_goal_amount_cannot_be_more_than_parent_amount')');
                        $(this).val('');
                    }
                });
            });

            // Optional: if child goal type changes, update amount type select visibility (if you want)
            $(document).on('change', '.child-goal input.goal-type', function() {
                const input = $(this);
                const val = input.val().toLowerCase();
                const container = input.closest('.child-goal');
                const amountTypeElem = container.find('.amount-type-select, input[type="hidden"]');

                if (val === 'product') {
                    if (amountTypeElem.is('input[type="hidden"]')) {
                        // Replace hidden with select
                        amountTypeElem.replaceWith(`
                        <select name="${amountTypeElem.attr('name')}" class="form-control amount-type-select" required>
                            <option value="amount">@lang('sale.amount')</option>
                            <option value="quantity">@lang('lang_v1.quantity')</option>
                        </select>
                    `);
                    }
                } else {
                    if (amountTypeElem.is('select')) {
                        amountTypeElem.replaceWith(
                            `<input type="hidden" name="${amountTypeElem.attr('name')}" value="amount">`
                            );
                    }
                }
            });

        });

        // Delete child goal button click (outside doc ready to ensure delegation)
        $(document).on('click', '.delete-child-goal', function() {
            $(this).closest('.child-goal').remove();
        });
    </script>
@endsection
