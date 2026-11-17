<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_has_stable_url_meta_tags_and_structured_data()
    {
        $product = $this->createProduct(5, false, ['name' => 'linen shirt', 'short_description' => 'Washed linen, relaxed fit.', 'regular_price' => 1500, 'sku' => 'LS-1']);

        $this->assertStringEndsWith('/shop/item/linen-shirt-' . $product->id, $product->url());
        $this->assertSame($product->url(), $product->fresh()->url(), 'the same on every request');

        $response = $this->get($product->url())->assertOk();
        $response->assertSee('<meta name="description" content="Washed linen, relaxed fit.">', false)
            ->assertSee('<link rel="canonical" href="' . $product->url() . '">', false)
            ->assertSee('<meta property="og:type" content="product">', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"price":"1500.00"', false)
            ->assertSee('https://schema.org/InStock', false)
            ->assertDontSee('noindex');
    }

    public function test_old_encrypted_product_links_still_work_and_bad_ones_404()
    {
        $product = $this->createProduct(5, false);

        $this->get(route('shop.product', [$product->encryptedId()]))->assertOk();
        $this->get('/shop/item/not-a-real-thing')->assertNotFound();
    }

    public function test_private_and_filtered_pages_are_noindex()
    {
        $this->createProduct(5, true);

        $this->get('/shop')->assertDontSee('noindex');
        $this->get('/shop?sort=price-asc')->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical" href="' . url('/shop') . '">', false);
        $this->get('/cart')->assertSee('noindex, follow', false);
        $this->get('/login')->assertSee('noindex, follow', false);
    }

    public function test_sitemap_lists_published_products_categories_and_pages()
    {
        $live = $this->createProduct(5, false, ['name' => 'live coat']);
        $draft = $this->createProduct(5, false, ['name' => 'secret coat', 'status' => 0]);
        Page::where('slug', 'faq')->update(['published' => true]);

        $response = $this->get('/sitemap.xml')->assertOk();

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $response->assertSee($live->url(), false)
            ->assertDontSee('secret-coat')
            ->assertSee(url('/shop/hoddie'), false)
            ->assertSee(url('/pages/faq'), false)
            ->assertDontSee(url('/pages/privacy'), false);
    }

    public function test_robots_txt_points_to_the_sitemap()
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('Sitemap: ' . url('sitemap.xml'));
    }

    public function test_related_products_prefer_the_same_category_and_skip_sold_out_and_drafts()
    {
        $this->seedStoreBasics();
        [$hoodies, $jackets] = [\App\Models\Category::where('slug', 'hoddie')->first(), \App\Models\Category::where('slug', 'jacket')->first()];
        $product = $this->createProduct(5, false, ['name' => 'main hoodie', 'category_id' => $hoodies->id]);
        $this->createProduct(5, false, ['name' => 'other hoodie', 'category_id' => $hoodies->id]);
        $this->createProduct(0, false, ['name' => 'sold out hoodie', 'category_id' => $hoodies->id]);
        $this->createProduct(5, false, ['name' => 'draft hoodie', 'category_id' => $hoodies->id, 'status' => 0]);
        $this->createProduct(5, false, ['name' => 'filler jacket', 'category_id' => $jackets->id]);

        $names = $product->related()->pluck('name')->all();

        $this->assertSame(['other hoodie', 'filler jacket'], $names);
        $this->get($product->url())->assertSee('You may also like')->assertSee('other hoodie');
    }

    public function test_draft_pages_are_hidden_until_published_and_previewable_by_staff()
    {
        $this->seedStoreBasics();
        $this->assertSame(4, Page::count(), 'the migration adds four draft pages');

        $this->get('/pages/privacy')->assertNotFound();
        $this->get('/')->assertDontSee('/pages/privacy');

        $this->signInAdmin();
        $this->get('/pages/privacy')->assertOk()->assertSee('Preview: this page is a draft');

        Page::where('slug', 'privacy')->update(['published' => true]);
        app()->forgetInstance('store.footer_pages');
        auth('admin')->logout();
        $this->get('/pages/privacy')->assertOk()->assertSee('Privacy policy')->assertDontSee('Preview:');
        $this->get('/')->assertSee(url('/pages/privacy'), false);
    }

    public function test_admin_edits_a_page_in_markdown_and_html_is_stripped()
    {
        $this->seedStoreBasics();
        $this->signInAdmin();
        $page = Page::where('slug', 'faq')->first();

        $this->put(route('admin.pages.update', $page), [
            'title' => 'Help & FAQ', 'slug' => 'faq', 'footer_group' => 'help', 'published' => 1,
            'body' => "## Sizing\n\nOur shirts run **true to size**.\n\n<script>alert(1)</script>",
        ])->assertSessionHas('success', 'Page saved and published.');

        $this->get('/pages/faq')->assertOk()
            ->assertSee('<h2>Sizing</h2>', false)
            ->assertSee('<strong>true to size</strong>', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_page_slugs_are_unique_and_staff_need_the_settings_permission()
    {
        $this->seedStoreBasics();
        $this->signInAdmin();

        $this->post(route('admin.pages.store'), ['title' => 'FAQ', 'body' => 'x', 'footer_group' => 'help'])
            ->assertSessionHasErrors(['slug' => 'Another page already uses this web address.']);
        $this->post(route('admin.pages.store'), ['title' => 'Size guide', 'body' => '## Tops', 'footer_group' => 'help'])
            ->assertRedirect();
        $this->assertTrue(Page::where('slug', 'size-guide')->exists());

        $this->signInAdmin($this->makeAdmin(['role' => 2, 'permissions' => ['orders']]));
        $this->get(route('admin.pages'))->assertForbidden();
    }
}
