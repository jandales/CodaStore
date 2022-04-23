<?php

namespace App\Models;

use App\Http\Traits\DateAndTimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory, DateAndTimeFormat;

    protected $fillable = [
        'product_id',
        'user_id', 
        'comments',
        'rating'
    ];


    public function scopeRatedby($query, $user)
    {
        return $this->where('user_id', $user);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function byCurrentUser()
    {
        if($this->user_id === auth()->user()->id) return true;
    }

    public function scopeSearch($query,$input)
    {
        return $query->where('comments','like','%' . $input . '%');                 
                  
    }

 

}
