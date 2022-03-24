<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\StoreReviewRequest;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('product', 'user')->paginate(5);
        return view('admin.reviews.index')->with('reviews', $reviews);
    }

    public function search(Request $request)
    {
        $input = $request->search;
        $reviews = Review::search($input)->with('product', 'user')->get();
        return view('admin.reviews.index')->with('reviews', $reviews);
    }

    public function listbyStatus($status)
    {
        $reviews = Review::where('block',$status)->with('product', 'user')->get();
        return view('admin.reviews.index')->with('reviews', $reviews);
    }

    public function block(Review $review)
    {
        if($review->block == 1)
        {
            $review->block = 0;
            $review->save();
            return back()->with('success', 'Review successfully unblock');
        }
     
        $review->block = 1;
        $review->save();
        return back()->with('success', 'Review successfully block');

    }

    public function store(StoreReviewRequest  $request, Product $product)
    { 
        $user = auth()->user();

        if($product->reviewby($user)) 
        {
            $review = $user->review($product);
            $review->comments = $request->comments; 
            $review->rating = $request->rate;       
            $review->save(); 
            return back()->with('success', 'Your review successfully submitted');
        }

        Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,      
            'comments' => $request->comments,
            'rating' => $request->rate,
        ]);      
        
        return back()->with('success', 'Your review successfully submitted');

    }


    public function update(StoreReviewRequest  $request, Review $review)
    {        
        $review->comments = $request->comments; 
        $review->rating = $request->rate;       
        $review->save();      
        return back();

    }   

    public function destroy(Review $review)
    {
        $review->delete();
        return back();
    }
}
