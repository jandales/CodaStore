{{--
    Variant table on the product form: one row per option combination with its own price,
    stock and photo. Built by public/js/admin-variant-table.js from the options above it; the
    inputs are inside the product form, so the normal save sends them as skus[...].
--}}
@php
    $existingSkus = isset($product) && $product->exists
        ? $product->skus->map(fn ($s) => [
            'key' => $s->option_key,
            'options' => $s->options,
            'price' => $s->price,
            'qty' => $s->qty,
            'image' => $s->image,
            'sku' => $s->sku,
        ])->values()
        : collect();
@endphp
<div id="variant-table" class="variant-table" hidden
     data-skus="{{ $existingSkus->toJson() }}"
     data-max-options="{{ \App\Services\Products\ProductSkus::MAX_OPTIONS }}"
     data-max-variants="{{ \App\Services\Products\ProductSkus::MAX_VARIANTS }}">
    <div class="panel-line"></div>
    <div class="panel-body">
        <p class="variant-table__warning alert alert-danger" role="alert" hidden></p>
        <div class="variant-table__scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Photo</th>
                        <th scope="col">Variant</th>
                        <th scope="col">Price (₱)</th>
                        <th scope="col">Available</th>
                        <th scope="col">SKU <span class="label-hint">(optional)</span></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <p class="variant-table__total">Total inventory: <strong data-total>0</strong> available</p>
        <p class="form-help">Leave a price empty to use the product's regular price. "On sale" takes the same percentage off every variant.</p>
    </div>
</div>
