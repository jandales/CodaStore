<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'slug',     
    ];

    public function scopeExist($query,$name)
    {   
        return $query->where('name', $name )->first();                  
                
    }

    public function scopeSearch($query,$input)
    {
        return $query->where('name','like','%' . $input . '%')                  
                     ->orWhere('slug','like','%' . $input . '%');
                
    }

}
