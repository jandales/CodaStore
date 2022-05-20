<?php 

namespace App\Services;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewServices
{
    public function list_review()
    {
        return Review::with('product', 'user')->paginate(10);
    }
    public function search(Request $request)
    {
        return Review::search($request->keyword)->with('product', 'user')->paginate(10);
    }

    public function list_review_status($status)
    {
        return Review::where('block',$status)->with('product', 'user')->paginate(10);
    }

    public function block(Review $review)
    {     
        $review->block = $review->block == 1 ? 0 : 1;
        $review->save();        
    }

    public function store(Request $request, Product $product)
    {
        $user = auth()->user();

        if ($product->reviewby($user)) 
        {
            $review = $user->review($product);
            $review->comments = $request->comments; 
            $review->rating = $request->rate;       
            $review->save(); 
            return;
        }

        Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,      
            'comments' => $request->comments,
            'rating' => $request->rate,
        ]);  
    
    }
    public function update(Request $request,  Review $review)
    {
        $review->comments = $request->comments; 
        $review->rating = $request->rate;       
        $review->save();  
    }

    public function destroySelected(Request $request)
    {
        foreach($request->selected as $id){
            $review = Review::find($id);
            $review->delete();
        }
       
    }

}