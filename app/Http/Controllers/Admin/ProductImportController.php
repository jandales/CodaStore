<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Products\ProductImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Upload a CSV -> see a dry-run report of every row -> confirm to import the valid rows.
 * The uploaded file is kept under storage/app/imports between the two steps.
 */
class ProductImportController extends Controller
{
    private const DIR = 'imports';

    public function create()
    {
        return view('admin.products.import');
    }

    public function template()
    {
        $rows = [
            ['Name', 'SKU', 'Category', 'Status', 'Regular price', 'Sale price', 'On sale', 'Stock', 'Sizes', 'Description'],
            ['Linen overshirt', 'OVR-001', 'jacket', 'Published', '1899', '1499', 'Yes', '25', 'S|M|L|XL', 'Relaxed fit in washed linen'],
            ['Everyday hoodie', 'HOD-014', 'hoddie', 'Draft', '1299', '', 'No', '40', 'M|L', ''],
        ];

        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="coda-product-import-template.csv"',
        ]);
    }

    public function preview(Request $request, ProductImporter $importer)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048', 'mimes:csv,txt'],
        ], [
            'file.required' => 'Choose a CSV file to upload.',
            'file.mimes' => 'The file must be a CSV. In Excel or Google Sheets, use "Save as / Download as CSV".',
            'file.max' => 'The file must be 2 MB or smaller.',
        ]);

        $this->purgeOldUploads();

        $token = Str::random(40);
        $request->file('file')->storeAs(self::DIR, $token . '.csv');

        $report = $importer->analyse(Storage::path(self::DIR . '/' . $token . '.csv'));
        if ($report['error'] && !$report['rows']) {
            Storage::delete(self::DIR . '/' . $token . '.csv');
            return back()->withErrors(['file' => $report['error']]);
        }

        return view('admin.products.import', [
            'report' => $report,
            'token' => $token,
            'filename' => $request->file('file')->getClientOriginalName(),
        ]);
    }

    public function run(Request $request, ProductImporter $importer)
    {
        $token = (string) $request->input('token');
        $path = self::DIR . '/' . $token . '.csv';

        if (!preg_match('/^[A-Za-z0-9]{40}$/', $token) || !Storage::exists($path)) {
            return redirect()->route('admin.products.import')->withErrors(['file' => 'That upload has expired. Upload the file again.']);
        }

        // Check the file again: products may have changed since the preview.
        $report = $importer->analyse(Storage::path($path));
        Storage::delete($path);

        if ($report['error'] && !$report['rows']) {
            return redirect()->route('admin.products.import')->withErrors(['file' => $report['error']]);
        }
        if ($report['counts']['create'] + $report['counts']['update'] === 0) {
            return redirect()->route('admin.products.import')->withErrors(['file' => 'Every row has an error, so nothing was imported.']);
        }

        $result = $importer->import($report);

        $message = "Import finished: {$result['created']} created, {$result['updated']} updated";
        $message .= $result['skipped'] ? ", {$result['skipped']} rows with errors skipped." : '.';

        return redirect()->route('admin.products.import')->with('success', $message);
    }

    /** Uploads that were previewed but never confirmed are removed after a day. */
    private function purgeOldUploads(): void
    {
        foreach (Storage::files(self::DIR) as $file) {
            if (Storage::lastModified($file) < now()->subDay()->getTimestamp()) {
                Storage::delete($file);
            }
        }
    }
}
