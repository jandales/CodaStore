<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Simple content pages (Privacy, Terms, Shipping & Returns, FAQ) edited in the admin.
 * The four standard pages are created as unpublished drafts with headings only: the store
 * owner writes the actual policy text and publishes them.
 */
class CreatePagesTable extends Migration
{
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('title', 120);
            $table->string('meta_description', 160)->nullable();
            $table->longText('body');
            $table->string('footer_group', 20)->default('help'); // help, company, none
            $table->boolean('published')->default(false);
            $table->timestamps();
        });

        $now = now();
        $draft = "> **Draft.** Replace the notes in italics with your own text, then tick \"Published\".\n\n";
        $pages = [
            ['shipping-returns', 'Shipping & returns', 'help', $draft
                . "## Shipping\n\n_Where you deliver, how long delivery takes, and what it costs._\n\n"
                . "## Tracking your order\n\nWhen your order ships we email you the courier and tracking number. You can also see it on your order page.\n\n"
                . "## Returns\n\n_How many days customers have, what condition items must be in, and who pays return shipping._\n\n"
                . "To start a return, open the order in your account and choose **Return this order**.\n\n"
                . "## Refunds\n\nApproved returns are refunded to the original PayPal payment. _Say how long refunds usually take._\n"],
            ['faq', 'FAQ', 'help', $draft
                . "## How do I choose my size?\n\n_Explain your sizing, or link to a size guide._\n\n"
                . "## Which payment methods do you accept?\n\nWe accept PayPal, including cards paid through PayPal.\n\n"
                . "## Can I change or cancel my order?\n\nYou can cancel an order from your account until it ships.\n\n"
                . "## How do I contact you?\n\nUse the [contact page](/contact). _Add your usual response time._\n"],
            ['privacy', 'Privacy policy', 'company', $draft
                . "## What we collect\n\n_The personal data you collect (name, email, addresses, order history) and why._\n\n"
                . "## Payments\n\nPayments are processed by PayPal. We don't store card details.\n\n"
                . "## Emails\n\n_Which emails you send (order updates, newsletter, bag reminders) and how to unsubscribe._\n\n"
                . "## Your rights\n\n_How customers can access, correct or delete their data, and who to contact._\n"],
            ['terms', 'Terms of service', 'company', $draft
                . "## About these terms\n\n_Who runs the store and which law applies._\n\n"
                . "## Orders and pricing\n\n_When an order is accepted, how pricing errors are handled._\n\n"
                . "## Returns\n\nSee [Shipping & returns](/pages/shipping-returns).\n\n"
                . "## Contact\n\n_How to reach you about these terms._\n"],
        ];

        foreach ($pages as [$slug, $title, $group, $body]) {
            DB::table('pages')->insert([
                'slug' => $slug, 'title' => $title, 'footer_group' => $group, 'body' => $body,
                'published' => false, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
