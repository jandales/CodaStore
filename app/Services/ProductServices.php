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
                    $validated['imagePath'] = $image['path'] ?? $this->defaultImage;
                    $validated['featured'] = $request->featured ?? 0;
                    $validated['on_sale'] = $request->boolean('on_sale');
                    $validated['sale_price'] = $request->sale_price ?? 0;
                    $validated['admin_id'] = auth()->guard('admin')->user()->id;
                    $validated['short_description'] = $request->short_description;
                    $validated['long_description'] = $request->long_description;
                    $validated['tags'] = $request->tags;
                    $product = Product::create($validated);

                    Stock::create([
                        'product_id' => $product->id,
                        'qty' => (int) ($request->qty ?? 0), // variants: set from the variant table below
                    ]);

                    Self::imageGalleryUpdate($request->input('images'), $product->id);
                    Self::createAttributes($request->input('attributes'), $request->hasVariant, $product->id);
                    Self::syncVariants($request, $product);

                    return $product;
                });






    }

    public function update(Request $request, Product $product)
    {

        $image = json_decode($request->image, true);

        return DB::transaction(function () use($request, $product, $image) {
            $product->name = $request->name;
            $product->slug = productSlug($request->name);
            $product->category_id = $request->category_id;
            $product->short_description = $request->short_description;
            $product->long_description = $request->long_description;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->sale_price = $request->sale_price ?? 0;
            $product->on_sale = $request->boolean('on_sale');
            $product->regular_price = $request->regular_price;
            $product->status = $request->status;
            $product->tags = $request->tags;
            $product->featured = $request->featured ?? 0;
            $product->imagePath = $image['path'] ?? $product->imagePath;
            $product->save();

            $stock = $product->stock;

            $stock->qty = (int) ($request->qty ?? 0); // variants: replaced by the sum of the variants below

            $stock->save();

            Self::updateProductImage($image, $product->imageDetail() );

            Self::imageGalleryUpdate($request->input('images'), $product->id);

            Self::createAttributes($request->input('attributes'), $request->hasVariant, $product->id);
            Self::syncVariants($request, $product);

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

    private function updateProductImage($request, $photo)
    {
        if ($request == null) return;

        if ($photo != null)  Self::unlink($photo);

    }

    private function imageGalleryUpdate($images, $product_id)
    {

        if ($images == null)  return;

        foreach ($images as $image) {

            $image = is_array($image) ? $image : json_decode((string) $image, true);
            if (!is_array($image) || empty($image['id'])) continue;

            // Already gone (removed earlier, or the page was saved twice): nothing to do.
            $photo = Photo::find($image['id']);
            if (!$photo) continue;

            // Only this product's photos, or new uploads not attached to a product yet.
            if ($photo->product_id && (int) $photo->product_id !== (int) $product_id) continue;

            if ((int) ($image['deleted'] ?? 0) === 1) {
                Self::unlink($photo);
                continue;
            }

            if ((int) $photo->product_id !== (int) $product_id) {

                Self::updatePhotoOwner($photo, $product_id);

            }
        }

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

    private function updatePhotoOwner(Photo $photo, $product_id)
    {

        $photo->product_id = $product_id;

        $photo->save();

    }
    private function unlink(Photo $photo)
    {
        return DB::transaction(function () use($photo) {

            $deleted = $photo->delete();

            if(!$deleted) return back()->with('error','Image cant delete');

            // Only remove the file when no other product, photo or variant uses it.
            \App\Services\Products\ProductImageFiles::deleteIfUnused($photo->path);

            return response()->json(['deleted' => $deleted]);

        });

    }

    public function search(Request $request)
    {
        $keyword = $request->keyword  ?? 'null';

        return Product::search($keyword)->with('category','stock')->paginate(10);
    }

}