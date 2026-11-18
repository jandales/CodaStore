<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStoreBasics();
        $this->signInAdmin();
    }

    public function test_categories_page_is_a_list_with_an_add_button()
    {
        $this->get(route('admin.categories'))->assertOk()
            ->assertSee(route('admin.categories.create'), false)
            ->assertSee('Add category')
            ->assertSee('hoddie')
            ->assertDontSee('name="description"', false);
    }

    public function test_add_category_has_its_own_page_and_returns_to_the_list()
    {
        $this->get(route('admin.categories.create'))->assertOk()
            ->assertSee('Add category')
            ->assertSee('name="name"', false);

        $this->post(route('categories.store'), ['name' => 'knitwear', 'description' => 'Warm layers'])
            ->assertRedirect(route('admin.categories'))
            ->assertSessionHas('message', 'Category "knitwear" added.');

        $this->assertTrue(Category::where('name', 'knitwear')->exists());
    }

    public function test_a_duplicate_name_goes_back_to_the_add_page_with_the_error()
    {
        $this->from(route('admin.categories.create'))
            ->post(route('categories.store'), ['name' => 'jacket'])
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_edit_page_works_for_a_category_without_an_image_and_saves()
    {
        $category = Category::factory()->create(['name' => 'scarves', 'slug' => 'scarves', 'image' => null]);

        $this->get(route('categories.edit', [$category->slug]))->assertOk()
            ->assertSee('Edit category')
            ->assertSee('value="scarves"', false)
            ->assertDontSee('x-category-table');

        $this->put(route('admin.categories.update', [$category->slug]), ['name' => 'scarves and hats'])
            ->assertRedirect(route('admin.categories'))
            ->assertSessionHas('message', 'Category "scarves and hats" saved.');
    }
}
