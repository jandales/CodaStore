<?php

namespace Tests\Feature;

use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStoreBasics(); // categories hoddie / jacket / others, attribute "size"
        $this->signInAdmin();
    }

    public function test_attributes_page_is_a_list_with_an_add_button()
    {
        $this->get(route('admin.attributes'))->assertOk()
            ->assertSee(route('admin.attributes.create'), false)
            ->assertSee('Add attribute')
            ->assertSee('size')
            ->assertDontSee('name="description"', false);
    }

    public function test_add_attribute_has_its_own_page_and_returns_to_the_list()
    {
        $this->get(route('admin.attributes.create'))->assertOk()->assertSee('Add attribute')->assertSee('name="name"', false);

        $this->post(route('admin.attributes.store'), ['name' => 'color', 'description' => 'Garment colour'])
            ->assertRedirect(route('admin.attributes'))
            ->assertSessionHas('message', 'Attribute "color" added.');

        $this->assertTrue(Attribute::where('name', 'color')->exists());

        $this->from(route('admin.attributes.create'))->post(route('admin.attributes.store'), ['name' => 'size'])
            ->assertRedirect(route('admin.attributes.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_edit_page_saves_and_checks_names_against_attributes_not_categories()
    {
        $color = Attribute::factory()->create(['name' => 'color', 'slug' => 'color']);

        $this->get(route('admin.attributes.edit', [$color->slug]))->assertOk()
            ->assertSee('Edit attribute')
            ->assertSee('value="color"', false);

        // Another attribute's name is refused...
        $this->from(route('admin.attributes.edit', [$color->slug]))
            ->put(route('admin.attributes.update', [$color->slug]), ['name' => 'size'])
            ->assertSessionHasErrors('name');

        // ...a category's name is fine (it used to be checked against categories).
        $this->put(route('admin.attributes.update', [$color->slug]), ['name' => 'jacket'])
            ->assertRedirect(route('admin.attributes'))
            ->assertSessionHas('message', 'Attribute "jacket" saved.');
    }
}
