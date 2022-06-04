<?php

namespace App\Models;

use App\Models\Cart;
use App\Models\Stock;
use App\Models\Variant;
use App\Models\Category;
use App\Http\Traits\Crypted;
use App\Models\ProductAttribute;
use App\Http\Traits\DateTimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Product extends Model
{

    use HasFactory, DateTimeFormat, Crypted;
    
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'category_id',
        'slug',        
        'short_description',
        'imagePath',
        'long_description',
        'sku',  
        'barcode',     
        'tags',
        'sale_price',
        'regular_price',
        'status',
        'is_taxable',
        'featured',
    ];

    // protected $attributes = [    
    //     'is_taxable' => 0,
    // ];

    public function category()
    {      
        return $this->belongsTo(Category::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'id', 'product_id');
    }   

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function hasVariants()
    {
        return $this->variants->count();        
    }

    public function attributes()
    { 
        return $this->hasMany(ProductAttribute::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function isWishlisted()
    {
        $user = auth()->user()->id;
        return $this->wishlist->contains('user_id',  $user);        
    }

    public function reviewby(User $user)
    {
        return $this->reviews->contains('user_id', $user->id);
    }

    public function rating()
    {
        $result = $this->reviews->sum('rating') / 5;        
        return round($result);
    }


    public function carts()
    {
        return $this->hasMany(Cart::class);
    }   

    public function hasInCart()
    {
        return $this->carts->where('product_id', $this->id)->contains('user_id',auth()->user()->id);
    }  



    public function imageDetail()
    {
        return $this->photos->where('path', $this->imagePath)->first();
    }

    public function status()
    {
        if ($this->status == 0) return 'Draft';
        return 'Published';
    }
    public function scopePublished($query)
    {
       return $query->where('status',1);
    } 
    

    public function scopeSearch($query,$input)
    {
        return $query->where('name','like','%' . $input . '%')                  
                     ->orWhere('slug','like','%' . $input . '%');
                
    }

    public function scopeFilterByCategory($query, $filter)
    {
        return $query->with(['category', 'stock'])
                ->whereHas('category', function ($q) use ($filter) {
                    if($filter != 'all'){
                        $q->where('slug', $filter)
                        ->orWhere('name', $filter);
                    }                   
                });
    }


    public function scopePublishedSearch($query,$input)
    {
        return $query->where('status',1)
                     ->where('name','like','%' . $input . '%')                  
                     ->orWhere('slug','like','%' . $input . '%');
                
    }

    public function scopeTags($query, $tag)
    {     
        return $query->where('tags','like','%' . $tag . '%');                                    
    }

    public function minQty()
    {
        if($this->stock->qty == 0) return "Items not Available";
        
        if($this->stock->qty < 20)  return $this->stock->qty . " items left";
        
    }


    

   

   

}
