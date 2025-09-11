{!! Form::open([
    'url' => action([Modules\Goals\Http\Controllers\GoalsController::class, 'store']),
    'method' => 'post',
]) !!}

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">@lang('goals::goals.add_product_goal')</h4>
</div>

<div class="modal-body">
    <div class="row">
        <input type="hidden" name="type" value="product">
        <input type="hidden" name="id" value="{{$id}}">

        <div class="col-md-8 col-md-offset-2">
            <div class="form-group">
                {!! Form::label('product_id', __('product.product_name') . ':') !!}
                <input type="hidden" name="product_id" id="product_id">

                <input
                    type="text"
                    id="product_search"
                    class="form-control"
                    placeholder="@lang('lang_v1.search_product')"
                    autocomplete="off"
                />

                <div id="product_search_results" class="list-group" style="position: absolute; z-index: 1050; width: 100%; max-height: 200px; overflow-y: auto; display: none;"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
    <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
        data-dismiss="modal">@lang('messages.close')</button>
</div>

{!! Form::close() !!}
<script>
     $(document).ready(function() {
            $('#product_search').on('input', function() {
                let query = $(this).val();
                if (query.length < 1) {
                    $('#product_search_results').hide();
                    return;
                }

                $.ajax({
                    url: '/goals/products/search', 
                    method: 'GET',
                    data: { q: query },
                    success: function(data) {
                        let results = data.products; 
                        let html = '';
                        if (results.length > 0) {
                            results.forEach(function(product) {
                                html += `<a href="#" class="list-group-item list-group-item-action product-result-item" data-id="${product.id}">${product.name}</a>`;
                            });
                            $('#product_search_results').html(html).show();
                        } else {
                            $('#product_search_results').html('<div class="list-group-item">No products found</div>').show();
                        }
                    }
                });
            });

            // When user clicks a product from the results
            $(document).on('click', '.product-result-item', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                let name = $(this).text();
                $('#product_id').val(id);
                $('#product_search').val(name);
                $('#product_search_results').hide();
            });

            // Hide results if clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#product_search, #product_search_results').length) {
                    $('#product_search_results').hide();
                }
            });
     });
</script>