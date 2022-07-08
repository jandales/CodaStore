<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ReviewServices;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;

class ReviewController extends Controller
{
    private $services;

    public function __construct(ReviewServices $services)
    {
        $this->services = $services;
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
}
