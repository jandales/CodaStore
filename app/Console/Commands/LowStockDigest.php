<?php

namespace App\Console\Commands;

use App\Mail\Admin\LowStockDigestMail;
use App\Models\GeneralSetting;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/** Emails the store a list of published products at or below the low-stock threshold. */
class LowStockDigest extends Command
{
    protected $signature = 'store:low-stock-digest';

    protected $description = 'Email the daily low-stock digest to the store';

    public function handle()
    {
        $threshold = GeneralSetting::lowStockThreshold();
        $products = \App\Services\Products\LowStock::items($threshold);

        if ($products->isEmpty()) {
            $this->info('No products are low on stock.');
            return 0;
        }

        $recipients = GeneralSetting::storeRecipients();
        if (!$recipients) {
            logger()->warning('Low-stock digest not sent: no store email and no administrators');
            $this->warn('No one to send the digest to. Set the store email in Settings.');
            return 0;
        }

        Mail::to($recipients)->send(new LowStockDigestMail($products, $threshold));
        $this->info("Sent the low-stock digest ({$products->count()} items) to " . implode(', ', $recipients) . '.');

        return 0;
    }
}
