{{--
    Variants card on the product form (create and edit), laid out like the reference:
    options with their values and an Edit button, "+ Add another option", then one row per
    variant with its photo, price and available quantity, and the total underneath.
    Built by public/js/admin-variants.js; it writes hidden inputs (hasVariant, attributes[],
    skus[...]) inside the product form, so the normal save sends them.
--}}
@php
    $attributeList = \App\Models\Attribute::orderBy('name')->get(['id', 'name'])
        ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])->values();

    $editing = isset($product) && $product->exists;

    $initialOptions = $editing
        ? $product->attributes()->with('attributes')->orderBy('id')->get()->map(fn ($pa) => [
            'id' => (int) $pa->attribute_id,
            'name' => optional($pa->attributes)->name,
            'values' => $product->variants->where('attribute_id', $pa->attribute_id)->pluck('name')->values(),
        ])->filter(fn ($o) => $o['name'])->values()
        : collect();

    $existingSkus = $editing
        ? $product->skus->map(fn ($s) => [
            'key' => $s->option_key,
            'price' => $s->price,
            'qty' => $s->qty,
            'image' => $s->image,
            'sku' => $s->sku,
        ])->values()
        : collect();
@endphp
<div class="panel variants-card" id="variants-card"
     data-attributes="{{ $attributeList->toJson() }}"
     data-options="{{ $initialOptions->toJson() }}"
     data-skus="{{ $existingSkus->toJson() }}"
     data-max-options="{{ \App\Services\Products\ProductSkus::MAX_OPTIONS }}"
     data-max-variants="{{ \App\Services\Products\ProductSkus::MAX_VARIANTS }}">
    <div class="panel-heading">
        <label class="panel-title">Variants</label>
    </div>
    <div class="panel-body">
        <div class="variant-options-list" data-options-list></div>
        <button type="button" class="variant-add-option" data-add-option>
            <i class="fas fa-plus" aria-hidden="true"></i> <span>Add options like size or colour</span>
        </button>
        <p class="form-help" data-no-attributes hidden>All option types are in use. Add more (for example Colour) under Products → Attributes.</p>
        <p class="variant-table__warning alert alert-danger" role="alert" hidden></p>
        <div data-hidden-inputs></div>
    </div>

    <div class="variant-table" data-table hidden>
        <div class="variant-table__scroll">
            <table class="table">
                <thead>
                    <tr>
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
        <p class="form-help variant-table__help">Leave a price empty to use the regular price above. "On sale" takes the same percentage off every variant. Pick a photo for a variant from the gallery to show it when customers choose that option.</p>
    </div>
</div>
