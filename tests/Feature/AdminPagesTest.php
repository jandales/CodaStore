<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_loads()
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_dashboard_requires_sign_in()
    {
        $this->get('/admin/dashboard')->assertRedirect();
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_load_for_staff(string $url)
    {
        $this->createProduct();
        $this->signInAdmin();

        $this->get($url)->assertOk();
    }

    public static function adminPages(): array
    {
        return [
            'dashboard' => ['/admin/dashboard'],
            'products' => ['/admin/products'],
            'orders' => ['/admin/orders'],
            'reviews' => ['/admin/review'],
            'customers' => ['/admin/customers'],
        ];
    }

    public function test_reviews_page_lists_customer_reviews()
    {
        // Regression: /admin/review crashed with "Call to undefined method User::avatar()".
        $product = $this->createProduct();
        \App\Models\Review::factory()->create(['product_id' => $product->id, 'comments' => 'Lovely fit']);
        $this->signInAdmin();

        $this->get('/admin/review')->assertOk()->assertSee('Lovely fit');
    }
}
