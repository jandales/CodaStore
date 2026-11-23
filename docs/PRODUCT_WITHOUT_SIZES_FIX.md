# Fix: Product without sizes asked for a size

Status: **fixed** (branch `fix-product-without-sizes`). All 315 tests pass.

## The problem

On the product page of a product **without sizes**:

1. A **"Size :"** label showed with nothing under it.
2. **Add to bag** failed with **"Please select size"**, and the shopper had no size to pick.

## Cause

The product had the **Size attribute attached** in the admin, but **no size options** (S, M, L...)
were added to it.

| Step | What happened |
|---|---|
| Page | `components/variants.blade.php` printed a label for **every attached attribute**, even one with no options |
| Script | `/product/hasvariant/{id}` listed every attached attribute, so `resources/js/app/include/product.js` sent `size = ""` with Add to bag |
| Server | `QuantityRequest` requires a value for each option sent, so it rejected the empty size with "Please select size" |

## The fix

A product's **options to pick** are now only the attributes that have **at least one option**. An
attribute attached without options is ignored everywhere.

| File | Change |
|---|---|
| `app/Models/Product.php` | New `selectableAttributes()`: attributes that have at least one option |
| `resources/views/components/variants.blade.php` | Lists only `selectableAttributes()` (also fixes `product-overview.blade.php`, which uses the same component) |
| `resources/views/product.blade.php` | Leaves out the options section when the product has no options |
| `app/Services/ShopServices.php` | `hasVariants()` returns only attributes with options, so the script no longer sends an empty size |
| `app/Http/Requests/QuantityRequest.php` | `prepareForValidation()` drops options the product doesn't offer, so pages already open in a browser still work |
| `app/Services/CartServices.php` | Handles a missing options list (`(array)`) |

Products **with** sizes behave as before: a size must be picked.

No JavaScript changed, so no asset rebuild is needed.

## Note for developers

Inside the `Product` model, `$this->attributes` is Eloquent's internal column array, **not** the
`attributes()` relation. Use `$this->getRelationValue('attributes')` there. Outside the model,
`$product->attributes` returns the relation as usual.

## Tests

In `tests/Feature/AddToBagTest.php`:

- `test_product_without_sizes_can_be_added_even_if_an_empty_size_is_sent`
- `test_product_page_hides_the_size_label_when_there_are_no_sizes` (also checks `/product/hasvariant` returns no attributes)
- `test_product_page_shows_the_size_label_when_there_are_sizes`

## Data clean-up (optional)

Products affected can be found with:

```sql
SELECT p.id, p.name
FROM products p
JOIN product_attributes pa ON pa.product_id = p.id
WHERE NOT EXISTS (
    SELECT 1 FROM product_variants pv
    WHERE pv.product_id = p.id AND pv.attribute_id = pa.attribute_id
);
```

The store works correctly with these products now; removing the empty attribute in the admin just
keeps the data tidy.
