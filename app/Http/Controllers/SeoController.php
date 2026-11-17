<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;

class SeoController extends Controller
{
    /** sitemap.xml: home, shop, categories, published products and published pages. */
    public function sitemap()
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('shop'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('about'), 'priority' => '0.4', 'changefreq' => 'monthly'],
            ['loc' => route('contact'), 'priority' => '0.4', 'changefreq' => 'monthly'],
        ];

        foreach (Category::orderBy('name')->get() as $category) {
            $urls[] = ['loc' => route('shop.category', [$category]), 'priority' => '0.8', 'changefreq' => 'weekly'];
        }

        foreach (Product::published()->orderBy('id')->get(['id', 'name', 'updated_at']) as $product) {
            $urls[] = [
                'loc' => $product->url(),
                'lastmod' => optional($product->updated_at)->toAtomString(),
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ];
        }

        foreach (Page::published()->orderBy('title')->get() as $page) {
            $urls[] = ['loc' => route('page', [$page->slug]), 'lastmod' => optional($page->updated_at)->toAtomString(), 'priority' => '0.3', 'changefreq' => 'monthly'];
        }

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /account',
            'Disallow: /orders',
            'Disallow: /search',
            'Allow: /',
            '',
            'Sitemap: ' . url('sitemap.xml'),
        ];

        return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
