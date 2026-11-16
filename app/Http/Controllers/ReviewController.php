<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ReviewServices;
use App\Http\Requests\StoreReviewRequest;

class ReviewController extends Controller
{
    private $services;

    public function __construct(ReviewServices $services)
    {
        $this->services = $services;
    }
    public function index()
    {
        $reviews = $this->services->list_review();
        return view('admin.reviews.index')->with('reviews', $reviews);
    }

    public function search(Request $request)
    {       
        $reviews = $this->services->search($request);
        return view('admin.reviews.index')->with(['reviews' => $reviews, 'keyword' => $request->keyword]);
    }

    public function listbyStatus($status)
    {
        $reviews = $this->services->list_review_status($status);
        return view('admin.reviews.index')->with('reviews', $reviews);
    }

    public function block(Review $review)
    {
        $this->services->block($review);
        $message = $review->fresh()->block == 1 ? 'Review blocked' : 'Review unblocked';
        return back()->with('success', $message);
    }

    public function store(StoreReviewRequest $request, Product $product)
    {
        $isEdit = (bool) $product->reviewby(auth()->user());
        $this->services->store($request, $product);

        $message = $isEdit ? 'Your review has been updated' : 'Thanks, your review is posted';
        return $this->backToReviews()->with('review_success', $message);
    }

    public function update(StoreReviewRequest  $request, Review $review)
    {
        $this->services->update($request, $review);
        return $this->backToReviews()->with('review_success', 'Your review has been updated');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        // 'success' for the admin list, 'review_success' for the product page.
        return $this->backToReviews()
            ->with('success', 'Review deleted')
            ->with('review_success', 'Your review has been deleted');
    }

    public function destroySelected(Request $request)
    {
        $this->services->destroySelected($request);
        return back()->with('success', 'Reviews deleted');
    }

    /**
     * Return to the previous page, scrolled to the product reviews section.
     */
    private function backToReviews()
    {
        return redirect()->to(strtok(url()->previous(), '#') . '#product-tabs');
    }

}
