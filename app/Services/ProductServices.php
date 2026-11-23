<?php

namespace  App\Services;

use App\Models\Photo;
use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Services\BaseServices;
use App\Models\ProductAttribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;


class ProductServices
{

    private $defaultImage = '/img/products/default.jpg';

    public function list()
    {
        return Product::with('category','stock')->paginate(10);
    }


    public function store(Request $request)
    {

       $image = json_decode($request->image, true);

       return  DB::transaction(function ()  use ($request, $image){
                    $validated = $request->validated();
                    $validated['slug'] = productSlug($request->name);
                    $validated['regular_price'] = $request->regular_price ?? 0; // optional
                    $validated['imagePath'] = $image['path'] ?? $this->defaultImage;
                    $validated['featured'] = $request->featured ?? 0;
                    $validated['on_sale'] = $request->boolean('on_sale');
                    $validated['sale_price'] = $request->sale_price ?? 0;
                    $validated['admin_id'] = auth()->guard('admin')->user()->id;
                    $validated['short_description'] = $request->short_description;
                    $validated['long_description'] = $request->long_description;
                    $validated['tags'] = $request->tags;
                    $product = Product::create($validated);

                    Self::saveMedia($request, $product);

                    Stock::create([
                        'product_id' => $product->id,
                        'qty' => (int) ($request->qty ?? 0), // variants: set from the variant table below
                    ]);

                    Self::createAttributes($request->input('attributes'), $request->hasVariant, $product->id);
                    Self::syncVariants($request, $product);

                    \App\Services\Products\StockMovements::recordDiff($product->id, ['product' => 0, 'skus' => []], 'created');

                    return $product;
                });






    }

    public function update(Request $request, Product $product)
    {

        $image = json_decode($request->image, true);

        return DB::transaction(function () use($request, $product, $image) {
            $stockBefore = \App\Services\Products\StockMovements::snapshot($product->id);
            $product->name = $request->name;
            $product->slug = productSlug($request->name);
            $product->category_id = $request->category_id;
            $product->short_description = $request->short_description;
            $product->long_description = $request->long_description;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->sale_price = $request->sale_price ?? 0;
            $product->on_sale = $request->boolean('on_sale');
            $product->regular_price = $request->regular_price ?? 0; // optional
            $product->status = $request->status;
            $product->tags = $request->tags;
            $product->featured = $request->featured ?? 0;
            $product->imagePath = $image['path'] ?? $product->imagePath;
            $product->save();

            $stock = $product->stock;

            $stock->qty = (int) ($request->qty ?? 0); // variants: replaced by the sum of the variants below

            $stock->save();

            // Photos are only removed when the form marks them deleted, never because another
            // photo became the main one.
            Self::saveMedia($request, $product);

            Self::createAttributes($request->input('attributes'), $request->hasVariant, $product->id);
            Self::syncVariants($request, $product);

            \App\Services\Products\StockMovements::recordDiff($product->id, $stockBefore, 'product_edit');

            return $product;

        });




    }

    /** Variants table from the form; without options the single quantity is the stock. */
    private function syncVariants(Request $request, Product $product): void
    {
        $skus = app(\App\Services\Products\ProductSkus::class);

        if ($request->boolean('hasVariant') && !empty($request->input('attributes')) && is_array($request->input('skus'))) {
            $skus->sync($product, $request->input('skus'));
        } else {
            $skus->clear($product);
        }

        // Saving the form means the per-variant stock has been looked at.
        $product->forceFill(['variant_stock_reviewed' => true])->saveQuietly();
    }

    public function destroy(Product $product)
    {
        return $product->delete();
    }

    public function forceDestroy(Product $product)
    {

        return DB::transaction( function () use ( $product ) {

            ProductAttribute::where('product_id', $product->id)->delete();

            ProductVariant::where('product_id', $product->id)->delete();

            if ( $product->stock != null ) {

                $product->stock->delete();

            }

            foreach ($product->photos as $photo ) {

                $photo->delete();

                // Other products may share the same file.
                \App\Services\Products\ProductImageFiles::deleteIfUnused($photo->path);

            }

            return $product->delete();

        });


    }

    public function destroySelectedItem($selected)
    {
        foreach ( $selected as $id ) {

            $id = decrypt($id);

            $product = Product::find($id);

            Self::destroy($product);

        }
    }

    public function changeStatus(Product $product)
    {
        $status = $product->status == 0 ?? 1;

        Self::updateStatus($product, $status);

    }

    public function changeSelectedItemStatus($productIDs, $status)
    {

        foreach($productIDs as $id)
        {
            $id = decrypt($id);

            $product = Product::find($id);

            Self::updateStatus($product, $status);

        }
    }


    public function filter($filterBy, $value)
    {

        $products = Product::with(['category', 'stock'])

            ->whereHas('category', function ($query) use ($filterBy, $value) {

                if($filterBy != 'all') $query->where($filterBy, $value);

        })->paginate(10);

        return ['products' => $products, 'filter' => $value == 0 ? "status=unpublished" : "status=published"];
    }

    private function updateStatus(Product $product, $status)
    {
        $product->status = $status;

        $product->save();
    }

    /**
     * Saves the photos sent by the form. With the Media card (media=1) their order is the
     * display order and the first one is the main image (the default picture when none are
     * left). Older forms send the main image separately as "image", which update() already set.
     */
    private function saveMedia(Request $request, Product $product): void
    {
        $removed = [];
        $kept = Self::imageGalleryUpdate($request->input('images'), $product->id, $removed);

        if ($request->boolean('media')) {
            $product->forceFill(['imagePath' => $kept[0] ?? Product::DEFAULT_IMAGE])->save();
        }

        // Files go last: a removed photo may have been the main image until just now. Only
        // delete a file when no other product, photo or variant uses it.
        foreach (array_unique($removed) as $path) {
            \App\Services\Products\ProductImageFiles::deleteIfUnused($path);
        }
    }

    /**
     * @param  string[]  $removed  filled with the paths of photos removed (files not deleted yet)
     * @return string[] paths of the photos kept, in the order they were sent
     */
    private function imageGalleryUpdate($images, $product_id, array &$removed = []): array
    {
        $kept = [];

        if (!is_array($images)) return $kept;

        foreach ($images as $image) {

            $image = is_array($image) ? $image : json_decode((string) $image, true);
            if (!is_array($image)) continue;

            $deleted = (int) ($image['deleted'] ?? 0) === 1;

            // No id: the product's main image from before the Media card, never a gallery
            // photo. Keep it as a photo so it can be ordered like the others.
            if (empty($image['id'])) {
                $path = (string) ($image['path'] ?? '');
                if ($deleted || !str_starts_with($path, '/') || !Product::isUsableImage($path)) continue;

                $photo = Photo::firstOrCreate(
                    ['product_id' => $product_id, 'path' => $path],
                    ['location' => dirname($path) . '/']
                );
            } else {
                // Already gone (removed earlier, or the page was saved twice): nothing to do.
                $photo = Photo::find($image['id']);
                if (!$photo) continue;

                // Only this product's photos, or new uploads not attached to a product yet.
                if ($photo->product_id && (int) $photo->product_id !== (int) $product_id) continue;

                if ($deleted) {
                    $photo->delete();
                    $removed[] = $photo->path;
                    continue;
                }
            }

            $photo->product_id = $product_id;
            $photo->sort_order = count($kept);
            $photo->save();

            $kept[] = $photo->path;
        }

        return $kept;
    }

    private function createAttributes($attributes, $hasVariant, $product_id)
    {
        if (!$hasVariant) return Self::destroyAttributes($product_id);

        if ($attributes == null) return;

        DB::transaction(function () use ($hasVariant, $attributes, $product_id) {

            Self::destroyAttributes($product_id);

            foreach($attributes as $attribute) {

                $attribute = json_decode($attribute,true);

                ProductAttribute::create([

                    'product_id' => $product_id,

                    'attribute_id' => $attribute['id']

                ]);

                $variants = $attribute['variants'];

                foreach($variants as $variant) {

                    ProductVariant::create([

                        'product_id' => $product_id,

                        'attribute_id' => $attribute['id'],

                        'name' => $variant,

                    ]);
                }
            }

        });


    }

    private function destroyAttributes($product_id)
    {
        DB::transaction(function ()  use ($product_id){

            ProductAttribute::where('product_id', $product_id)->delete();

            ProductVariant::where('product_id', $product_id)->delete();

        });

    }

    public function search(Request $request)
    {
        $keyword = $request->keyword  ?? 'null';

        return Product::search($keyword)->with('category','stock')->paginate(10);
    }

}