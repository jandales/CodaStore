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
        return back()->with('success', 'Review successfully block');

    }

    public function store(StoreReviewRequest $request, Product $product)
    { 
        $this->services->store($request, $product);        
        return back()->with('success', 'Your review successfully submitted'); 
    }

    public function update(StoreReviewRequest  $request, Review $review)
    {        
        $this->services->update($request, $review);
        return back();
    }   

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Review successfully Deleted');
    }

    public function destroySelected(Request $request)
    {        
        $this->services->destroySelected($request);
        return back()->with('success', 'Review successfully Deleted');
    }

}
