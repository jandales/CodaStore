<?php

namespace App\Services\Products;

use App\Models\ActivityLog;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

/**
 * Bulk create/update products from a CSV file. analyse() checks every row and reports what
 * would happen without writing anything (the dry run); import() applies the valid rows.
 *
 * Rows are matched to existing products by SKU. The columns match the products export, so an
 * exported file can be edited and imported back. Recognised headers (any case):
 * Name, SKU, Category, Status, Regular price, Sale price, On sale, Stock, Sizes, Description.
 */
class ProductImporter
{
    public const MAX_ROWS = 1000;

    private const HEADERS = [
        'name' => 'name',
        'sku' => 'sku',
        'category' => 'category',
        'status' => 'status',
        'regular_price' => 'regular_price',
        'price' => 'regular_price',
        'sale_price' => 'sale_price',
        'on_sale' => 'on_sale',
        'stock' => 'stock',
        'qty' => 'stock',
        'quantity' => 'stock',
        'sizes' => 'sizes',
        'description' => 'description',
        'short_description' => 'description',
    ];

    /** Columns from the export that are fine to leave in the file. */
    private const IGNORED = ['price_now'];

    private const DEFAULT_IMAGE = '/img/products/default.jpg';

    /**
     * @return array{error: ?string, rows: array, counts: array, ignored: array}
     */
    public function analyse(string $path): array
    {
        $report = ['error' => null, 'rows' => [], 'counts' => ['create' => 0, 'update' => 0, 'error' => 0], 'ignored' => []];

        $handle = @fopen($path, 'r');
        if (!$handle) {
            $report['error'] = "The file couldn't be read.";
            return $report;
        }

        $header = fgetcsv($handle);
        if (!$header || count(array_filter($header, 'strlen')) === 0) {
            fclose($handle);
            $report['error'] = 'The file is empty. The first row should be the column names.';
            return $report;
        }

        $columns = [];
        foreach ($header as $i => $title) {
            $key = $this->normaliseHeader($title, $i === 0);
            if (isset(self::HEADERS[$key])) {
                $columns[$i] = self::HEADERS[$key];
            } elseif ($key !== '' && !in_array($key, self::IGNORED, true)) {
                $report['ignored'][] = trim($title);
            }
        }

        if (!in_array('name', $columns, true) && !in_array('sku', $columns, true)) {
            fclose($handle);
            $report['error'] = 'The file needs a "Name" or "SKU" column. Download the template to see the expected columns.';
            return $report;
        }

        $categories = Category::all();
        $seenSkus = [];
        $line = 1;

        while (($cells = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($cells, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue; // blank line
            }
            if (count($report['rows']) >= self::MAX_ROWS) {
                $report['error'] = 'The file has more than ' . self::MAX_ROWS . ' products. Split it into smaller files.';
                break;
            }

            $data = [];
            foreach ($columns as $i => $field) {
                $data[$field] = isset($cells[$i]) ? trim((string) $cells[$i]) : '';
            }

            $row = $this->analyseRow($line, $data, $categories, $seenSkus);
            if ($row['sku'] !== '') {
                $seenSkus[mb_strtolower($row['sku'])] = $line;
            }

            $report['rows'][] = $row;
            $report['counts'][$row['errors'] ? 'error' : $row['action']]++;
        }
        fclose($handle);

        if (!$report['error'] && !$report['rows']) {
            $report['error'] = 'The file has column names but no products.';
        }

        return $report;
    }

    /**
     * Applies the valid rows of a report from analyse() in one transaction.
     *
     * @return array{created: int, updated: int, skipped: int}
     */
    public function import(array $report): array
    {
        $valid = array_values(array_filter($report['rows'], fn ($row) => !$row['errors']));
        $sizeAttribute = Attribute::where('slug', 'size')->orWhere('name', 'size')->first();
        $adminId = auth('admin')->id();

        $result = ActivityLog::muted(function () use ($valid, $sizeAttribute, $adminId) {
            return DB::transaction(function () use ($valid, $sizeAttribute, $adminId) {
                $created = 0;
                $updated = 0;
                foreach ($valid as $row) {
                    $values = $row['values'];
                    $product = $row['product_id'] ? Product::find($row['product_id']) : null;

                    if ($product) {
                        $product->fill(array_intersect_key($values, array_flip(['name', 'category_id', 'regular_price', 'sale_price', 'on_sale', 'status', 'short_description'])));
                        if (isset($values['name'])) {
                            $product->slug = productSlug($values['name']);
                        }
                        $product->save();
                        $updated++;
                    } else {
                        $product = Product::create(array_merge([
                            'sale_price' => 0,
                            'on_sale' => false,
                            'status' => 0,
                            'featured' => 0,
                            'imagePath' => self::DEFAULT_IMAGE,
                            'admin_id' => $adminId,
                        ], array_intersect_key($values, array_flip(['name', 'sku', 'category_id', 'regular_price', 'sale_price', 'on_sale', 'status', 'short_description'])), [
                            'slug' => productSlug($values['name']),
                        ]));
                        $created++;
                    }

                    if (array_key_exists('stock', $values)) {
                        Stock::updateOrCreate(['product_id' => $product->id], ['qty' => $values['stock']]);
                    } elseif (!$product->stock()->exists()) {
                        Stock::create(['product_id' => $product->id, 'qty' => 0]);
                    }

                    if (array_key_exists('sizes', $values) && $sizeAttribute) {
                        $this->replaceSizes($product, $sizeAttribute, $values['sizes']);
                    }
                }

                return ['created' => $created, 'updated' => $updated];
            });
        });

        $result['skipped'] = count($report['rows']) - count($valid);

        ActivityLog::record('product.import', null, "Imported products from CSV: {$result['created']} created, {$result['updated']} updated" . ($result['skipped'] ? ", {$result['skipped']} skipped" : ''), $result);

        return $result;
    }

    private function analyseRow(int $line, array $data, $categories, array $seenSkus): array
    {
        $errors = [];
        $values = [];
        $sku = $data['sku'] ?? '';
        $product = $sku !== '' ? Product::where('sku', $sku)->first() : null;
        $creating = !$product;

        if ($sku !== '' && isset($seenSkus[mb_strtolower($sku)])) {
            $errors[] = "SKU {$sku} is already used on row {$seenSkus[mb_strtolower($sku)]} of this file.";
        }

        // Name
        $name = $data['name'] ?? '';
        if ($name !== '') {
            if (mb_strlen($name) > 255) {
                $errors[] = 'Name is longer than 255 characters.';
            }
            $values['name'] = $name;
        } elseif ($creating) {
            $errors[] = $sku !== '' ? "No product has SKU {$sku}, so a name is needed to create one." : 'Name is missing.';
        }

        if ($creating && $sku !== '') {
            $values['sku'] = $sku;
        }

        // Category
        $categoryName = $data['category'] ?? '';
        if ($categoryName !== '') {
            $category = $categories->first(fn ($c) => mb_strtolower($c->name) === mb_strtolower($categoryName) || mb_strtolower($c->slug) === mb_strtolower($categoryName));
            if ($category) {
                $values['category_id'] = $category->id;
            } else {
                $errors[] = "Category \"{$categoryName}\" doesn't exist. Create it first or use one of: " . $categories->pluck('name')->implode(', ') . '.';
            }
        } elseif ($creating) {
            $errors[] = 'Category is missing.';
        }

        // Prices
        $regular = $this->money($data['regular_price'] ?? '');
        if ($regular === false) {
            $errors[] = 'Regular price must be a number, like 1299 or 1299.00.';
        } elseif ($regular !== null) {
            if ($regular < 1) {
                $errors[] = 'Regular price must be at least 1.';
            }
            $values['regular_price'] = (int) round($regular);
        } elseif ($creating) {
            $errors[] = 'Regular price is missing.';
        }

        $sale = $this->money($data['sale_price'] ?? '');
        if ($sale === false) {
            $errors[] = 'Sale price must be a number or left empty.';
        } elseif ($sale !== null) {
            $values['sale_price'] = (int) round($sale);
        }

        if (array_key_exists('on_sale', $data) && $data['on_sale'] !== '') {
            $onSale = $this->yesNo($data['on_sale']);
            if ($onSale === null) {
                $errors[] = 'On sale must be Yes or No.';
            } else {
                $values['on_sale'] = $onSale;
            }
        } elseif ($sale !== null && $sale !== false && array_key_exists('on_sale', $data)) {
            $values['on_sale'] = false;
        }

        $effectiveOnSale = $values['on_sale'] ?? ($product ? (bool) $product->on_sale : false);
        if ($effectiveOnSale) {
            $effectiveSale = $values['sale_price'] ?? ($product ? $product->sale_price : 0);
            $effectiveRegular = $values['regular_price'] ?? ($product ? $product->regular_price : 0);
            if (!$effectiveSale) {
                $errors[] = 'On sale is Yes but there is no sale price.';
            } elseif ($effectiveSale >= $effectiveRegular) {
                $errors[] = 'The sale price must be lower than the regular price.';
            }
        }

        // Stock
        $stock = $data['stock'] ?? '';
        if ($stock !== '') {
            if (!preg_match('/^\d+$/', str_replace(',', '', $stock))) {
                $errors[] = 'Stock must be a whole number, 0 or more.';
            } else {
                $values['stock'] = (int) str_replace(',', '', $stock);
            }
        }

        // Status
        $status = $data['status'] ?? '';
        if ($status !== '') {
            $map = ['published' => 1, 'publish' => 1, 'live' => 1, '1' => 1, 'draft' => 0, 'hidden' => 0, '0' => 0];
            if (!array_key_exists(mb_strtolower($status), $map)) {
                $errors[] = 'Status must be Published or Draft.';
            } else {
                $values['status'] = $map[mb_strtolower($status)];
            }
        }

        // Sizes: "S|M|L" or "S, M, L"
        if (array_key_exists('sizes', $data) && $data['sizes'] !== '') {
            $sizes = array_values(array_unique(array_filter(array_map('trim', preg_split('/[|,]/', $data['sizes'])), 'strlen')));
            if (count($sizes) > 20) {
                $errors[] = 'More than 20 sizes.';
            }
            $values['sizes'] = array_map(fn ($s) => mb_substr(mb_strtoupper($s), 0, 20), $sizes);
        }

        if (($data['description'] ?? '') !== '') {
            $values['short_description'] = $data['description'];
        }

        return [
            'line' => $line,
            'action' => $creating ? 'create' : 'update',
            'product_id' => $product ? $product->id : null,
            'name' => $name !== '' ? $name : ($product ? $product->name : ''),
            'sku' => $sku,
            'changes' => $errors ? [] : $this->describe($product, $values),
            'errors' => $errors,
            'values' => $values,
        ];
    }

    /** Human summary of what an update changes, or what a create sets. */
    private function describe(?Product $product, array $values): array
    {
        $out = [];
        $money = fn ($v) => '₱' . number_format((float) $v, 2);

        if (!$product) {
            $out[] = 'Price ' . $money($values['regular_price']);
            if (!empty($values['on_sale'])) {
                $out[] = 'on sale at ' . $money($values['sale_price']);
            }
            $out[] = 'stock ' . ($values['stock'] ?? 0);
            $out[] = (($values['status'] ?? 0) === 1) ? 'published' : 'draft';
            if (!empty($values['sizes'])) {
                $out[] = 'sizes ' . implode('/', $values['sizes']);
            }
            return $out;
        }

        if (isset($values['name']) && $values['name'] !== $product->name) {
            $out[] = 'name → ' . $values['name'];
        }
        if (isset($values['regular_price']) && (int) $values['regular_price'] !== (int) $product->regular_price) {
            $out[] = 'price ' . $money($product->regular_price) . ' → ' . $money($values['regular_price']);
        }
        if (isset($values['sale_price']) && (int) $values['sale_price'] !== (int) $product->sale_price) {
            $out[] = 'sale price ' . $money($product->sale_price) . ' → ' . $money($values['sale_price']);
        }
        if (isset($values['on_sale']) && (bool) $values['on_sale'] !== (bool) $product->on_sale) {
            $out[] = $values['on_sale'] ? 'put on sale' : 'taken off sale';
        }
        if (isset($values['stock']) && (int) $values['stock'] !== $product->stockQty()) {
            $out[] = 'stock ' . $product->stockQty() . ' → ' . $values['stock'];
        }
        if (isset($values['status']) && (int) $values['status'] !== (int) $product->status) {
            $out[] = $values['status'] === 1 ? 'published' : 'moved to drafts';
        }
        if (isset($values['category_id']) && (int) $values['category_id'] !== (int) $product->category_id) {
            $out[] = 'category changed';
        }
        if (isset($values['sizes'])) {
            $out[] = 'sizes ' . implode('/', $values['sizes']);
        }

        return $out ?: ['No changes'];
    }

    private function replaceSizes(Product $product, Attribute $size, array $sizes): void
    {
        ProductVariant::where('product_id', $product->id)->where('attribute_id', $size->id)->delete();
        ProductAttribute::where('product_id', $product->id)->where('attribute_id', $size->id)->delete();

        if (!$sizes) {
            return;
        }

        ProductAttribute::create(['product_id' => $product->id, 'attribute_id' => $size->id]);
        foreach ($sizes as $name) {
            ProductVariant::create(['product_id' => $product->id, 'attribute_id' => $size->id, 'name' => $name]);
        }
    }

    private function normaliseHeader(string $title, bool $first): string
    {
        if ($first) {
            $title = preg_replace('/^\xEF\xBB\xBF/', '', $title); // Excel BOM
        }

        return trim(preg_replace('/[^a-z0-9]+/', '_', mb_strtolower(trim($title))), '_');
    }

    /** null for empty, false for not a number, otherwise the amount. Accepts "₱1,299.00". */
    private function money(string $value)
    {
        $clean = str_replace([',', '₱', 'PHP', ' '], '', $value);
        if ($clean === '') {
            return null;
        }

        return is_numeric($clean) && (float) $clean >= 0 ? (float) $clean : false;
    }

    private function yesNo(string $value): ?bool
    {
        $v = mb_strtolower(trim($value));
        if (in_array($v, ['yes', 'y', 'true', '1', 'on'], true)) {
            return true;
        }
        if (in_array($v, ['no', 'n', 'false', '0', 'off'], true)) {
            return false;
        }

        return null;
    }
}
