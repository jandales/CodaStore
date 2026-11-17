<?php

namespace Tests\Feature;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_post_a_review()
    {
        $product = $this->createProduct();

        $this->post(route('review.store', [$product->encryptedId()]), ['rate' => 5, 'comments' => 'Nice'])
            ->assertRedirect(route('login'));
        $this->assertSame(0, Review::count());
    }

    public function test_rating_and_comment_are_required()
    {
        $product = $this->createProduct();
        $this->signInCustomer();

        $this->post(route('review.store', [$product->encryptedId()]), ['rate' => 0, 'comments' => ''])
            ->assertSessionHasErrors([
                'rate' => 'Please select a rating',
                'comments' => 'Please write your review',
            ]);
        $this->assertSame(0, Review::count());
    }

    public function test_posting_a_review_shows_a_confirmation()
    {
        $product = $this->createProduct();
        $user = $this->signInCustomer();

        $this->from(route('shop.product', [$product->encryptedId()]))
            ->post(route('review.store', [$product->encryptedId()]), ['rate' => 4, 'comments' => 'Great fit'])
            ->assertSessionHas('review_success', 'Thanks, your review is posted');

        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'product_id' => $product->id, 'rating' => 4]);
    }

    public function test_posting_again_updates_the_existing_review()
    {
        $product = $this->createProduct();
        $user = $this->signInCustomer();
        Review::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'rating' => 2]);

        $this->post(route('review.store', [$product->encryptedId()]), ['rate' => 5, 'comments' => 'Changed my mind'])
            ->assertSessionHas('review_success', 'Your review has been updated');

        $this->assertSame(1, Review::count());
        $this->assertSame(5, (int) Review::first()->rating);
    }

    public function test_new_reviews_are_unblocked_by_default()
    {
        $review = Review::factory()->create();

        $this->assertSame(0, (int) $review->fresh()->block);
    }
}
