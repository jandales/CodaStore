<?php

namespace App\Services\Products;

use App\Models\Photo;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Support\Facades\File;

/**
 * Deletes a product image file only when nothing uses it any more. Several products and
 * gallery photos can point at the same file (the sample products share a few images), so
 * removing one photo must not break the others.
 */
class ProductImageFiles
{
    /** Images that ship with the project; never deleted from disk. */
    private const PROTECTED_PREFIXES = ['/img/products/default/', '/img/products/default.'];

    /** @return bool true when the file was deleted */
    public static function deleteIfUnused(?string $path): bool
    {
        $path = '/' . ltrim((string) $path, '/');
        if ($path === '/' || str_contains($path, '..') || !str_starts_with($path, '/img/')) {
            return false;
        }
        foreach (self::PROTECTED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return false;
            }
        }
        if (self::inUse($path)) {
            return false;
        }

        return File::delete(public_path($path));
    }

    public static function inUse(string $path): bool
    {
        $variants = [$path, ltrim($path, '/')];

        return Photo::whereIn('path', $variants)->exists()
            || Product::withTrashed()->whereIn('imagePath', $variants)->exists()
            || ProductSku::whereIn('image', $variants)->exists();
    }
}
